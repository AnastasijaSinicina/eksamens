<?php

$order_id = $_GET['view'];

// Get order details
$order_id = $_GET['view'];

// Get order information
$order_query = "SELECT p.*, l.lietotajvards
               FROM sparkly_pasutijumi p
               JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs
               WHERE p.id_pasutijums = ?";
$order_stmt = $savienojums->prepare($order_query);

if ($order_stmt === false) {
    die('Prepare failed: ' . $savienojums->error);
}

$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows > 0) {
    $view_order = $order_result->fetch_assoc();
    
    $items_query = "SELECT i.*, p.attels1, p.nosaukums
                   FROM sparkly_pasutijuma_vienumi i
                   JOIN produkcija_sprarkly p ON i.produkta_id = p.id_bumba
                   WHERE i.pasutijuma_id = ?";
    $items_stmt = $savienojums->prepare($items_query);
    
    if ($items_stmt === false) {
        die('Prepare failed: ' . $savienojums->error);
    }
    
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $order_items = [];
    while ($item = $items_result->fetch_assoc()) {
        $order_items[] = $item;
    }
    
    $view_order['items'] = $order_items;
}
?>