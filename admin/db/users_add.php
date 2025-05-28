<?php
// users_add.php - Updated with change tracking
require_once 'con_db.php';
session_start();

// Function to get user ID from username
function getUserIdFromUsername($savienojums, $lietotajvards) {
    if (!$lietotajvards) return null;
    
    $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("s", $lietotajvards);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['id_lietotajs'];
    }
    
    $stmt->close();
    return null;
}

// Add user (admin/moder)
if (isset($_POST['add_user'])) {
    $vards = trim($_POST['vards']);
    $uzvards = trim($_POST['uzvards']);
    $lietotajvards = trim($_POST['lietotajvards']);
    $epasts = trim($_POST['epasts']);
    $parole = $_POST['parole'];
    $loma = $_POST['loma'];
    
    // Get creator user ID
    $creator_username = $_SESSION['lietotajvardsSIN'] ?? null;
    $creator_id = getUserIdFromUsername($savienojums, $creator_username);
    
    // Validate required fields
    if (empty($vards) || empty($uzvards) || empty($lietotajvards) || empty($epasts) || empty($parole) || empty($loma)) {
        $response = ['status' => 'error', 'message' => 'Visi lauki ir obligāti.'];
        echo json_encode($response);
        exit;
    }
    
    // Validate role
    if (!in_array($loma, ['admin', 'moder'])) {
        $response = ['status' => 'error', 'message' => 'Nepareiza lietotāja loma.'];
        echo json_encode($response);
        exit;
    }
    
    // Validate email format
    if (!filter_var($epasts, FILTER_VALIDATE_EMAIL)) {
        $response = ['status' => 'error', 'message' => 'Nepareizs e-pasta formāts.'];
        echo json_encode($response);
        exit;
    }
    
    // Check if username or email already exists
    $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ? OR epasts = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("ss", $lietotajvards, $epasts);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response = ['status' => 'error', 'message' => 'Lietotājvārds vai e-pasts jau eksistē.'];
        echo json_encode($response);
        $stmt->close();
        exit;
    }
    $stmt->close();
    
    // Hash the password
    $hashed_password = password_hash($parole, PASSWORD_DEFAULT);
    
    // Insert new user with creator tracking
    $sql = "INSERT INTO lietotaji_sparkly (vards, uzvards, lietotajvards, epasts, parole, loma, izveidots_liet, datums) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("ssssssi", $vards, $uzvards, $lietotajvards, $epasts, $hashed_password, $loma, $creator_id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Lietotājs ir veiksmīgi pievienots.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās pievienot lietotāju: ' . $stmt->error];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}

// Add client (no creator tracking needed as per requirement)
if (isset($_POST['add_client'])) {
    $vards = trim($_POST['vards']);
    $uzvards = trim($_POST['uzvards']);
    $lietotajvards = trim($_POST['lietotajvards']);
    $epasts = trim($_POST['epasts']);
    $parole = $_POST['parole'];
    $loma = 'klients';
    
    // Validate required fields
    if (empty($vards) || empty($uzvards) || empty($lietotajvards) || empty($epasts) || empty($parole)) {
        $response = ['status' => 'error', 'message' => 'Visi lauki ir obligāti.'];
        echo json_encode($response);
        exit;
    }
    
    // Validate email format
    if (!filter_var($epasts, FILTER_VALIDATE_EMAIL)) {
        $response = ['status' => 'error', 'message' => 'Nepareizs e-pasta formāts.'];
        echo json_encode($response);
        exit;
    }
    
    // Check if username or email already exists
    $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ? OR epasts = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("ss", $lietotajvards, $epasts);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response = ['status' => 'error', 'message' => 'Lietotājvārds vai e-pasts jau eksistē.'];
        echo json_encode($response);
        $stmt->close();
        exit;
    }
    $stmt->close();
    
    // Hash the password
    $hashed_password = password_hash($parole, PASSWORD_DEFAULT);
    
    // Insert new client (no creator tracking for clients)
    $sql = "INSERT INTO lietotaji_sparkly (vards, uzvards, lietotajvards, epasts, parole, loma, datums) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("ssssss", $vards, $uzvards, $lietotajvards, $epasts, $hashed_password, $loma);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Klients ir veiksmīgi pievienots.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās pievienot klientu: ' . $stmt->error];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}
?>