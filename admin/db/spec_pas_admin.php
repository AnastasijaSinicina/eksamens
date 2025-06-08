<?php
// Start with basic error checking
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require 'con_db.php';

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

// Handle AJAX requests for orders list with pagination
if ((isset($_GET['ajax']) && $_GET['ajax'] == '1') || (isset($_POST['ajax']) && $_POST['ajax'] == '1')) {
    $status_filter = $_POST['status'] ?? $_GET['status'] ?? '';
    $search = $_POST['search'] ?? $_GET['search'] ?? '';
    $date_from = $_POST['date_from'] ?? $_GET['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? $_GET['date_to'] ?? '';
    $page = intval($_POST['page'] ?? $_GET['page'] ?? 1);
    $limit = 6; // Orders per page
    $offset = ($page - 1) * $limit;
    
    // Base query for counting total records
    $count_sql = "SELECT COUNT(*) as total
                  FROM sparkly_spec_pas ssp
                  LEFT JOIN lietotaji_sparkly l ON ssp.lietotajs_id = l.id_lietotajs
                  WHERE 1=1";
    
    // Main query for fetching records
    $sql = "SELECT ssp.*, l.lietotajvards
            FROM sparkly_spec_pas ssp
            LEFT JOIN lietotaji_sparkly l ON ssp.lietotajs_id = l.id_lietotajs
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Add filters to both queries
    if (!empty($status_filter)) {
        $count_sql .= " AND ssp.statuss = ?";
        $sql .= " AND ssp.statuss = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    if (!empty($search)) {
        $search_condition = " AND (ssp.id_spec_pas LIKE ? OR l.lietotajvards LIKE ? OR ssp.vards LIKE ? OR ssp.uzvards LIKE ? OR ssp.epasts LIKE ?)";
        $count_sql .= $search_condition;
        $sql .= $search_condition;
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
        $types .= "sssss";
    }

    if (!empty($date_from)) {
        $count_sql .= " AND DATE(ssp.datums) >= ?";
        $sql .= " AND DATE(ssp.datums) >= ?";
        $params[] = $date_from;
        $types .= "s";
    }
    
    if (!empty($date_to)) {
        $count_sql .= " AND DATE(ssp.datums) <= ?";
        $sql .= " AND DATE(ssp.datums) <= ?";
        $params[] = $date_to;
        $types .= "s";
    }
    
    // Get total count
    if (!empty($params)) {
        $count_stmt = $savienojums->prepare($count_sql);
        $count_stmt->bind_param($types, ...$params);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_records = $count_result->fetch_assoc()['total'];
        $count_stmt->close();
    } else {
        $count_result = $savienojums->query($count_sql);
        $total_records = $count_result->fetch_assoc()['total'];
    }
    
    $total_pages = ceil($total_records / $limit);
    
    // Add ordering and pagination to main query
    $sql .= " ORDER BY ssp.datums DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    // Execute main query
    if (!empty($params)) {
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    // Return JSON response with pagination info
    if (isset($_POST['return_json']) || isset($_GET['return_json'])) {
        $orders = [];
        if ($result && $result->num_rows > 0) {
            while ($order = $result->fetch_assoc()) {
                $orders[] = $order;
            }
        }
        
        echo json_encode([
            'orders' => $orders,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_records' => $total_records,
                'limit' => $limit
            ]
        ]);
        
        if (isset($stmt)) {
            $stmt->close();
        }
        exit;
    }
    
    // Return HTML for table rows
    if ($result && $result->num_rows > 0) {
        while ($order = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>#C' . $order['id_spec_pas'] . '</td>';
            echo '<td>';
            echo '<div class="client-info">';
            echo '<div>' . htmlspecialchars($order['vards'] . ' ' . $order['uzvards']) . '</div>';
            echo '<small>' . htmlspecialchars($order['lietotajvards'] ?? 'Nav') . '</small>';
            echo '</div>';
            echo '</td>';
            echo '<td>' . date('d.m.Y H:i', strtotime($order['datums'])) . '</td>';
            echo '<td>' . $order['daudzums'] . '</td>';
            echo '<td>';
            if ($order['cena']) {
                echo number_format($order['cena'], 2) . '€';
            } else {
                echo '<span class="price-not-set">Nav noteikta</span>';
            }
            echo '</td>';
            echo '<td>';
            echo '<span class="status ' . strtolower(str_replace('ē', 'e', $order['statuss'])) . '">' . $order['statuss'] . '</span>';
            echo '</td>';
            echo '<td class="action-buttons">';
            echo '<a href="spec_pas.php?view=' . $order['id_spec_pas'] . '" class="btn edit-btn"><i class="fas fa-eye"></i> Skatīt</a>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr>';
        echo '<td colspan="7" class="no-records">Nav atrasts neviens pielāgots pasūtījums ar norādītajiem parametriem</td>';
        echo '</tr>';
    }
    
    // Add pagination info as data attributes to the last row
    if ($result && $result->num_rows > 0) {
        echo '<tr style="display:none;" data-current-page="' . $page . '" data-total-pages="' . $total_pages . '" data-total-records="' . $total_records . '"></tr>';
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
    exit;
}

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

// Get all custom orders for list view (with basic pagination for non-AJAX)
if (!$view_order && !isset($_POST['ajax']) && !isset($_GET['ajax'])) {
    $orders_query = "SELECT ssp.*, l.lietotajvards 
                     FROM sparkly_spec_pas ssp
                     LEFT JOIN lietotaji_sparkly l ON ssp.lietotajs_id = l.id_lietotajs
                     ORDER BY ssp.datums DESC
                     LIMIT 6";
    $orders_result = $savienojums->query($orders_query);
}
?>