<?php
require_once 'con_db.php';
session_start();

if ((isset($_GET['ajax']) && $_GET['ajax'] == '1') || (isset($_POST['ajax']) && $_POST['ajax'] == '1')) {
    $status_filter = $_POST['status'] ?? $_GET['status'] ?? '';
    $search = $_POST['search'] ?? $_GET['search'] ?? '';
    $date_from = $_POST['date_from'] ?? $_GET['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? $_GET['date_to'] ?? '';
    
    $sql = "SELECT p.*, l.lietotajvards,
                   m.lietotajvards as red_liet_username,
                   m.vards as red_liet_first_name,
                   m.uzvards as red_liet_last_name
            FROM sparkly_pasutijumi p
            JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs
            LEFT JOIN lietotaji_sparkly m ON p.red_liet = m.id_lietotajs
            WHERE 1=1";
    
    $params = [];
    $types = "";
    

    if (!empty($status_filter)) {
        $sql .= " AND p.statuss = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    if (!empty($search)) {
        $sql .= " AND (p.id_pasutijums LIKE ? OR l.lietotajvards LIKE ? OR p.vards LIKE ? OR p.uzvards LIKE ? OR p.epasts LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
        $types .= "sssss";
    }
    

    if (!empty($date_from)) {
        $sql .= " AND DATE(p.pas_datums) >= ?";
        $params[] = $date_from;
        $types .= "s";
    }
    
    if (!empty($date_to)) {
        $sql .= " AND DATE(p.pas_datums) <= ?";
        $params[] = $date_to;
        $types .= "s";
    }
    
    $sql .= " ORDER BY p.pas_datums DESC";
    
    if (!empty($params)) {
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $savienojums->query($sql);
    }
    

    if ($result && $result->num_rows > 0) {
        while ($order = $result->fetch_assoc()) {
            if (!empty($order['red_liet_first_name']) && !empty($order['red_liet_last_name'])) {
                $red_liet_name = $order['red_liet_first_name'] . ' ' . $order['red_liet_last_name'];
            } else if (!empty($order['red_liet_username'])) {
                $red_liet_name = $order['red_liet_username'];
            } else {
                $red_liet_name = '';
            }
            
            echo '<tr>';
            echo '<td>' . $order['id_pasutijums'] . '</td>';
            echo '<td>';
            echo '<div class="client-info">';
            echo '<div>' . htmlspecialchars($order['vards'] . ' ' . $order['uzvards']) . '</div>';
            echo '</div>';
            echo '</td>';
            echo '<td>' . date('d.m.Y H:i', strtotime($order['pas_datums'])) . '</td>';
            echo '<td>' . number_format($order['kopeja_cena'], 2) . '€</td>';
            echo '<td>' . $order['produktu_skaits'] . '</td>';
            echo '<td>';
            echo '<span class="status ' . strtolower($order['statuss']) . '">' . $order['statuss'] . '</span>';
            echo '</td>';
            echo '<td>';
            if (!empty($red_liet_name)) {
                echo '<small>' . htmlspecialchars($red_liet_name) . '</small><br>';
                if (!empty($order['red_dat'])) {
                    echo '<small>' . date('d.m.Y H:i', strtotime($order['red_dat'])) . '</small>';
                }
            }
            echo '</td>';
            echo '<td class="action-buttons">';
            echo '<a href="pasutijumi.php?view=' . $order['id_pasutijums'] . '" class="btn edit-btn"><i class="fas fa-eye"></i> Skatīt</a>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr>';
        echo '<td colspan="8" class="no-records">Nav atrasts neviens pasūtījums ar norādītajiem parametriem</td>';
        echo '</tr>';
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
    exit;
}


if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
    $user_id = null;
    
    if ($lietotajvards) {
        $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("s", $lietotajvards);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id_lietotajs'];
        }
        $stmt->close();
    }
    
    $sql = "UPDATE sparkly_pasutijumi SET statuss = ?, red_liet = ?, red_dat = NOW() WHERE id_pasutijums = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("sii", $new_status, $user_id, $order_id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Pasūtījuma statuss ir atjaunināts.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās atjaunināt statusu: ' . $stmt->error];
    }
    
    $stmt->close();
    echo json_encode($response);
    exit;
}

if (isset($_GET['fetch_order_details']) && isset($_GET['id'])) {
    $order_id = $_GET['id'];
    
    $sql = "SELECT p.*, l.lietotajvards,
                   m.lietotajvards as red_liet_username,
                   m.vards as red_liet_first_name,
                   m.uzvards as red_liet_last_name
            FROM sparkly_pasutijumi p
            JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs
            LEFT JOIN lietotaji_sparkly m ON p.red_liet = m.id_lietotajs
            WHERE p.id_pasutijums = ?";
    
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        if (!empty($order['red_liet_first_name']) && !empty($order['red_liet_last_name'])) {
            $order['red_liet_name'] = $order['red_liet_first_name'] . ' ' . $order['red_liet_last_name'];
        } else if (!empty($order['red_liet_username'])) {
            $order['red_liet_name'] = $order['red_liet_username'];
        } else {
            $order['red_liet_name'] = '';
        }
        
        echo json_encode($order);
    } else {
        echo json_encode(null);
    }
    
    $stmt->close();
    exit;
}

if (isset($_GET['fetch_order_items']) && isset($_GET['id'])) {
    $order_id = $_GET['id'];
    
    $sql = "SELECT i.*, p.attels1, p.nosaukums
            FROM sparkly_pasutijuma_vienumi i
            JOIN produkcija_sprarkly p ON i.produkta_id = p.id_bumba
            WHERE i.pasutijuma_id = ?";
    
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['attels1'])) {
                $row['attels1'] = base64_encode($row['attels1']);
            }
            $items[] = $row;
        }
    }
    
    echo json_encode($items);
    $stmt->close();
    exit;
}

if (isset($_GET['fetch_orders'])) {
    $status_filter = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    
    $sql = "SELECT p.*, l.lietotajvards,
                   m.lietotajvards as red_liet_username,
                   m.vards as red_liet_first_name,
                   m.uzvards as red_liet_last_name
            FROM sparkly_pasutijumi p
            JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs
            LEFT JOIN lietotaji_sparkly m ON p.red_liet = m.id_lietotajs
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($status_filter)) {
        $sql .= " AND p.statuss = ?";
        $params[] = $status_filter;
        $types .= "s";
    }
    
    if (!empty($search)) {
        $sql .= " AND (p.id_pasutijums LIKE ? OR l.lietotajvards LIKE ? OR p.vards LIKE ? OR p.uzvards LIKE ? OR p.epasts LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
        $types .= "sssss";
    }

    if (!empty($date_from)) {
        $sql .= " AND DATE(p.pas_datums) >= ?";
        $params[] = $date_from;
        $types .= "s";
    }
    
    if (!empty($date_to)) {
        $sql .= " AND DATE(p.pas_datums) <= ?";
        $params[] = $date_to;
        $types .= "s";
    }
    
    $sql .= " ORDER BY p.pas_datums DESC";
    

    if (!empty($params)) {
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $savienojums->query($sql);
    }
    
    $orders = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            if (!empty($row['red_liet_first_name']) && !empty($row['red_liet_last_name'])) {
                $row['red_liet_name'] = $row['red_liet_first_name'] . ' ' . $row['red_liet_last_name'];
            } else if (!empty($row['red_liet_username'])) {
                $row['red_liet_name'] = $row['red_liet_username'];
            } else {
                $row['red_liet_name'] = '';
            }
            
            $orders[] = $row;
        }
    }
    
    echo json_encode($orders);
    
    if (isset($stmt)) {
        $stmt->close();
    }
    exit;

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