<?php

require_once 'con_db.php';

function insertCustomOrder($user_id, $order_data) {
    global $savienojums;
    
    try {
        $savienojums->autocommit(FALSE);
        
        // Include pas_datums in the INSERT query with CURRENT_TIMESTAMP
        $insert_query = "INSERT INTO sparkly_spec_pas 
                        (lietotajs_id, vards, uzvards, epasts, talrunis, adrese, pilseta, pasta_indekss, 
                         forma, audums, malu_figura, dekorejums1, dekorejums2, 
                         daudzums, piezimes, statuss, datums) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Iesniegts', CURRENT_TIMESTAMP)";
        
        $stmt = $savienojums->prepare($insert_query);
        
        if ($stmt === false) {
            throw new Exception("Database prepare error: " . $savienojums->error);
        }
        
        // Bind parameters (removed pas_datums from bind_param since we're using CURRENT_TIMESTAMP in SQL)
        $stmt->bind_param("issssssssssssis", 
            $user_id,
            $order_data['vards'], 
            $order_data['uzvards'], 
            $order_data['epasts'], 
            $order_data['talrunis'], 
            $order_data['adrese'], 
            $order_data['pilseta'], 
            $order_data['pasta_indekss'],
            $order_data['forma'], 
            $order_data['audums'], 
            $order_data['malu_figura'], 
            $order_data['dekorejums1'], 
            $order_data['dekorejums2'],
            $order_data['daudzums'], 
            $order_data['piezimes']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert custom order: " . $stmt->error);
        }
        
        $custom_order_id = $savienojums->insert_id;
        error_log("Custom order created with ID: " . $custom_order_id);
        
        // Update user's custom order count
        $update_custom_order_count = $savienojums->prepare("UPDATE lietotaji_sparkly SET spec_pas_skaits = spec_pas_skaits + 1 WHERE id_lietotajs = ?");
        $update_custom_order_count->bind_param("i", $user_id);
        
        if (!$update_custom_order_count->execute()) {
            throw new Exception("Failed to update user custom order count: " . $update_custom_order_count->error);
        }
        
        error_log("User custom order count incremented for user ID: " . $user_id);
        
        $savienojums->commit();
        $savienojums->autocommit(TRUE);
        
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($update_custom_order_count)) {
            $update_custom_order_count->close();
        }
        
        return ['success' => true, 'order_id' => $custom_order_id];
        
    } catch (Exception $e) {
        $savienojums->rollback();
        $savienojums->autocommit(TRUE);
        
        error_log("Error creating custom order: " . $e->getMessage());
        
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($update_custom_order_count)) {
            $update_custom_order_count->close();
        }
        
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>