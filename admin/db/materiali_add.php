<?php
require_once 'con_db.php';
session_start();


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

if (isset($_POST['add_audums'])) {
    $nosaukums = $_POST['nosaukums'];
    
    $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
    $user_id = getUserIdFromUsername($savienojums, $lietotajvards);
    
    $sql = "INSERT INTO sparkly_audums (nosaukums, izveidots_liet, datums) VALUES (?, ?, NOW())";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("si", $nosaukums, $user_id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Audums ir pievienots.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās pievienot audumu: ' . $stmt->error];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}


if (isset($_POST['add_dekorejums1'])) {
    $nosaukums = $_POST['nosaukums'];
    $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
    $user_id = getUserIdFromUsername($savienojums, $lietotajvards);
    
    if (isset($_FILES['attels']) && $_FILES['attels']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['attels']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $response = ['status' => 'error', 'message' => 'Nepareizs faila formāts. Atļauti: JPG, JPEG, PNG, GIF'];
            echo json_encode($response);
            exit;
        }
        
        if ($_FILES['attels']['size'] > 5242880) {
            $response = ['status' => 'error', 'message' => 'Fails ir pārāk liels. Maksimālais izmērs: 5MB'];
            echo json_encode($response);
            exit;
        }
        
        $attels = file_get_contents($_FILES['attels']['tmp_name']);
        
        if ($attels === false) {
            $response = ['status' => 'error', 'message' => 'Neizdevās nolasīt attēla failu.'];
            echo json_encode($response);
            exit;
        }
        
        $sql = "INSERT INTO sparkly_dekorejums1 (nosaukums, attels, izveidots_liet, datums) VALUES (?, ?, ?, NOW())";
        $stmt = $savienojums->prepare($sql);
        
        if (!$stmt) {
            $response = ['status' => 'error', 'message' => 'Database prepare error: ' . $savienojums->error];
            echo json_encode($response);
            exit;
        }
        
        $null = NULL;
        $stmt->bind_param("sbi", $nosaukums, $null, $user_id);
        $stmt->send_long_data(1, $attels); 
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Dekorējums ir pievienots.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Neizdevās pievienot dekorējumu: ' . $stmt->error];
        }
        $stmt->close();
    } else {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels (form limit)', 
            UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
            UPLOAD_ERR_NO_FILE => 'Neviens fails netika augšupielādēts',
            UPLOAD_ERR_NO_TMP_DIR => 'Trūkst pagaidu direktorijas',
            UPLOAD_ERR_CANT_WRITE => 'Neizdevās ierakstīt failu diskā',
            UPLOAD_ERR_EXTENSION => 'PHP paplašinājums apturēja faila augšupielādi'
        ];
        
        $error_code = $_FILES['attels']['error'] ?? UPLOAD_ERR_NO_FILE;
        $error_message = $error_messages[$error_code] ?? 'Nezināma kļūda';
        
        $response = ['status' => 'error', 'message' => 'Faila augšupielādes kļūda: ' . $error_message];
    }
    
    echo json_encode($response);
    exit;
}



if (isset($_POST['add_figura'])) {
    $nosaukums = $_POST['nosaukums'];
    $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
    $user_id = getUserIdFromUsername($savienojums, $lietotajvards);
    
    
    error_log("File upload check: " . print_r($_FILES, true));
    
    if (isset($_FILES['attels']) && $_FILES['attels']['error'] == UPLOAD_ERR_OK) {
     
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['attels']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $response = ['status' => 'error', 'message' => 'Nepareizs faila formāts. Atļauti: JPG, JPEG, PNG, GIF'];
            echo json_encode($response);
            exit;
        }
        
        if ($_FILES['attels']['size'] > 5242880) {
            $response = ['status' => 'error', 'message' => 'Fails ir pārāk liels. Maksimālais izmērs: 5MB'];
            echo json_encode($response);
            exit;
        }
        
        $attels = file_get_contents($_FILES['attels']['tmp_name']);
        
        if ($attels === false) {
            $response = ['status' => 'error', 'message' => 'Neizdevās nolasīt attēla failu.'];
            echo json_encode($response);
            exit;
        }
    
        error_log("Image size: " . strlen($attels) . " bytes");
        
        $sql = "INSERT INTO sparkly_malu_figura (nosaukums, attels, izveidots_liet, datums) VALUES (?, ?, ?, NOW())";
        $stmt = $savienojums->prepare($sql);
        
        if (!$stmt) {
            $response = ['status' => 'error', 'message' => 'Database prepare error: ' . $savienojums->error];
            echo json_encode($response);
            exit;
        }
        
        $null = NULL;
        $stmt->bind_param("sbi", $nosaukums, $null, $user_id);
        $stmt->send_long_data(1, $attels); 
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Malu figūra ir pievienota.'];
        } else {
            error_log("SQL Error: " . $stmt->error);
            $response = ['status' => 'error', 'message' => 'Neizdevās pievienot malu figūru: ' . $stmt->error];
        }
        $stmt->close();
    } else {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels (form limit)', 
            UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
            UPLOAD_ERR_NO_FILE => 'Neviens fails netika augšupielādēts',
            UPLOAD_ERR_NO_TMP_DIR => 'Trūkst pagaidu direktorijas',
            UPLOAD_ERR_CANT_WRITE => 'Neizdevās ierakstīt failu diskā',
            UPLOAD_ERR_EXTENSION => 'PHP paplašinājums apturēja faila augšupielādi'
        ];
        
        $error_code = $_FILES['attels']['error'] ?? UPLOAD_ERR_NO_FILE;
        $error_message = $error_messages[$error_code] ?? 'Nezināma kļūda';
        
        error_log("Upload error: " . $error_code . " - " . $error_message);
        $response = ['status' => 'error', 'message' => 'Faila augšupielādes kļūda: ' . $error_message];
    }
    
    echo json_encode($response);
    exit;
}

if (isset($_POST['add_forma'])) {
    $forma = $_POST['forma'];
    $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
    $user_id = getUserIdFromUsername($savienojums, $lietotajvards);
    
    $sql = "INSERT INTO sparkly_formas (forma, izveidots_liet, datums) VALUES (?, ?, NOW())";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("si", $forma, $user_id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Forma ir pievienota.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās pievienot formu: ' . $stmt->error];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}
?>