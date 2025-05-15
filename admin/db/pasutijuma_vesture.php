<?php

 //Iegūst visus lietotāja pasūtījumus (regulāri + pielāgotie)
//Visi pasūtījumi kārtoti pēc datuma (jaunākie pirmie)

function getUserOrders($savienojums, $lietotajs_id) {
    error_log("Getting orders for user ID: " . $lietotajs_id);

    $all_orders = [];
    
    $regular_orders = getRegularOrders($savienojums, $lietotajs_id);
    $all_orders = array_merge($all_orders, $regular_orders);
    
    $custom_orders = getCustomOrders($savienojums, $lietotajs_id);
    $all_orders = array_merge($all_orders, $custom_orders);
    
    usort($all_orders, function($a, $b) {
        $date_a = strtotime($a['pas_datums'] ?? '1970-01-01');
        $date_b = strtotime($b['pas_datums'] ?? '1970-01-01');
        return $date_b - $date_a;
    });
    
    error_log("Total orders after combining: " . count($all_orders));
    return $all_orders;
}

//Iegūst lietotāja regulāros pasūtījumus
function getRegularOrders($savienojums, $lietotajs_id) {
    $orders = [];
    
    $order_query = "SELECT p.*, COUNT(pv.vienums_id) as total_items, 'regular' as order_type
                    FROM sparkly_pasutijumi p
                    LEFT JOIN sparkly_pasutijuma_vienumi pv ON p.id_pasutijums = pv.pasutijuma_id
                    WHERE p.lietotajs_id = ?
                    GROUP BY p.id_pasutijums
                    ORDER BY p.pas_datums DESC";
    
    $order_stmt = $savienojums->prepare($order_query);
    if ($order_stmt) {
        $order_stmt->bind_param("i", $lietotajs_id);
        
        if ($order_stmt->execute()) {
            $orders_result = $order_stmt->get_result();
            
            while ($order = $orders_result->fetch_assoc()) {
                $orders[] = $order;
            }
            
            error_log("Regular orders found: " . count($orders));
        } else {
            error_log("Failed to execute regular order query: " . $order_stmt->error);
        }
        
        $order_stmt->close();
    } else {
        error_log("Failed to prepare regular order query: " . $savienojums->error);
    }
    
    return $orders;
}

// Iegūst lietotāja pielāgotos pasūtījumus

function getCustomOrders($savienojums, $lietotajs_id) {
    $orders = [];

    $table_check = $savienojums->query("SHOW TABLES LIKE 'sparkly_spec_pas'");
    if (!$table_check || $table_check->num_rows === 0) {
        error_log("Custom orders table does not exist");
        return $orders;
    }
    error_log("Custom orders table exists");
    $all_custom_query = "SELECT lietotajs_id, id_spec_pas, vards, uzvards FROM sparkly_spec_pas";
    $all_custom_result = $savienojums->query($all_custom_query);
    if ($all_custom_result) {
        error_log("All custom orders in database: " . $all_custom_result->num_rows);
        
        while ($row = $all_custom_result->fetch_assoc()) {
            error_log("Custom order - ID: {$row['id_spec_pas']}, User ID: {$row['lietotajs_id']}, Name: {$row['vards']} {$row['uzvards']}");
        }
    }

    $custom_order_query = "SELECT 
        ssp.*, 
        'custom' as order_type,
        sf.forma as forma_name,
        sa.nosaukums as audums_name,
        smf.nosaukums as malu_figura_name,
        sd1.nosaukums as dekorejums1_name,
        sd2.nosaukums as dekorejums2_name
    FROM sparkly_spec_pas ssp
    LEFT JOIN sparkly_formas sf ON ssp.forma = sf.id_forma
    LEFT JOIN sparkly_audums sa ON ssp.audums = sa.id_audums  
    LEFT JOIN sparkly_malu_figura smf ON ssp.malu_figura = smf.id_malu_figura
    LEFT JOIN sparkly_dekorejums1 sd1 ON ssp.dekorejums1 = sd1.id_dekorejums1
    LEFT JOIN sparkly_dekorejums2 sd2 ON ssp.dekorejums2 = sd2.id_dekorejums2
    WHERE ssp.lietotajs_id = ?
    ORDER BY ssp.pas_datums DESC";

    $custom_order_stmt = $savienojums->prepare($custom_order_query);
    if ($custom_order_stmt) {
        $custom_order_stmt->bind_param("i", $lietotajs_id);
        
        if ($custom_order_stmt->execute()) {
            $custom_orders_result = $custom_order_stmt->get_result();
            error_log("Custom orders found for user {$lietotajs_id}: " . $custom_orders_result->num_rows);
            
            while ($custom_order = $custom_orders_result->fetch_assoc()) {
                $custom_order['id_pasutijums'] = $custom_order['id_spec_pas'];
                
                $custom_order['kopeja_cena'] = 0;
                $custom_order['produktu_skaits'] = $custom_order['daudzums'] ?? 1;
                $custom_order['apmaksas_veids'] = 'Pēc vienošanās';
                $custom_order['piegades_veids'] = 'Pēc vienošanās';
                
                $custom_order['total_items'] = 1; 
                
                $orders[] = $custom_order;
                error_log("Added custom order ID: " . $custom_order['id_spec_pas']);
            }
        } else {
            error_log("Failed to execute custom order query: " . $custom_order_stmt->error);
        }
        $custom_order_stmt->close();
    } else {
        error_log("Failed to prepare custom order query: " . $savienojums->error);
    }
    
    return $orders;
}

// Iegūst pasūtījuma vienumus

function getOrderItems($savienojums, $pasutijuma_id) {
    $items = [];
    
    $items_query = "SELECT pv.*, p.nosaukums, p.attels1
                   FROM sparkly_pasutijuma_vienumi pv
                   JOIN produkcija_sprarkly p ON pv.produkta_id = p.id_bumba
                   WHERE pv.pasutijuma_id = ?";
    
    $items_stmt = $savienojums->prepare($items_query);
    if ($items_stmt) {
        $items_stmt->bind_param("i", $pasutijuma_id);
        
        if ($items_stmt->execute()) {
            $items_result = $items_stmt->get_result();
            
            while ($item = $items_result->fetch_assoc()) {
                $items[] = $item;
            }
        } else {
            error_log("Failed to execute order items query: " . $items_stmt->error);
        }
        
        $items_stmt->close();
    } else {
        error_log("Failed to prepare order items query: " . $savienojums->error);
    }
    
    return $items;
}
?>