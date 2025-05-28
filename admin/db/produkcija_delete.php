<?php

header('Content-Type: application/json');
require_once 'con_db.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Nepaziņa metode.']);
    exit;
}

if (!isset($_POST['delete_product']) || !isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nepareizi parametri.']);
    exit;
}

$id = (int)$_POST['id'];

try {
    $name_sql = "SELECT nosaukums FROM produkcija_sprarkly WHERE id_bumba = ?";
    $name_stmt = $savienojums->prepare($name_sql);
    
    if (!$name_stmt) {
        throw new Exception('Neizdevās sagatavoť vaicājumu: ' . $savienojums->error);
    }
    
    $name_stmt->bind_param("i", $id);
    $name_stmt->execute();
    $name_result = $name_stmt->get_result();
    $product_name = '';
    
    if ($name_result->num_rows > 0) {
        $name_row = $name_result->fetch_assoc();
        $product_name = $name_row['nosaukums'];
    } else {
        $name_stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Produkts netika atrasts.']);
        exit;
    }
    $name_stmt->close();
    
    $sql = "DELETE FROM produkcija_sprarkly WHERE id_bumba = ?";
    $stmt = $savienojums->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Neizdevās sagatavoť dzēšanas vaicājumu: ' . $savienojums->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $stmt->close();
            echo json_encode([
                'status' => 'success', 
                'message' => "Produkts '$product_name' ir veiksmīgi dzēsts."
            ]);
        } else {
            $stmt->close();
            echo json_encode(['status' => 'error', 'message' => 'Produkts netika atrasts vai jau ir dzēsts.']);
        }
    } else {
        $stmt->close();
        throw new Exception('Neizdevās dzēst produktu: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}


if (isset($savienojums)) {
    $savienojums->close();
}
?>