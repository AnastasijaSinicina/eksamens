<?php

/**
 * Iegūst visus lietotāja pasūtījumus (regulāri + pielāgotie)
 * Visi pasūtījumi kārtoti pēc datuma (jaunākie pirmie)
 */
function getUserOrders($savienojums, $lietotajs_id) {
    if (!isset($savienojums) || !isset($lietotajs_id)) {
        error_log("Invalid parameters for getUserOrders");
        return [];
    }

    error_log("Getting orders for user ID: " . $lietotajs_id);

    $all_orders = [];
    
    // Get regular orders
    $regular_orders = getRegularOrders($savienojums, $lietotajs_id);
    if (isset($regular_orders) && is_array($regular_orders)) {
        $all_orders = array_merge($all_orders, $regular_orders);
    }
    
    // Get custom orders
    $custom_orders = getCustomOrders($savienojums, $lietotajs_id);
    if (isset($custom_orders) && is_array($custom_orders)) {
        $all_orders = array_merge($all_orders, $custom_orders);
    }
    
    // Sort by date (newest first)
    usort($all_orders, function($a, $b) {
        $date_a = isset($a['pas_datums']) ? strtotime($a['pas_datums']) : strtotime('1970-01-01');
        $date_b = isset($b['pas_datums']) ? strtotime($b['pas_datums']) : strtotime('1970-01-01');
        return $date_b - $date_a;
    });
    
    error_log("Total orders after combining: " . count($all_orders));
    return $all_orders;
}

/**
 * Iegūst lietotāja regulāros pasūtījumus
 */
function getRegularOrders($savienojums, $lietotajs_id) {
    if (!isset($savienojums) || !isset($lietotajs_id)) {
        error_log("Invalid parameters for getRegularOrders");
        return [];
    }

    $orders = [];
    
    $order_query = "SELECT p.*, COUNT(pv.vienums_id) as total_items, 'regular' as order_type
                    FROM sparkly_pasutijumi p
                    LEFT JOIN sparkly_pasutijuma_vienumi pv ON p.id_pasutijums = pv.pasutijuma_id
                    WHERE p.lietotajs_id = ?
                    GROUP BY p.id_pasutijums
                    ORDER BY p.pas_datums DESC";
    
    $order_stmt = $savienojums->prepare($order_query);
    if (isset($order_stmt) && $order_stmt) {
        $order_stmt->bind_param("i", $lietotajs_id);
        
        if ($order_stmt->execute()) {
            $orders_result = $order_stmt->get_result();
            
            if (isset($orders_result)) {
                while ($order = $orders_result->fetch_assoc()) {
                    if (isset($order) && is_array($order)) {
                        // Ensure all required fields are set
                        $order['order_type'] = 'regular';
                        $order['pas_datums'] = isset($order['pas_datums']) ? $order['pas_datums'] : date('Y-m-d H:i:s');
                        $order['kopeja_cena'] = isset($order['kopeja_cena']) ? $order['kopeja_cena'] : 0;
                        $order['produktu_skaits'] = isset($order['total_items']) ? $order['total_items'] : 0;
                        $order['statuss'] = isset($order['statuss']) ? $order['statuss'] : 'Nezināms';
                        $order['apmaksas_veids'] = isset($order['apmaksas_veids']) ? $order['apmaksas_veids'] : 'Nav norādīts';
                        $order['piegades_veids'] = isset($order['piegades_veids']) ? $order['piegades_veids'] : 'Nav norādīts';
                        
                        $orders[] = $order;
                    }
                }
            }
            
            error_log("Regular orders found: " . count($orders));
        } else {
            error_log("Failed to execute regular order query: " . (isset($order_stmt->error) ? $order_stmt->error : 'Unknown error'));
        }
        
        $order_stmt->close();
    } else {
        error_log("Failed to prepare regular order query: " . (isset($savienojums->error) ? $savienojums->error : 'Unknown error'));
    }
    
    return $orders;
}

/**
 * Iegūst lietotāja pielāgotos pasūtījumus
 */
function getCustomOrders($savienojums, $lietotajs_id) {
    if (!isset($savienojums) || !isset($lietotajs_id)) {
        error_log("Invalid parameters for getCustomOrders");
        return [];
    }

    $orders = [];

    // Check if custom orders table exists
    $table_check = $savienojums->query("SHOW TABLES LIKE 'sparkly_spec_pas'");
    if (!isset($table_check) || !$table_check || $table_check->num_rows === 0) {
        error_log("Custom orders table does not exist");
        return $orders;
    }
    
    error_log("Custom orders table exists");
    
    // Debug: Log all custom orders
    $all_custom_query = "SELECT lietotajs_id, id_spec_pas, vards, uzvards FROM sparkly_spec_pas";
    $all_custom_result = $savienojums->query($all_custom_query);
    if (isset($all_custom_result) && $all_custom_result) {
        error_log("All custom orders in database: " . $all_custom_result->num_rows);
        
        while ($row = $all_custom_result->fetch_assoc()) {
            if (isset($row) && is_array($row)) {
                $id = isset($row['id_spec_pas']) ? $row['id_spec_pas'] : 'Unknown';
                $user_id = isset($row['lietotajs_id']) ? $row['lietotajs_id'] : 'Unknown';
                $name = (isset($row['vards']) ? $row['vards'] : '') . ' ' . (isset($row['uzvards']) ? $row['uzvards'] : '');
                error_log("Custom order - ID: {$id}, User ID: {$user_id}, Name: {$name}");
            }
        }
    }

    // Get custom orders with related data
    $custom_order_query = "SELECT 
        ssp.*, 
        'custom' as order_type,
        sf.forma as forma_name,
        sa.nosaukums as audums_name,
        smf.nosaukums as malu_figura_name,
        sd1.nosaukums as dekorejums1_name
    FROM sparkly_spec_pas ssp
    LEFT JOIN sparkly_formas sf ON ssp.forma = sf.id_forma
    LEFT JOIN sparkly_audums sa ON ssp.audums = sa.id_audums  
    LEFT JOIN sparkly_malu_figura smf ON ssp.malu_figura = smf.id_malu_figura
    LEFT JOIN sparkly_dekorejums1 sd1 ON ssp.dekorejums1 = sd1.id_dekorejums1
    WHERE ssp.lietotajs_id = ?
    ORDER BY ssp.datums DESC";

    $custom_order_stmt = $savienojums->prepare($custom_order_query);
    if (isset($custom_order_stmt) && $custom_order_stmt) {
        $custom_order_stmt->bind_param("i", $lietotajs_id);
        
        if ($custom_order_stmt->execute()) {
            $custom_orders_result = $custom_order_stmt->get_result();
            if (isset($custom_orders_result)) {
                error_log("Custom orders found for user {$lietotajs_id}: " . $custom_orders_result->num_rows);
                
                while ($custom_order = $custom_orders_result->fetch_assoc()) {
                    if (isset($custom_order) && is_array($custom_order)) {
                        // Normalize custom order data to match regular order structure
                        $custom_order['id_pasutijums'] = isset($custom_order['id_spec_pas']) ? $custom_order['id_spec_pas'] : 0;
                        $custom_order['order_type'] = 'custom';
                        
                        // Handle date field
                        if (isset($custom_order['datums'])) {
                            $custom_order['pas_datums'] = $custom_order['datums'];
                        } else {
                            $custom_order['pas_datums'] = date('Y-m-d H:i:s');
                            $id = isset($custom_order['id_spec_pas']) ? $custom_order['id_spec_pas'] : 'Unknown';
                            error_log("Warning: Custom order {$id} had no datums field, using current time");
                        }
                        
                        // Set default values for missing fields
                        $custom_order['kopeja_cena'] = isset($custom_order['cena']) ? $custom_order['cena'] : 0;
                        $custom_order['produktu_skaits'] = isset($custom_order['daudzums']) ? $custom_order['daudzums'] : 1;
                        $custom_order['statuss'] = isset($custom_order['statuss']) ? $custom_order['statuss'] : 'Iesniegts';
                        $custom_order['apmaksas_veids'] = 'Pēc vienošanās';
                        $custom_order['piegades_veids'] = 'Pēc vienošanās';
                        $custom_order['total_items'] = 1;
                        
                        // Ensure address fields are set
                        $custom_order['vards'] = isset($custom_order['vards']) ? $custom_order['vards'] : '';
                        $custom_order['uzvards'] = isset($custom_order['uzvards']) ? $custom_order['uzvards'] : '';
                        $custom_order['adrese'] = isset($custom_order['adrese']) ? $custom_order['adrese'] : '';
                        $custom_order['pilseta'] = isset($custom_order['pilseta']) ? $custom_order['pilseta'] : '';
                        $custom_order['pasta_indekss'] = isset($custom_order['pasta_indekss']) ? $custom_order['pasta_indekss'] : '';
                        $custom_order['talrunis'] = isset($custom_order['talrunis']) ? $custom_order['talrunis'] : '';
                        $custom_order['epasts'] = isset($custom_order['epasts']) ? $custom_order['epasts'] : '';
                        
                        // Ensure specification fields are set
                        $custom_order['forma_name'] = isset($custom_order['forma_name']) ? $custom_order['forma_name'] : '';
                        $custom_order['audums_name'] = isset($custom_order['audums_name']) ? $custom_order['audums_name'] : '';
                        $custom_order['malu_figura_name'] = isset($custom_order['malu_figura_name']) ? $custom_order['malu_figura_name'] : '';
                        $custom_order['dekorejums1_name'] = isset($custom_order['dekorejums1_name']) ? $custom_order['dekorejums1_name'] : '';
                        $custom_order['piezimes'] = isset($custom_order['piezimes']) ? $custom_order['piezimes'] : '';
                        
                        $orders[] = $custom_order;
                        
                        $id = isset($custom_order['id_spec_pas']) ? $custom_order['id_spec_pas'] : 'Unknown';
                        $date = isset($custom_order['pas_datums']) ? $custom_order['pas_datums'] : 'Unknown';
                        error_log("Added custom order ID: {$id} with date: {$date}");
                    }
                }
            }
        } else {
            error_log("Failed to execute custom order query: " . (isset($custom_order_stmt->error) ? $custom_order_stmt->error : 'Unknown error'));
        }
        $custom_order_stmt->close();
    } else {
        error_log("Failed to prepare custom order query: " . (isset($savienojums->error) ? $savienojums->error : 'Unknown error'));
    }
    
    return $orders;
}

/**
 * Iegūst pasūtījuma vienumus
 */
function getOrderItems($savienojums, $pasutijuma_id) {
    if (!isset($savienojums) || !isset($pasutijuma_id)) {
        error_log("Invalid parameters for getOrderItems");
        return [];
    }

    $items = [];
    
    $items_query = "SELECT pv.*, p.nosaukums, p.attels1
                   FROM sparkly_pasutijuma_vienumi pv
                   JOIN produkcija_sprarkly p ON pv.produkta_id = p.id_bumba
                   WHERE pv.pasutijuma_id = ?";
    
    $items_stmt = $savienojums->prepare($items_query);
    if (isset($items_stmt) && $items_stmt) {
        $items_stmt->bind_param("i", $pasutijuma_id);
        
        if ($items_stmt->execute()) {
            $items_result = $items_stmt->get_result();
            
            if (isset($items_result)) {
                while ($item = $items_result->fetch_assoc()) {
                    if (isset($item) && is_array($item)) {
                        // Ensure required fields are set
                        $item['nosaukums'] = isset($item['nosaukums']) ? $item['nosaukums'] : 'Nezināms produkts';
                        $item['cena'] = isset($item['cena']) ? $item['cena'] : 0;
                        $item['daudzums_no_groza'] = isset($item['daudzums_no_groza']) ? $item['daudzums_no_groza'] : 1;
                        $item['attels1'] = isset($item['attels1']) ? $item['attels1'] : '';
                        
                        $items[] = $item;
                    }
                }
            }
        } else {
            error_log("Failed to execute order items query: " . (isset($items_stmt->error) ? $items_stmt->error : 'Unknown error'));
        }
        
        $items_stmt->close();
    } else {
        error_log("Failed to prepare order items query: " . (isset($savienojums->error) ? $savienojums->error : 'Unknown error'));
    }
    
    return $items;
}

?>