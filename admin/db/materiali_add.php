<?php
require_once 'con_db.php';
session_start();

// Pievienot audumu
if (isset($_POST['add_audums'])) {
    $nosaukums = $_POST['nosaukums'];
    
    // Pārbaudīt vai lietotājs ir pieslēdzies
    if (isset($_SESSION['lietotajvardsSIN'])) {
        $lietotajvards = $_SESSION['lietotajvardsSIN'];
        
        // Iegūt lietotāja ID
        $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("s", $lietotajvards);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id_lietotajs'];
            $stmt->close();
            
            // Pievienot audumu
            $sql = "INSERT INTO sparkly_audums (nosaukums, izveidots_liet, datums) VALUES (?, ?, NOW())";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("si", $nosaukums, $user_id);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Audums ir pievienots.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Neizdevās pievienot audumu: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            $response = ['status' => 'error', 'message' => 'Lietotājs nav atrasts.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Lietotājs nav pieslēdzies.'];
    }
    
    echo json_encode($response);
    exit;
}

// Pievienot dekorējumu 1
if (isset($_POST['add_dekorejums1'])) {
    $nosaukums = $_POST['nosaukums'];
    
    // Pārbaudīt vai lietotājs ir pieslēdzies
    if (isset($_SESSION['lietotajvardsSIN'])) {
        $lietotajvards = $_SESSION['lietotajvardsSIN'];
        
        // Iegūt lietotāja ID
        $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("s", $lietotajvards);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id_lietotajs'];
            $stmt->close();
            
            // Pārbaudīt vai fails tika augšupielādēts
            if (isset($_FILES['attels']) && $_FILES['attels']['error'] == UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $file_type = $_FILES['attels']['type'];
                
                // Pārbaudīt faila tipu
                if (!in_array($file_type, $allowed_types)) {
                    $response = ['status' => 'error', 'message' => 'Nepareizs faila formāts. Atļauti: JPG, JPEG, PNG, GIF'];
                    echo json_encode($response);
                    exit;
                }
                
                // Pārbaudīt faila izmēru (5MB maksimums)
                if ($_FILES['attels']['size'] > 5242880) {
                    $response = ['status' => 'error', 'message' => 'Fails ir pārāk liels. Maksimālais izmērs: 5MB'];
                    echo json_encode($response);
                    exit;
                }
                
                // Nolasīt attēla saturu
                $attels = file_get_contents($_FILES['attels']['tmp_name']);
                
                if ($attels === false) {
                    $response = ['status' => 'error', 'message' => 'Neizdevās nolasīt attēla failu.'];
                    echo json_encode($response);
                    exit;
                }
                
                // Sagatavot SQL vaicājumu
                $sql = "INSERT INTO sparkly_dekorejums1 (nosaukums, attels, izveidots_liet, datums) VALUES (?, ?, ?, NOW())";
                $stmt = $savienojums->prepare($sql);
                
                if (!$stmt) {
                    $response = ['status' => 'error', 'message' => 'Datubāzes kļūda: ' . $savienojums->error];
                    echo json_encode($response);
                    exit;
                }
                
                // Pievienot datus datubāzē
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
                // Faila augšupielādes kļūdu ziņojumi
                $error_messages = [
                    UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels (servera ierobežojums)',
                    UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels (formas ierobežojums)', 
                    UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
                    UPLOAD_ERR_NO_FILE => 'Neviens fails netika augšupielādēts',
                    UPLOAD_ERR_NO_TMP_DIR => 'Trūkst pagaidu direktorijas',
                    UPLOAD_ERR_CANT_WRITE => 'Neizdevās ierakstīt failu diskā',
                    UPLOAD_ERR_EXTENSION => 'PHP paplašinājums apturēja faila augšupielādi'
                ];
                
                $error_code = isset($_FILES['attels']) ? $_FILES['attels']['error'] : UPLOAD_ERR_NO_FILE;
                $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Nezināma kļūda';
                
                $response = ['status' => 'error', 'message' => 'Faila augšupielādes kļūda: ' . $error_message];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Lietotājs nav atrasts.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Lietotājs nav pieslēdzies.'];
    }
    
    echo json_encode($response);
    exit;
}

// Pievienot malu figūru
if (isset($_POST['add_figura'])) {
    $nosaukums = $_POST['nosaukums'];
    
    // Pārbaudīt vai lietotājs ir pieslēdzies
    if (isset($_SESSION['lietotajvardsSIN'])) {
        $lietotajvards = $_SESSION['lietotajvardsSIN'];
        
        // Iegūt lietotāja ID
        $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("s", $lietotajvards);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id_lietotajs'];
            $stmt->close();
            
            // Žurnāla ieraksts faila pārbaudes nolūkos
            error_log("Faila augšupielādes pārbaude: " . print_r($_FILES, true));
            
            // Pārbaudīt vai fails tika augšupielādēts
            if (isset($_FILES['attels']) && $_FILES['attels']['error'] == UPLOAD_ERR_OK) {
                // Atļautie failu tipi
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $file_type = $_FILES['attels']['type'];
                
                // Pārbaudīt faila tipu
                if (!in_array($file_type, $allowed_types)) {
                    $response = ['status' => 'error', 'message' => 'Nepareizs faila formāts. Atļauti: JPG, JPEG, PNG, GIF'];
                    echo json_encode($response);
                    exit;
                }
                
                // Pārbaudīt faila izmēru (5MB maksimums)
                if ($_FILES['attels']['size'] > 5242880) {
                    $response = ['status' => 'error', 'message' => 'Fails ir pārāk liels. Maksimālais izmērs: 5MB'];
                    echo json_encode($response);
                    exit;
                }
                
                // Nolasīt attēla saturu
                $attels = file_get_contents($_FILES['attels']['tmp_name']);
                
                if ($attels === false) {
                    $response = ['status' => 'error', 'message' => 'Neizdevās nolasīt attēla failu.'];
                    echo json_encode($response);
                    exit;
                }
        
                // Žurnāla ieraksts attēla izmēra pārbaudei
                error_log("Attēla izmērs: " . strlen($attels) . " baiti");
                
                // Sagatavot SQL vaicājumu
                $sql = "INSERT INTO sparkly_malu_figura (nosaukums, attels, izveidots_liet, datums) VALUES (?, ?, ?, NOW())";
                $stmt = $savienojums->prepare($sql);
                
                if (!$stmt) {
                    $response = ['status' => 'error', 'message' => 'Datubāzes kļūda: ' . $savienojums->error];
                    echo json_encode($response);
                    exit;
                }
                
                // Pievienot datus datubāzē
                $null = NULL;
                $stmt->bind_param("sbi", $nosaukums, $null, $user_id);
                $stmt->send_long_data(1, $attels); 
                
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Malu figūra ir pievienota.'];
                } else {
                    error_log("SQL kļūda: " . $stmt->error);
                    $response = ['status' => 'error', 'message' => 'Neizdevās pievienot malu figūru: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                // Faila augšupielādes kļūdu ziņojumi
                $error_messages = [
                    UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels (servera ierobežojums)',
                    UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels (formas ierobežojums)', 
                    UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
                    UPLOAD_ERR_NO_FILE => 'Neviens fails netika augšupielādēts',
                    UPLOAD_ERR_NO_TMP_DIR => 'Trūkst pagaidu direktorijas',
                    UPLOAD_ERR_CANT_WRITE => 'Neizdevās ierakstīt failu diskā',
                    UPLOAD_ERR_EXTENSION => 'PHP paplašinājums apturēja faila augšupielādi'
                ];
                
                $error_code = isset($_FILES['attels']) ? $_FILES['attels']['error'] : UPLOAD_ERR_NO_FILE;
                $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Nezināma kļūda';
                
                error_log("Augšupielādes kļūda: " . $error_code . " - " . $error_message);
                $response = ['status' => 'error', 'message' => 'Faila augšupielādes kļūda: ' . $error_message];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Lietotājs nav atrasts.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Lietotājs nav pieslēdzies.'];
    }
    
    echo json_encode($response);
    exit;
}

// Pievienot formu
if (isset($_POST['add_forma'])) {
    $forma = $_POST['forma'];
    
    // Pārbaudīt vai lietotājs ir pieslēdzies
    if (isset($_SESSION['lietotajvardsSIN'])) {
        $lietotajvards = $_SESSION['lietotajvardsSIN'];
        
        // Iegūt lietotāja ID
        $sql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("s", $lietotajvards);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id_lietotajs'];
            $stmt->close();
            
            // Pievienot formu
            $sql = "INSERT INTO sparkly_formas (forma, izveidots_liet, datums) VALUES (?, ?, NOW())";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("si", $forma, $user_id);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Forma ir pievienota.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Neizdevās pievienot formu: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            $response = ['status' => 'error', 'message' => 'Lietotājs nav atrasts.'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Lietotājs nav pieslēdzies.'];
    }
    
    echo json_encode($response);
    exit;
}
?>