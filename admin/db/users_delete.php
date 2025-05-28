<?php
// users_delete.php - Delete user queries
require_once 'con_db.php';
session_start();

// Delete user (admin/moder)
if (isset($_POST['delete_user'])) {
    $id = intval($_POST['id']);
    
    if (!$id) {
        $response = ['status' => 'error', 'message' => 'Nepareizs lietotāja ID.'];
        echo json_encode($response);
        exit;
    }
    
    // Check if user exists and is admin/moder
    $sql = "SELECT id_lietotajs, lietotajvards FROM lietotaji_sparkly WHERE id_lietotajs = ? AND loma IN ('admin', 'moder')";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $response = ['status' => 'error', 'message' => 'Lietotājs nav atrasts vai nav administrators/moderators.'];
        echo json_encode($response);
        $stmt->close();
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Prevent deleting yourself (if session username matches)
    if (isset($_SESSION['lietotajvardsSIN']) && $_SESSION['lietotajvardsSIN'] === $user['lietotajvards']) {
        $response = ['status' => 'error', 'message' => 'Jūs nevarat dzēst savu pašu kontu.'];
        echo json_encode($response);
        exit;
    }
    
    // Delete the user
    $sql = "DELETE FROM lietotaji_sparkly WHERE id_lietotajs = ? AND loma IN ('admin', 'moder')";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response = ['status' => 'success', 'message' => 'Lietotājs ir veiksmīgi izdzēsts.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Lietotājs nav atrasts vai nevar tikt dzēsts.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās dzēst lietotāju: ' . $stmt->error];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}

// Delete client
if (isset($_POST['delete_client'])) {
    $id = intval($_POST['id']);
    
    if (!$id) {
        $response = ['status' => 'error', 'message' => 'Nepareizs klienta ID.'];
        echo json_encode($response);
        exit;
    }
    
    // Check if client exists
    $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE id_lietotajs = ? AND loma = 'klients'";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $response = ['status' => 'error', 'message' => 'Klients nav atrasts.'];
        echo json_encode($response);
        $stmt->close();
        exit;
    }
    $stmt->close();
    
    // TODO: Check if client has any orders or related data before deletion
    // You might want to add checks for related records in other tables
    
    // Delete the client
    $sql = "DELETE FROM lietotaji_sparkly WHERE id_lietotajs = ? AND loma = 'klients'";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response = ['status' => 'success', 'message' => 'Klients ir veiksmīgi izdzēsts.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Klients nav atrasts vai nevar tikt dzēsts.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās dzēst klientu: ' . $stmt->error];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}
?>