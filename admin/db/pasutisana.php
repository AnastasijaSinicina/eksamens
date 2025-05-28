<?php

/**
 * Get user data by username
 */
function getUserData($connection, $username) {
    $query = "SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get cart count for user
 */
function getCartCount($connection, $username) {
    $query = "SELECT COUNT(*) as count FROM grozs_sparkly WHERE lietotajvards = ? AND statuss = 'aktīvs'";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['count'];
}

/**
 * Get cart items for user
 */
function getCartItems($connection, $username) {
    $query = "SELECT g.*, p.nosaukums, p.cena, p.attels1 
              FROM grozs_sparkly g 
              JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
              WHERE g.lietotajvards = ? AND g.statuss = 'aktīvs'";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($item = $result->fetch_assoc()) {
        $items[] = $item;
    }
    return $items;
}

/**
 * Generate unique order number
 */
function generateUniqueOrderNumber($connection) {
    $max_attempts = 12;
    $attempts = 0;
    
    do {
        // Generate 12-digit random number (100000000000 to 999999999999)
        $order_number = rand(100000000000, 999999999999);
        
        // Check if this number already exists
        $check_query = "SELECT COUNT(*) as count FROM sparkly_pasutijumi WHERE pasutijuma_numurs = ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("i", $order_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->fetch_assoc()['count'] > 0;
        
        $attempts++;
        
        if (!$exists) {
            return $order_number;
        }
        
    } while ($exists && $attempts < $max_attempts);
    
    // If we couldn't generate unique number after max attempts, throw error
    throw new Exception("Unable to generate unique order number after $max_attempts attempts");
}

/**
 * Insert order into database
 */
function insertOrder($connection, $order_data) {
    $query = "INSERT INTO sparkly_pasutijumi 
              (lietotajs_id, pasutijuma_numurs, kopeja_cena, apmaksas_veids, piegades_veids, 
               produktu_skaits, vards, uzvards, epasts, talrunis, pilseta, adrese, pasta_indeks, statuss) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("iidssissssssss", 
        $order_data['lietotajs_id'],
        $order_data['pasutijuma_numurs'],
        $order_data['kopeja_cena'],
        $order_data['apmaksas_veids'],
        $order_data['piegades_veids'],
        $order_data['produktu_skaits'],
        $order_data['vards'],
        $order_data['uzvards'],
        $order_data['epasts'],
        $order_data['telefons'],
        $order_data['pilseta'],
        $order_data['adrese'],
        $order_data['pasta_indekss'],
        $order_data['statuss']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert order: " . $stmt->error);
    }
    
    return $connection->insert_id;
}

/**
 * Insert order items
 */
function insertOrderItems($connection, $order_id, $cart_items) {
    $query = "INSERT INTO sparkly_pasutijuma_vienumi 
              (pasutijuma_id, produkta_id, daudzums_no_groza, cena) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $connection->prepare($query);
    
    foreach ($cart_items as $item) {
        $stmt->bind_param("iiid", 
            $order_id, 
            $item['bumba_id'], 
            $item['daudzums'], 
            $item['cena']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert order item: " . $stmt->error);
        }
    }
}

/**
 * Update cart status to 'ordered'
 */
function updateCartStatus($connection, $username) {
    $query = "UPDATE grozs_sparkly SET statuss = 'pasūtīts' WHERE lietotajvards = ? AND statuss = 'aktīvs'";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $username);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update cart: " . $stmt->error);
    }
}

/**
 * Update user order count
 */
function updateUserOrderCount($connection, $user_id) {
    $query = "UPDATE lietotaji_sparkly SET pas_skaits = pas_skaits + 1 WHERE id_lietotajs = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update user order count: " . $stmt->error);
    }
}

/**
 * Process complete order (transaction)
 */
function processOrder($connection, $order_data, $cart_items, $username) {
    try {
        // Start transaction
        $connection->autocommit(FALSE);
        
        // Generate unique order number
        $order_data['pasutijuma_numurs'] = generateUniqueOrderNumber($connection);
        error_log("Generated unique order number: " . $order_data['pasutijuma_numurs']);
        
        // Insert the order
        $order_id = insertOrder($connection, $order_data);
        error_log("Order created with ID: " . $order_id);
        
        // Insert order items
        insertOrderItems($connection, $order_id, $cart_items);
        
        // Update cart status
        updateCartStatus($connection, $username);
        
        // Update user order count
        updateUserOrderCount($connection, $order_data['lietotajs_id']);
        error_log("User order count incremented for user ID: " . $order_data['lietotajs_id']);
        
        // Commit transaction
        $connection->commit();
        $connection->autocommit(TRUE);
        
        return [
            'success' => true,
            'order_id' => $order_id
        ];
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $connection->rollback();
        $connection->autocommit(TRUE);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

?>