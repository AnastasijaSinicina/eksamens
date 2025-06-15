<?php
require_once 'con_db.php';
session_start();

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
                  FROM sparkly_pasutijumi p
                  JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs
                  LEFT JOIN lietotaji_sparkly m ON p.red_liet = m.id_lietotajs
                  WHERE 1=1";
    
    // Main query for fetching records
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
    
    // Add filters to both queries
    if (!empty($status_filter)) {
        $count_sql .= " AND p.statuss = ?";
        $sql .= " AND p.statuss = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    if (!empty($search)) {
        $search_condition = " AND (p.id_pasutijums LIKE ? OR l.lietotajvards LIKE ? OR p.vards LIKE ? OR p.uzvards LIKE ? OR p.epasts LIKE ?)";
        $count_sql .= $search_condition;
        $sql .= $search_condition;
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
        $types .= "sssss";
    }

    if (!empty($date_from)) {
        $count_sql .= " AND DATE(p.pas_datums) >= ?";
        $sql .= " AND DATE(p.pas_datums) >= ?";
        $params[] = $date_from;
        $types .= "s";
    }
    
    if (!empty($date_to)) {
        $count_sql .= " AND DATE(p.pas_datums) <= ?";
        $sql .= " AND DATE(p.pas_datums) <= ?";
        $params[] = $date_to;
        $types .= "s";
    }
    
    // Get total count
    if (!empty($params)) {
        $count_stmt = $savienojums->prepare($count_sql);
        if ($count_stmt) {
            $count_stmt->bind_param($types, ...$params);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $total_records = $count_result->fetch_assoc()['total'];
            $count_stmt->close();
        } else {
            $total_records = 0;
        }
    } else {
        $count_result = $savienojums->query($count_sql);
        $total_records = $count_result ? $count_result->fetch_assoc()['total'] : 0;
    }
    
    $total_pages = ceil($total_records / $limit);
    
    // Add ordering and pagination to main query
    $sql .= " ORDER BY p.pas_datums DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    // Execute main query
    if (!empty($params)) {
        $stmt = $savienojums->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = false;
        }
    } else {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $savienojums->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = false;
        }
    }
    
    // Return JSON response with pagination info
    if (isset($_POST['return_json']) || isset($_GET['return_json'])) {
        $orders = [];
        if ($result && $result->num_rows > 0) {
            while ($order = $result->fetch_assoc()) {
                if (!empty($order['red_liet_first_name']) && !empty($order['red_liet_last_name'])) {
                    $red_liet_name = $order['red_liet_first_name'] . ' ' . $order['red_liet_last_name'];
                } else if (!empty($order['red_liet_username'])) {
                    $red_liet_name = $order['red_liet_username'];
                } else {
                    $red_liet_name = '';
                }
                $order['red_liet_name'] = $red_liet_name;
                $orders[] = $order;
            }
        }
        
        header('Content-Type: application/json');
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
            if (!empty($order['red_liet_first_name']) && !empty($order['red_liet_last_name'])) {
                $red_liet_name = $order['red_liet_first_name'] . ' ' . $order['red_liet_last_name'];
            } else if (!empty($order['red_liet_username'])) {
                $red_liet_name = $order['red_liet_username'];
            } else {
                $red_liet_name = '';
            }
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($order['id_pasutijums']) . '</td>';
            echo '<td>';
            echo '<div class="client-info">';
            echo '<div>' . htmlspecialchars($order['vards'] . ' ' . $order['uzvards']) . '</div>';
            echo '<small>' . htmlspecialchars($order['lietotajvards']) . '</small>';
            echo '</div>';
            echo '</td>';
            echo '<td>' . date('d.m.Y H:i', strtotime($order['pas_datums'])) . '</td>';
            echo '<td>' . number_format($order['kopeja_cena'], 2) . '€</td>';
            echo '<td>' . htmlspecialchars($order['produktu_skaits']) . '</td>';
            echo '<td>';
            echo '<span class="status ' . strtolower($order['statuss']) . '">' . htmlspecialchars($order['statuss']) . '</span>';
            echo '</td>';
            echo '<td>';
            if (!empty($red_liet_name)) {
                echo '<small>' . htmlspecialchars($red_liet_name) . '</small>';
                if (!empty($order['red_dat'])) {
                    echo '<br><small>' . date('d.m.Y H:i', strtotime($order['red_dat'])) . '</small>';
                }
            } else {
                echo '<span class="text-muted">-</span>';
            }
            echo '</td>';
            echo '<td class="action-buttons">';
            echo '<a href="pasutijumi.php?view=' . htmlspecialchars($order['id_pasutijums']) . '" class="btn"><i class="fas fa-eye"></i> Skatīt</a>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr>';
        echo '<td colspan="8" class="no-records">Nav atrasts neviens pasūtījums ar norādītajiem parametriem</td>';
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

// Handle status update
if (isset($_POST['update_status'])) {
    // Set proper content type for JSON response
    header('Content-Type: application/json');
    
    // Validate required fields
    if (empty($_POST['order_id']) || empty($_POST['new_status'])) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Trūkst nepieciešamie dati - pasūtījuma ID vai statuss nav norādīts'
        ]);
        exit;
    }
    
    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['new_status']);
    
    // Validate order ID
    if ($order_id <= 0) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Nederīgs pasūtījuma ID'
        ]);
        exit;
    }
    
    // Validate status
    $allowed_statuses = ['Iesniegts', 'Apstiprināts', 'Izgatavo', 'Saņemts'];
    if (!in_array($new_status, $allowed_statuses)) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Nederīgs pasūtījuma statuss'
        ]);
        exit;
    }
    
    $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
    $user_id = null;
    
    if ($lietotajvards) {
        $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?";
        $stmt = $savienojums->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $lietotajvards);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_id = $row['id_lietotajs'];
            }
            $stmt->close();
        }
    }
    
    // Check if order exists
    $check_sql = "SELECT id_pasutijums, statuss FROM sparkly_pasutijumi WHERE id_pasutijums = ?";
    $check_stmt = $savienojums->prepare($check_sql);
    if (!$check_stmt) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Datubāzes kļūda: neizdevās sagatavot vaicājumu'
        ]);
        exit;
    }
    
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $check_stmt->close();
        echo json_encode([
            'status' => 'error', 
            'message' => 'Pasūtījums ar ID #' . $order_id . ' nav atrasts'
        ]);
        exit;
    }
    
    $current_order = $check_result->fetch_assoc();
    $check_stmt->close();
    
    // Check if status is actually changing
    if ($current_order['statuss'] === $new_status) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Pasūtījuma statuss jau ir "' . $new_status . '"'
        ]);
        exit;
    }
    
    // Update the order status
    $sql = "UPDATE sparkly_pasutijumi SET statuss = ?, red_liet = ?, red_dat = NOW() WHERE id_pasutijums = ?";
    $stmt = $savienojums->prepare($sql);
    
    if (!$stmt) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Datubāzes kļūda: neizdevās sagatavot atjaunināšanas vaicājumu'
        ]);
        exit;
    }
    
    $stmt->bind_param("sii", $new_status, $user_id, $order_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response = [
                'status' => 'success', 
                'message' => 'Pasūtījuma #' . $order_id . ' statuss ir veiksmīgi mainīts uz "' . $new_status . '"'
            ];
        } else {
            $response = [
                'status' => 'warning', 
                'message' => 'Pasūtījuma statuss netika mainīts - iespējams, nav izmaiņu'
            ];
        }
    } else {
        $response = [
            'status' => 'error', 
            'message' => 'Neizdevās atjaunināt statusu datubāzē: ' . $stmt->error
        ];
    }
    
    $stmt->close();
    echo json_encode($response);
    exit;
}

// Handle fetch order details
if (isset($_GET['fetch_order_details']) && isset($_GET['id'])) {
    header('Content-Type: application/json');
    
    $order_id = intval($_GET['id']);
    
    if ($order_id <= 0) {
        echo json_encode(['error' => 'Nederīgs pasūtījuma ID']);
        exit;
    }
    
    $sql = "SELECT p.*, l.lietotajvards,
                   m.lietotajvards as red_liet_username,
                   m.vards as red_liet_first_name,
                   m.uzvards as red_liet_last_name
            FROM sparkly_pasutijumi p
            JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs
            LEFT JOIN lietotaji_sparkly m ON p.red_liet = m.id_lietotajs
            WHERE p.id_pasutijums = ?";
    
    $stmt = $savienojums->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Datubāzes kļūda']);
        exit;
    }
    
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
        echo json_encode(['error' => 'Pasūtījums nav atrasts']);
    }
    
    $stmt->close();
    exit;
}

// Handle fetch order items
if (isset($_GET['fetch_order_items']) && isset($_GET['id'])) {
    header('Content-Type: application/json');
    
    $order_id = intval($_GET['id']);
    
    if ($order_id <= 0) {
        echo json_encode(['error' => 'Nederīgs pasūtījuma ID']);
        exit;
    }
    
    $sql = "SELECT i.*, p.attels1, p.nosaukums
            FROM sparkly_pasutijuma_vienumi i
            JOIN produkcija_sprarkly p ON i.produkta_id = p.id_bumba
            WHERE i.pasutijuma_id = ?";
    
    $stmt = $savienojums->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Datubāzes kļūda']);
        exit;
    }
    
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
?>