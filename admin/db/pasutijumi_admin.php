<?php
// Database queries for order management

// Update order status query
if (isset($_GET['action']) && $_GET['action'] == 'update_status') {
    $update_query = "UPDATE sparkly_pasutijumi SET statuss = ? WHERE id_pasutijums = ?";
}

// Get order details with customer information query
if (isset($_GET['action']) && $_GET['action'] == 'get_order_details') {
    $order_query = "SELECT p.*, l.lietotajvards
                   FROM sparkly_pasutijumi p
                   JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs
                   WHERE p.id_pasutijums = ?";
}

// Get order items with product information query
if (isset($_GET['action']) && $_GET['action'] == 'get_order_items') {
    $items_query = "SELECT i.*, p.attels1, p.nosaukums
                   FROM sparkly_pasutijuma_vienumi i
                   JOIN produkcija_sprarkly p ON i.produkta_id = p.id_bumba
                   WHERE i.pasutijuma_id = ?";
}

// Get orders with filters query builder
if (isset($_GET['action']) && $_GET['action'] == 'get_orders_filtered') {
    $base_query = "SELECT p.*, l.lietotajvards
                  FROM sparkly_pasutijumi p
                  JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs";
    
    // Filter conditions
    $where_conditions = [];
    $status_filter_condition = "p.statuss = ?";
    $search_condition = "(p.id_pasutijums LIKE ? OR l.lietotajvards LIKE ? OR p.vards LIKE ? OR p.uzvards LIKE ? OR p.epasts LIKE ?)";
    $date_from_condition = "p.pas_datums >= ?";
    $date_to_condition = "p.pas_datums <= ?";
    
    $order_by_clause = " ORDER BY p.pas_datums DESC";
}
?>