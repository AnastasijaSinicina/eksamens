<?php
// Start with basic error checking
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require 'db/con_db.php';

// Check if table exists first
$table_check = $savienojums->query("SHOW TABLES LIKE 'sparkly_spec_pas'");
if (!$table_check || $table_check->num_rows === 0) {
    echo "<main><section class='admin-content'>";
    echo "<h1>Pielāgoto pasūtījumu pārvaldība</h1>";
    echo "<div class='error-message'>Tabula 'sparkly_spec_pas' neeksistē. Lūdzu, izveidojiet tabulu.</div>";
    echo "</section></main>";
    exit();
}

// Check if related tables exist
$check_formas_table_sql = "SHOW TABLES LIKE 'sparkly_formas'";
$formas_table_exists = $savienojums->query($check_formas_table_sql)->num_rows > 0;

$check_audumi_table_sql = "SHOW TABLES LIKE 'sparkly_audums'";
$audumi_table_exists = $savienojums->query($check_audumi_table_sql)->num_rows > 0;

$check_malu_figura_table_sql = "SHOW TABLES LIKE 'sparkly_malu_figura'";
$malu_figura_table_exists = $savienojums->query($check_malu_figura_table_sql)->num_rows > 0;

$check_dekorejums1_table_sql = "SHOW TABLES LIKE 'sparkly_dekorejums1'";
$dekorejums1_table_exists = $savienojums->query($check_dekorejums1_table_sql)->num_rows > 0;


// Handle status and price update
if (isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];
    $new_price = floatval($_POST['new_price']);
    
    $update_query = "UPDATE sparkly_spec_pas SET statuss = ?, cena = ? WHERE id_spec_pas = ?";
    $update_stmt = $savienojums->prepare($update_query);
    
    if ($update_stmt) {
        $update_stmt->bind_param("sdi", $new_status, $new_price, $order_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Pasūtījums veiksmīgi atjaunināts!";
        } else {
            $error_message = "Kļūda atjauninot pasūtījumu: " . $update_stmt->error;
        }
        $update_stmt->close();
    } else {
        $error_message = "Kļūda sagatavojot vaicājumu: " . $savienojums->error;
    }
}

// Get order details if viewing specific order
$view_order = null;
if (isset($_GET['view'])) {
    $order_id = intval($_GET['view']);
    
    // Build query with JOINs for names
    $order_query = "SELECT ssp.*, l.lietotajvards";
    
    // Add name fields based on existing tables
    if ($formas_table_exists) {
        $order_query .= ", f.forma AS forma_name";
    }
    if ($audumi_table_exists) {
        $order_query .= ", a.nosaukums AS audums_name";
    }
    if ($malu_figura_table_exists) {
        $order_query .= ", m.nosaukums AS malu_figura_name";
    }
    if ($dekorejums1_table_exists) {
        $order_query .= ", d1.nosaukums AS dekorejums1_name";
    }
    
    $order_query .= " FROM sparkly_spec_pas ssp
                     LEFT JOIN lietotaji_sparkly l ON ssp.lietotajs_id = l.id_lietotajs";
    
    // Add JOINs based on existing tables
    if ($formas_table_exists) {
        $order_query .= " LEFT JOIN sparkly_formas f ON ssp.forma = f.id_forma";
    }
    if ($audumi_table_exists) {
        $order_query .= " LEFT JOIN sparkly_audums a ON ssp.audums = a.id_audums";
    }
    if ($malu_figura_table_exists) {
        $order_query .= " LEFT JOIN sparkly_malu_figura m ON ssp.malu_figura = m.id_malu_figura";
    }
    if ($dekorejums1_table_exists) {
        $order_query .= " LEFT JOIN sparkly_dekorejums1 d1 ON ssp.dekorejums1 = d1.id_dekorejums1";
    }

    
    $order_query .= " WHERE ssp.id_spec_pas = ?";
    
    $order_stmt = $savienojums->prepare($order_query);
    
    if ($order_stmt) {
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        
        if ($order_result->num_rows > 0) {
            $view_order = $order_result->fetch_assoc();
        }
        $order_stmt->close();
    }
}

// Get all custom orders for list view
if (!$view_order) {
    $orders_query = "SELECT ssp.*, l.lietotajvards 
                     FROM sparkly_spec_pas ssp
                     LEFT JOIN lietotaji_sparkly l ON ssp.lietotajs_id = l.id_lietotajs
                     ORDER BY ssp.datums DESC";
    $orders_result = $savienojums->query($orders_query);
}
?>