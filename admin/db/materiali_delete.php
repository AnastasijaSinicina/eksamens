<?php
require_once 'con_db.php';

// Dzēst audumu
if (isset($_POST['delete_audums']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Dzēst audumu no datubāzes
    $sql = "DELETE FROM sparkly_audums WHERE id_audums = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Audums ir izdzēsts.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās dzēst audumu.'];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}

// Dzēst dekorējumu 1
if (isset($_POST['delete_dekorejums1']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Dzēst dekorējumu no datubāzes
    $sql = "DELETE FROM sparkly_dekorejums1 WHERE id_dekorejums1 = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Dekorējums ir izdzēsts.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās dzēst dekorējumu.'];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}

// Dzēst malu figūru
if (isset($_POST['delete_figura']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Dzēst malu figūru no datubāzes
    $sql = "DELETE FROM sparkly_malu_figura WHERE id_malu_figura = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Malu figūra ir izdzēsta.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās dzēst malu figūru.'];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}

// Dzēst formu
if (isset($_POST['delete_forma']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Dzēst formu no datubāzes
    $sql = "DELETE FROM sparkly_formas WHERE id_forma = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Forma ir izdzēsta.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās dzēst formu.'];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}
?>