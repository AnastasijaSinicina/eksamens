<?php
// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build the WHERE clause for filtering
$where_conditions = [];
$params = [];
$types = '';

if (!empty($status_filter)) {
    $where_conditions[] = "p.statuss = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $search_term = "%$search%";
    $where_conditions[] = "(p.id_pasutijums LIKE ? OR l.lietotajvards LIKE ? OR p.vards LIKE ? OR p.uzvards LIKE ? OR p.epasts LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sssss';
}

if (!empty($date_from)) {
    $where_conditions[] = "p.pas_datums >= ?";
    $params[] = $date_from . ' 00:00:00';
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "p.pas_datums <= ?";
    $params[] = $date_to . ' 23:59:59';
    $types .= 's';
}

// Build the final SQL query
$query = "SELECT p.*, l.lietotajvards
          FROM sparkly_pasutijumi p
          JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs";

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY p.pas_datums DESC";

// Prepare and execute query with error checking
$stmt = $savienojums->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . $savienojums->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders_result = $stmt->get_result();
?>