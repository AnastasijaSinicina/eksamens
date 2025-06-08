<?php
require_once 'con_db.php';
session_start();

// Rediģēt audumu
if (isset($_POST['edit_audums']) && isset($_POST['id'])) {
    $id = $_POST['id'];
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
            
            // Atjaunināt audumu
            $sql = "UPDATE sparkly_audums SET nosaukums = ?, red_liet = ?, red_dat = NOW() WHERE id_audums = ?";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("sii", $nosaukums, $user_id, $id);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Audums ir atjaunināts.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Neizdevās atjaunināt audumu: ' . $stmt->error];
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

// Rediģēt dekorējumu 1
if (isset($_POST['edit_dekorejums1']) && isset($_POST['id'])) {
    try {
        // Pārbaudīt obligātos laukus
        if (empty($_POST['id']) || empty($_POST['nosaukums'])) {
            throw new Exception('ID un nosaukums ir obligāti lauki.');
        }
        
        $id = intval($_POST['id']);
        $nosaukums = trim($_POST['nosaukums']);
        
        // Pārbaudīt vai lietotājs ir pieslēdzies
        if (!isset($_SESSION['lietotajvardsSIN'])) {
            throw new Exception('Sesija ir beigusies.');
        }
        
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
        } else {
            throw new Exception('Lietotājs nav atrasts.');
        }
        
        $update_image = false;
        $attels = null;
        
        // Pārbaudīt vai tika augšupielādēts jauns attēls
        if (isset($_FILES['attels']) && $_FILES['attels']['error'] == UPLOAD_ERR_OK) {
            $update_image = true;
            
            // Validēt attēla failu
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['attels']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Nepareizs faila formāts. Atļauti: JPG, JPEG, PNG, GIF');
            }
            
            // Pārbaudīt faila izmēru (5MB maksimums)
            if ($_FILES['attels']['size'] > 5242880) {
                throw new Exception('Fails ir pārāk liels. Maksimālais izmērs: 5MB');
            }
            
            // Nolasīt attēla saturu
            $attels = file_get_contents($_FILES['attels']['tmp_name']);
            
            if ($attels === false) {
                throw new Exception('Neizdevās nolasīt attēla failu.');
            }
        } else if (isset($_FILES['attels']) && $_FILES['attels']['error'] != UPLOAD_ERR_NO_FILE) {
            // Faila augšupielādes kļūdu ziņojumi
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels (servera ierobežojums)',
                UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels (formas ierobežojums)', 
                UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
                UPLOAD_ERR_NO_TMP_DIR => 'Trūkst pagaidu direktorijas',
                UPLOAD_ERR_CANT_WRITE => 'Neizdevās ierakstīt failu diskā',
                UPLOAD_ERR_EXTENSION => 'PHP paplašinājums apturēja faila augšupielādi'
            ];
            
            $error_code = $_FILES['attels']['error'];
            $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Nezināma kļūda';
            
            throw new Exception('Faila augšupielādes kļūda: ' . $error_message);
        }
        
        // Atjaunināt datubāzē
        if ($update_image) {
            // Atjaunināt ar jaunu attēlu
            $sql = "UPDATE sparkly_dekorejums1 SET nosaukums = ?, attels = ?, red_liet = ?, red_dat = NOW() WHERE id_dekorejums1 = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Datubāzes kļūda: ' . $savienojums->error);
            }
            
            $null = NULL;
            $stmt->bind_param("sbii", $nosaukums, $null, $user_id, $id);
            $stmt->send_long_data(1, $attels);
            
        } else {
            // Atjaunināt tikai nosaukumu
            $sql = "UPDATE sparkly_dekorejums1 SET nosaukums = ?, red_liet = ?, red_dat = NOW() WHERE id_dekorejums1 = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Datubāzes kļūda: ' . $savienojums->error);
            }
            
            $stmt->bind_param("sii", $nosaukums, $user_id, $id);
        }
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            if ($affected_rows > 0) {
                $response = ['status' => 'success', 'message' => 'Dekorējums ir atjaunināts veiksmīgi.'];
            } else {
                $response = ['status' => 'warning', 'message' => 'Nav veiktas izmaiņas vai ieraksts nav atrasts.'];
            }
        } else {
            throw new Exception('Neizdevās atjaunināt dekorējumu: ' . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}

// Rediģēt malu figūru
if (isset($_POST['edit_figura']) && isset($_POST['id'])) {
    try {
        // Pārbaudīt obligātos laukus
        if (empty($_POST['id']) || empty($_POST['nosaukums'])) {
            throw new Exception('ID un nosaukums ir obligāti lauki.');
        }
        
        $id = intval($_POST['id']);
        $nosaukums = trim($_POST['nosaukums']);
        
        // Pārbaudīt vai lietotājs ir pieslēdzies
        if (!isset($_SESSION['lietotajvardsSIN'])) {
            throw new Exception('Sesija ir beigusies.');
        }
        
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
        } else {
            throw new Exception('Lietotājs nav atrasts.');
        }
        
        // Žurnāla ieraksts faila pārbaudes nolūkos
        error_log("Figūras rediģēšana - Faila augšupielādes pārbaude: " . print_r($_FILES, true));
        
        $update_image = false;
        $attels = null;
        
        // Pārbaudīt vai tika augšupielādēts jauns attēls
        if (isset($_FILES['attels']) && $_FILES['attels']['error'] == UPLOAD_ERR_OK) {
            $update_image = true;
            
            // Validēt attēla failu
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['attels']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Nepareizs faila formāts. Atļauti: JPG, JPEG, PNG, GIF');
            }
            
            // Pārbaudīt faila izmēru (5MB maksimums)
            if ($_FILES['attels']['size'] > 5242880) {
                throw new Exception('Fails ir pārāk liels. Maksimālais izmērs: 5MB');
            }
            
            // Nolasīt attēla saturu
            $attels = file_get_contents($_FILES['attels']['tmp_name']);
            
            if ($attels === false) {
                throw new Exception('Neizdevās nolasīt attēla failu.');
            }
            
            // Žurnāla ieraksts attēla izmēra pārbaudei
            error_log("Figūras rediģēšana - Attēla izmērs: " . strlen($attels) . " baiti");
        } else if (isset($_FILES['attels']) && $_FILES['attels']['error'] != UPLOAD_ERR_NO_FILE) {
            // Faila augšupielādes kļūdu ziņojumi
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels (servera ierobežojums)',
                UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels (formas ierobežojums)', 
                UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
                UPLOAD_ERR_NO_TMP_DIR => 'Trūkst pagaidu direktorijas',
                UPLOAD_ERR_CANT_WRITE => 'Neizdevās ierakstīt failu diskā',
                UPLOAD_ERR_EXTENSION => 'PHP paplašinājums apturēja faila augšupielādi'
            ];
            
            $error_code = $_FILES['attels']['error'];
            $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Nezināma kļūda';
            
            error_log("Figūras rediģēšana - Augšupielādes kļūda: " . $error_code . " - " . $error_message);
            throw new Exception('Faila augšupielādes kļūda: ' . $error_message);
        }
        
        // Atjaunināt datubāzē
        if ($update_image) {
            // Atjaunināt ar jaunu attēlu
            $sql = "UPDATE sparkly_malu_figura SET nosaukums = ?, attels = ?, red_liet = ?, red_dat = NOW() WHERE id_malu_figura = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Datubāzes kļūda: ' . $savienojums->error);
            }
            
            $null = NULL;
            $stmt->bind_param("sbii", $nosaukums, $null, $user_id, $id);
            $stmt->send_long_data(1, $attels);
            
        } else {
            // Atjaunināt tikai nosaukumu
            $sql = "UPDATE sparkly_malu_figura SET nosaukums = ?, red_liet = ?, red_dat = NOW() WHERE id_malu_figura = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Datubāzes kļūda: ' . $savienojums->error);
            }
            
            $stmt->bind_param("sii", $nosaukums, $user_id, $id);
        }
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            if ($affected_rows > 0) {
                $response = ['status' => 'success', 'message' => 'Malu figūra ir atjaunināta veiksmīgi.'];
            } else {
                $response = ['status' => 'warning', 'message' => 'Nav veiktas izmaiņas vai ieraksts nav atrasts.'];
            }
        } else {
            error_log("Figūras rediģēšana - SQL kļūda: " . $stmt->error);
            throw new Exception('Neizdevās atjaunināt malu figūru: ' . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}

// Rediģēt formu
if (isset($_POST['edit_forma']) && isset($_POST['id'])) {
    $id = $_POST['id'];
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
            
            // Atjaunināt formu
            $sql = "UPDATE sparkly_formas SET forma = ?, red_liet = ?, red_dat = NOW() WHERE id_forma = ?";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("sii", $forma, $user_id, $id);
            
            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Forma ir atjaunināta.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Neizdevās atjaunināt formu: ' . $stmt->error];
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