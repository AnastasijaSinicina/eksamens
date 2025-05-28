<?php
require 'con_db.php';

// Get dashboard statistics
try {
    // Total counts
    $stats = [];
    
    // Produktu skaits
    $product_count = $savienojums->query("SELECT COUNT(*) as count FROM produkcija_sprarkly")->fetch_assoc()['count'];
    $stats['products'] = $product_count;
    
    // Pasūtījumu skaits
    $order_count = $savienojums->query("SELECT COUNT(*) as count FROM sparkly_pasutijumi")->fetch_assoc()['count'];
    $stats['orders'] = $order_count;
    
    // Pielāgoto pasūtījumu skaits
    $custom_count = $savienojums->query("SELECT COUNT(*) as count FROM sparkly_spec_pas")->fetch_assoc()['count'];
    $stats['spec_pas'] = $custom_count;
    
    // Pasūtījuma statusi
    $status_query = "SELECT statuss, COUNT(*) as count FROM sparkly_pasutijumi GROUP BY statuss";
    $status_result = $savienojums->query($status_query);
    $order_statuses = [];
    while ($row = $status_result->fetch_assoc()) {
        $order_statuses[$row['statuss']] = $row['count'];
    }
    
    // Pēdējie pasūtījumi
    $recent_orders_query = "SELECT p.*, l.lietotajvards 
                           FROM sparkly_pasutijumi p 
                           JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs 
                           ORDER BY p.pas_datums DESC 
                           LIMIT 5";
    $recent_orders = $savienojums->query($recent_orders_query);
    
    // Ieņēmumi
    $today_revenue = $savienojums->query("SELECT COALESCE(SUM(kopeja_cena), 0) as revenue FROM sparkly_pasutijumi WHERE DATE(pas_datums) = CURDATE()")->fetch_assoc()['revenue'];
    $month_revenue = $savienojums->query("SELECT COALESCE(SUM(kopeja_cena), 0) as revenue FROM sparkly_pasutijumi WHERE MONTH(pas_datums) = MONTH(CURDATE()) AND YEAR(pas_datums) = YEAR(CURDATE())")->fetch_assoc()['revenue'];
    $total_revenue = $savienojums->query("SELECT COALESCE(SUM(kopeja_cena), 0) as revenue FROM sparkly_pasutijumi")->fetch_assoc()['revenue'];
    
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = ['products' => 0, 'orders' => 0, 'spec_pas' => 0];
    $order_statuses = [];
    $recent_orders = null;
    $today_revenue = $month_revenue = $total_revenue = 0;
}
?>