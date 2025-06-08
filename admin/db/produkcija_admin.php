<?php
require_once 'con_db.php';

// Check if related tables exist
$check_formas_table_sql = "SHOW TABLES LIKE 'sparkly_formas'";
$formas_table_exists = $savienojums->query($check_formas_table_sql)->num_rows > 0;

$check_audumi_table_sql = "SHOW TABLES LIKE 'sparkly_audums'";
$audumi_table_exists = $savienojums->query($check_audumi_table_sql)->num_rows > 0;

$check_malu_figura_table_sql = "SHOW TABLES LIKE 'sparkly_malu_figura'";
$malu_figura_table_exists = $savienojums->query($check_malu_figura_table_sql)->num_rows > 0;

$check_dekorejums1_table_sql = "SHOW TABLES LIKE 'sparkly_dekorejums1'";
$dekorejums1_table_exists = $savienojums->query($check_dekorejums1_table_sql)->num_rows > 0;

// Handle AJAX requests for products list with pagination
if ((isset($_GET['ajax']) && $_GET['ajax'] == '1') || (isset($_POST['ajax']) && $_POST['ajax'] == '1')) {
    $search = $_POST['search'] ?? $_GET['search'] ?? '';
    $filter = $_POST['filter'] ?? $_GET['filter'] ?? '';
    $page = intval($_POST['page'] ?? $_GET['page'] ?? 1);
    $limit = 10; // Products per page
    $offset = ($page - 1) * $limit;
    
    // Build dynamic SELECT for count query
    $count_sql = "SELECT COUNT(*) as total FROM produkcija_sprarkly p ";
    
    // Build dynamic SELECT for main query
    $sql = "SELECT p.*, ";
    
    if ($formas_table_exists) {
        $sql .= "f.forma AS forma_name, ";
    } else {
        $sql .= "p.forma AS forma_name, ";
    }

    if ($audumi_table_exists) {
        $sql .= "a.nosaukums AS audums_name, ";
    } else {
        $sql .= "p.audums_id AS audums_name, ";
    }

    if ($malu_figura_table_exists) {
        $sql .= "m.nosaukums AS malu_figura_name, ";
    } else {
        $sql .= "p.figura_id AS malu_figura_name, ";
    }

    if ($dekorejums1_table_exists) {
        $sql .= "d1.nosaukums AS dekorejums1_name, ";
    } else {
        $sql .= "p.dekorejums1_id AS dekorejums1_name, ";
    }

    $sql .= "creator.lietotajvards as created_username, ";
    $sql .= "creator.vards as created_first_name, ";
    $sql .= "creator.uzvards as created_last_name, ";
    $sql .= "editor.lietotajvards as updated_username, ";
    $sql .= "editor.vards as updated_first_name, ";
    $sql .= "editor.uzvards as updated_last_name, ";
    $sql .= "p.izveidots_liet, p.timestamp as created_at, p.red_liet, p.red_dat as updated_at ";
    $sql .= "FROM produkcija_sprarkly p ";

    // Add JOINs for both queries
    $joins = "";
    if ($formas_table_exists) {
        $joins .= "LEFT JOIN sparkly_formas f ON p.forma = f.id_forma ";
    }
    if ($audumi_table_exists) {
        $joins .= "LEFT JOIN sparkly_audums a ON p.audums_id = a.id_audums ";
    }
    if ($malu_figura_table_exists) {
        $joins .= "LEFT JOIN sparkly_malu_figura m ON p.figura_id = m.id_malu_figura ";
    }
    if ($dekorejums1_table_exists) {
        $joins .= "LEFT JOIN sparkly_dekorejums1 d1 ON p.dekorejums1_id = d1.id_dekorejums1 ";
    }
    $joins .= "LEFT JOIN lietotaji_sparkly creator ON p.izveidots_liet = creator.id_lietotajs ";
    $joins .= "LEFT JOIN lietotaji_sparkly editor ON p.red_liet = editor.id_lietotajs ";
    
    $count_sql .= $joins;
    $sql .= $joins;
    
    $where_conditions = ["1=1"];
    $params = [];
    $types = "";
    
    // Add search filter
    if (!empty($search)) {
        $where_conditions[] = "(p.nosaukums LIKE ? OR p.id_bumba LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }
    
    // Add date filter
    if (!empty($filter)) {
        if ($filter === 'recent') {
            $where_conditions[] = "p.timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        } elseif ($filter === 'updated') {
            $where_conditions[] = "p.red_dat >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }
    }
    
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
    $count_sql .= $where_clause;
    $sql .= $where_clause;
    
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
    $sql .= " ORDER BY p.id_bumba DESC LIMIT ? OFFSET ?";
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
        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($product = $result->fetch_assoc()) {
                $products[] = $product;
            }
        }
        
        echo json_encode([
            'products' => $products,
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
        while ($row = $result->fetch_assoc()) {
            echo "<tr data-product-id='{$row['id_bumba']}'>";
            echo "<td>{$row['id_bumba']}</td>";
            
            // Display first image as preview
            if (!empty($row['attels1'])) {
                echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['attels1']) . "' alt='" . htmlspecialchars($row['nosaukums']) . "' class='product-thumbnail'></td>";
            } else {
                echo "<td><div class='no-image'><i class='fas fa-image'></i></div></td>";
            }
            
            echo "<td class='product-name'>" . htmlspecialchars($row['nosaukums']) . "</td>";
            echo "<td>" . htmlspecialchars($row['forma_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['audums_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['malu_figura_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['dekorejums1_name']) . "</td>";
            echo "<td class='price'>€" . number_format($row['cena'], 2) . "</td>";
            
            // Created info
            $created_info = '';
            if (!empty($row['created_first_name']) && !empty($row['created_last_name'])) {
                $created_info .= htmlspecialchars($row['created_first_name'] . ' ' . $row['created_last_name']);
            } elseif (!empty($row['izveidots_liet'])) {
                $created_info .= 'Lietotājs ID: ' . $row['izveidots_liet'];
            }
            
            if (!empty($row['created_at'])) {
                $created_info .= '<br><small>' . date('d.m.Y H:i', strtotime($row['created_at'])) . '</small>';
            }
            echo "<td class='metadata'>" . ($created_info) . "</td>";
            
            // Updated info
            $updated_info = '';
            if (!empty($row['updated_first_name']) && !empty($row['updated_last_name'])) {
                $updated_info .= htmlspecialchars($row['updated_first_name'] . ' ' . $row['updated_last_name']);
            } elseif (!empty($row['red_liet'])) {
                $updated_info .= 'Lietotājs ID: ' . $row['red_liet'];
            } else {
                $updated_info = '';
            }
            
            if (!empty($row['updated_at'])) {
                $updated_info .= ($updated_info ? '<br>' : '') . '<small>' . date('d.m.Y H:i', strtotime($row['updated_at'])) . '</small>';
            }
            echo "<td class='metadata'>" . ($updated_info ?: 'Nav atjaunināts') . "</td>";
            
            // Action buttons
            echo "<td class='action-buttons'>";
            echo "<a href='produkcija_form.php?edit={$row['id_bumba']}' class='btn edit-btn' title='Rediģēt produktu'>";
            echo "<i class='fas fa-edit'></i>";
            echo "</a>";
            echo "<button onclick='deleteProduct({$row['id_bumba']}, \"" . htmlspecialchars(addslashes($row['nosaukums'])) . "\")' class='btn delete-btn' title='Dzēst produktu'>";
            echo "<i class='fas fa-trash-alt'></i>";
            echo "</button>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='11' class='no-records'>";
        echo "<div class='empty-state'>";
        echo "<i class='fas fa-box-open'></i>";
        echo "<h3>Nav atrasts neviens produkts</h3>";
        echo "<p>Nav atrasts neviens produkts ar norādītajiem parametriem.</p>";
        echo "</div>";
        echo "</td></tr>";
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

// Build main query for initial page load (non-AJAX)
$sql = "SELECT p.*, ";

if ($formas_table_exists) {
    $sql .= "f.forma AS forma_name, ";
} else {
    $sql .= "p.forma AS forma_name, ";
}

if ($audumi_table_exists) {
    $sql .= "a.nosaukums AS audums_name, ";
} else {
    $sql .= "p.audums_id AS audums_name, ";
}

if ($malu_figura_table_exists) {
    $sql .= "m.nosaukums AS malu_figura_name, ";
} else {
    $sql .= "p.figura_id AS malu_figura_name, ";
}

if ($dekorejums1_table_exists) {
    $sql .= "d1.nosaukums AS dekorejums1_name, ";
} else {
    $sql .= "p.dekorejums1_id AS dekorejums1_name, ";
}

$sql .= "creator.lietotajvards as created_username, ";
$sql .= "creator.vards as created_first_name, ";
$sql .= "creator.uzvards as created_last_name, ";
$sql .= "editor.lietotajvards as updated_username, ";
$sql .= "editor.vards as updated_first_name, ";
$sql .= "editor.uzvards as updated_last_name, ";
$sql .= "p.izveidots_liet, p.timestamp as created_at, p.red_liet, p.red_dat as updated_at ";
$sql .= "FROM produkcija_sprarkly p ";

if ($formas_table_exists) {
    $sql .= "LEFT JOIN sparkly_formas f ON p.forma = f.id_forma ";
}

if ($audumi_table_exists) {
    $sql .= "LEFT JOIN sparkly_audums a ON p.audums_id = a.id_audums ";
}

if ($malu_figura_table_exists) {
    $sql .= "LEFT JOIN sparkly_malu_figura m ON p.figura_id = m.id_malu_figura ";
}

if ($dekorejums1_table_exists) {
    $sql .= "LEFT JOIN sparkly_dekorejums1 d1 ON p.dekorejums1_id = d1.id_dekorejums1 ";
}

$sql .= "LEFT JOIN lietotaji_sparkly creator ON p.izveidots_liet = creator.id_lietotajs ";
$sql .= "LEFT JOIN lietotaji_sparkly editor ON p.red_liet = editor.id_lietotajs ";

$sql .= "ORDER BY p.id_bumba DESC LIMIT 10";

$products_result = $savienojums->query($sql);

if (!$products_result) {
    echo "Query Error: " . $savienojums->error;
}

// Get options for dropdowns (existing code)
$formas_options = [];
if ($formas_table_exists) {
    $forma_sql = "SELECT * FROM sparkly_formas ORDER BY forma";
    $forma_result = $savienojums->query($forma_sql);
    if ($forma_result && $forma_result->num_rows > 0) {
        while ($forma_row = $forma_result->fetch_assoc()) {
            $formas_options[] = $forma_row;
        }
    }
}

$audums_options = [];
if ($audumi_table_exists) {
    $audums_sql = "SELECT * FROM sparkly_audums ORDER BY nosaukums";
    $audums_result = $savienojums->query($audums_sql);
    if ($audums_result && $audums_result->num_rows > 0) {
        while ($audums_row = $audums_result->fetch_assoc()) {
            $audums_options[] = $audums_row;
        }
    }
}

$malu_figura_options = [];
if ($malu_figura_table_exists) {
    $malu_figura_sql = "SELECT * FROM sparkly_malu_figura ORDER BY nosaukums";
    $malu_figura_result = $savienojums->query($malu_figura_sql);
    if ($malu_figura_result && $malu_figura_result->num_rows > 0) {
        while ($malu_figura_row = $malu_figura_result->fetch_assoc()) {
            $malu_figura_options[] = $malu_figura_row;
        }
    }
}

$dekorejums1_options = [];
if ($dekorejums1_table_exists) {
    $dekorejums1_sql = "SELECT * FROM sparkly_dekorejums1 ORDER BY nosaukums";
    $dekorejums1_result = $savienojums->query($dekorejums1_sql);
    if ($dekorejums1_result && $dekorejums1_result->num_rows > 0) {
        while ($dekorejums1_row = $dekorejums1_result->fetch_assoc()) {
            $dekorejums1_options[] = $dekorejums1_row;
        }
    }
}

// Get single product (existing code)
if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $product_sql = "SELECT p.*, 
                    creator.lietotajvards as created_username,
                    creator.vards as created_first_name,
                    creator.uzvards as created_last_name,
                    editor.lietotajvards as updated_username,
                    editor.vards as updated_first_name,
                    editor.uzvards as updated_last_name,
                    p.izveidots_liet as created_by, 
                    p.timestamp as created_at, 
                    p.red_liet as updated_by, 
                    p.red_dat as updated_at 
                    FROM produkcija_sprarkly p
                    LEFT JOIN lietotaji_sparkly creator ON p.izveidots_liet = creator.id_lietotajs
                    LEFT JOIN lietotaji_sparkly editor ON p.red_liet = editor.id_lietotajs
                    WHERE p.id_bumba = ?";
    $product_stmt = $savienojums->prepare($product_sql);
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    $single_product = ($product_result->num_rows > 0) ? $product_result->fetch_assoc() : null;
    $product_stmt->close();
}
?>