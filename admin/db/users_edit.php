<?php
// users_edit.php - Updated with change tracking
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

// Edit user (admin/moder)
if (isset($_POST['edit_user'])) {
    try {
        // Basic validation
        if (empty($_POST['id']) || empty($_POST['vards']) || empty($_POST['uzvards']) || 
            empty($_POST['lietotajvards']) || empty($_POST['epasts']) || empty($_POST['loma'])) {
            throw new Exception('Visi lauki (izņemot paroli) ir obligāti.');
        }
        
        $id = intval($_POST['id']);
        $vards = trim($_POST['vards']);
        $uzvards = trim($_POST['uzvards']);
        $lietotajvards = trim($_POST['lietotajvards']);
        $epasts = trim($_POST['epasts']);
        $parole = $_POST['parole'] ?? '';
        $loma = $_POST['loma'];
        
        // Get editor user ID
        $editor_username = $_SESSION['lietotajvardsSIN'] ?? null;
        $editor_id = getUserIdFromUsername($savienojums, $editor_username);
        
        // Validate role
        if (!in_array($loma, ['admin', 'moder'])) {
            throw new Exception('Nepareiza lietotāja loma.');
        }
        
        // Validate email format
        if (!filter_var($epasts, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Nepareizs e-pasta formāts.');
        }
        
        // Check if username or email already exists for other users
        $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE (lietotajvards = ? OR epasts = ?) AND id_lietotajs != ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("ssi", $lietotajvards, $epasts, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Lietotājvārds vai e-pasts jau eksistē citam lietotājam.');
        }
        $stmt->close();
        
        // Prepare SQL statement based on whether password is being updated
        if (!empty($parole)) {
            // Update with new password
            $hashed_password = password_hash($parole, PASSWORD_DEFAULT);
            $sql = "UPDATE lietotaji_sparkly SET vards = ?, uzvards = ?, lietotajvards = ?, epasts = ?, parole = ?, loma = ?, red_liet = ?, red_dat = NOW() WHERE id_lietotajs = ? AND loma IN ('admin', 'moder')";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
            }
            
            $stmt->bind_param("ssssssii", $vards, $uzvards, $lietotajvards, $epasts, $hashed_password, $loma, $editor_id, $id);
            
        } else {
            // Update without changing password
            $sql = "UPDATE lietotaji_sparkly SET vards = ?, uzvards = ?, lietotajvards = ?, epasts = ?, loma = ?, red_liet = ?, red_dat = NOW() WHERE id_lietotajs = ? AND loma IN ('admin', 'moder')";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
            }
            
            $stmt->bind_param("sssssii", $vards, $uzvards, $lietotajvards, $epasts, $loma, $editor_id, $id);
        }
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            if ($affected_rows > 0) {
                $response = ['status' => 'success', 'message' => 'Lietotājs ir veiksmīgi atjaunināts.'];
            } else {
                $response = ['status' => 'warning', 'message' => 'Nav veiktas izmaiņas vai lietotājs nav atrasts.'];
            }
        } else {
            throw new Exception('Neizdevās atjaunināt lietotāju: ' . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}

// Edit client
if (isset($_POST['edit_client'])) {
    try {
        // Basic validation
        if (empty($_POST['id']) || empty($_POST['vards']) || empty($_POST['uzvards']) || 
            empty($_POST['lietotajvards']) || empty($_POST['epasts'])) {
            throw new Exception('Visi lauki (izņemot paroli) ir obligāti.');
        }
        
        $id = intval($_POST['id']);
        $vards = trim($_POST['vards']);
        $uzvards = trim($_POST['uzvards']);
        $lietotajvards = trim($_POST['lietotajvards']);
        $epasts = trim($_POST['epasts']);
        $parole = $_POST['parole'] ?? '';
        
        // Get editor user ID for clients
        $editor_username = $_SESSION['lietotajvardsSIN'] ?? null;
        $editor_id = getUserIdFromUsername($savienojums, $editor_username);
        
        // Validate email format
        if (!filter_var($epasts, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Nepareizs e-pasta formāts.');
        }
        
        // Check if username or email already exists for other users
        $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE (lietotajvards = ? OR epasts = ?) AND id_lietotajs != ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("ssi", $lietotajvards, $epasts, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Lietotājvārds vai e-pasts jau eksistē citam lietotājam.');
        }
        $stmt->close();
        
        // Prepare SQL statement based on whether password is being updated
        if (!empty($parole)) {
            // Update with new password
            $hashed_password = password_hash($parole, PASSWORD_DEFAULT);
            $sql = "UPDATE lietotaji_sparkly SET vards = ?, uzvards = ?, lietotajvards = ?, epasts = ?, parole = ?, red_liet = ?, red_dat = NOW() WHERE id_lietotajs = ? AND loma = 'klients'";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
            }
            
            $stmt->bind_param("sssssii", $vards, $uzvards, $lietotajvards, $epasts, $hashed_password, $editor_id, $id);
            
        } else {
            // Update without changing password
            $sql = "UPDATE lietotaji_sparkly SET vards = ?, uzvards = ?, lietotajvards = ?, epasts = ?, red_liet = ?, red_dat = NOW() WHERE id_lietotajs = ? AND loma = 'klients'";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
            }
            
            $stmt->bind_param("ssssii", $vards, $uzvards, $lietotajvards, $epasts, $editor_id, $id);
        }
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            if ($affected_rows > 0) {
                $response = ['status' => 'success', 'message' => 'Klients ir veiksmīgi atjaunināts.'];
            } else {
                $response = ['status' => 'warning', 'message' => 'Nav veiktas izmaiņas vai klients nav atrasts.'];
            }
        } else {
            throw new Exception('Neizdevās atjaunināt klientu: ' . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}
?>