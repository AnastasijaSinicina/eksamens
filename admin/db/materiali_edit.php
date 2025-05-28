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

if (isset($_POST['edit_audums'])) {
    $id = $_POST['id'];
    $nosaukums = $_POST['nosaukums'];
    
    $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
    $user_id = getUserIdFromUsername($savienojums, $lietotajvards);
    
    $sql = "UPDATE sparkly_audums SET nosaukums = ?, red_liet = ?, red_dat = NOW() WHERE id_audums = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("sii", $nosaukums, $user_id, $id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Audums ir atjaunināts.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās atjaunināt audumu: ' . $stmt->error];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}


if (isset($_POST['edit_dekorejums1'])) {
    try {
        if (empty($_POST['id']) || empty($_POST['nosaukums'])) {
            throw new Exception('ID un nosaukums ir obligāti lauki.');
        }
        
        $id = intval($_POST['id']);
        $nosaukums = trim($_POST['nosaukums']);
        $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
        
        if (!$lietotajvards) {
            throw new Exception('Sesija ir beigusies.');
        }
        
        $user_id = getUserIdFromUsername($savienojums, $lietotajvards);
        if (!$user_id) {
            throw new Exception('Lietotājs nav atrasts.');
        }
        
        $update_image = false;
        $attels = null;
        
        if (isset($_FILES['attels']) && $_FILES['attels']['error'] == UPLOAD_ERR_OK) {
            $update_image = true;
            
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['attels']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Nepareizs faila formāts. Atļauti: JPG, JPEG, PNG, GIF');
            }
            

            if ($_FILES['attels']['size'] > 5242880) {
                throw new Exception('Fails ir pārāk liels. Maksimālais izmērs: 5MB');
            }
            
            $attels = file_get_contents($_FILES['attels']['tmp_name']);
            
            if ($attels === false) {
                throw new Exception('Neizdevās nolasīt attēla failu.');
            }
        } else if (isset($_FILES['attels']) && $_FILES['attels']['error'] != UPLOAD_ERR_NO_FILE) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels (server limit)',
                UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels (form limit)', 
                UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
                UPLOAD_ERR_NO_TMP_DIR => 'Trūkst pagaidu direktorijas',
                UPLOAD_ERR_CANT_WRITE => 'Neizdevās ierakstīt failu diskā',
                UPLOAD_ERR_EXTENSION => 'PHP paplašinājums apturēja faila augšupielādi'
            ];
            
            $error_code = $_FILES['attels']['error'];
            $error_message = $error_messages[$error_code] ?? 'Nezināma kļūda';
            
            throw new Exception('Faila augšupielādes kļūda: ' . $error_message);
        }
        
        if ($update_image) {

            $sql = "UPDATE sparkly_dekorejums1 SET nosaukums = ?, attels = ?, red_liet = ?, red_dat = NOW() WHERE id_dekorejums1 = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
            }
            
            $null = NULL;
            $stmt->bind_param("sbii", $nosaukums, $null, $user_id, $id);
            $stmt->send_long_data(1, $attels);
            
        } else {
            $sql = "UPDATE sparkly_dekorejums1 SET nosaukums = ?, red_liet = ?, red_dat = NOW() WHERE id_dekorejums1 = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
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

if (isset($_POST['edit_dekorejums2'])) {
    try {
       if (empty($_POST['id']) || empty($_POST['nosaukums'])) {
            throw new Exception('ID un nosaukums ir obligāti lauki.');
        }
        
        $id = intval($_POST['id']);
        $nosaukums = trim($_POST['nosaukums']);
        $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
        
        if (!$lietotajvards) {
            throw new Exception('Sesija ir beigusies.');
        }
        
        $user_id = getUserIdFromUsername($savienojums, $lietotajvards);
        if (!$user_id) {
            throw new Exception('Lietotājs nav atrasts.');
        }
        
        $update_image = false;
        $attels = null;
        
        if (isset($_FILES['attels']) && $_FILES['attels']['error'] == UPLOAD_ERR_OK) {
            $update_image = true;
            
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['attels']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Nepareizs faila formāts. Atļauti: JPG, JPEG, PNG, GIF');
            }
            
            if ($_FILES['attels']['size'] > 5242880) {
                throw new Exception('Fails ir pārāk liels. Maksimālais izmērs: 5MB');
            }
            
            $attels = file_get_contents($_FILES['attels']['tmp_name']);
            
            if ($attels === false) {
                throw new Exception('Neizdevās nolasīt attēla failu.');
            }
        } else if (isset($_FILES['attels']) && $_FILES['attels']['error'] != UPLOAD_ERR_NO_FILE) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels (server limit)',
                UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels (form limit)', 
                UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
                UPLOAD_ERR_NO_TMP_DIR => 'Trūkst pagaidu direktorijas',
                UPLOAD_ERR_CANT_WRITE => 'Neizdevās ierakstīt failu diskā',
                UPLOAD_ERR_EXTENSION => 'PHP paplašinājums apturēja faila augšupielādi'
            ];
            
            $error_code = $_FILES['attels']['error'];
            $error_message = $error_messages[$error_code] ?? 'Nezināma kļūda';
            
            throw new Exception('Faila augšupielādes kļūda: ' . $error_message);
        }

        if ($update_image) {

            $sql = "UPDATE sparkly_dekorejums2 SET nosaukums = ?, attels = ?, red_liet = ?, red_dat = NOW() WHERE id_dekorejums2 = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
            }
            
            $null = NULL;
            $stmt->bind_param("sbii", $nosaukums, $null, $user_id, $id);
            $stmt->send_long_data(1, $attels); 
            
        } else {
            $sql = "UPDATE sparkly_dekorejums2 SET nosaukums = ?, red_liet = ?, red_dat = NOW() WHERE id_dekorejums2 = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
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

if (isset($_POST['edit_figura'])) {
    try {
        if (empty($_POST['id']) || empty($_POST['nosaukums'])) {
            throw new Exception('ID un nosaukums ir obligāti lauki.');
        }
        
        $id = intval($_POST['id']);
        $nosaukums = trim($_POST['nosaukums']);
        $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
        
        if (!$lietotajvards) {
            throw new Exception('Sesija ir beigusies.');
        }
        
        $user_id = getUserIdFromUsername($savienojums, $lietotajvards);
        if (!$user_id) {
            throw new Exception('Lietotājs nav atrasts.');
        }
        
       error_log("Edit figura - File upload check: " . print_r($_FILES, true));
        
        $update_image = false;
        $attels = null;
        
        if (isset($_FILES['attels']) && $_FILES['attels']['error'] == UPLOAD_ERR_OK) {
            $update_image = true;
            
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['attels']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Nepareizs faila formāts. Atļauti: JPG, JPEG, PNG, GIF');
            }
            
            if ($_FILES['attels']['size'] > 5242880) {
                throw new Exception('Fails ir pārāk liels. Maksimālais izmērs: 5MB');
            }
            
            $attels = file_get_contents($_FILES['attels']['tmp_name']);
            
            if ($attels === false) {
                throw new Exception('Neizdevās nolasīt attēla failu.');
            }
            
            error_log("Edit figura - Image size: " . strlen($attels) . " bytes");
        } else if (isset($_FILES['attels']) && $_FILES['attels']['error'] != UPLOAD_ERR_NO_FILE) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels (server limit)',
                UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels (form limit)', 
                UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
                UPLOAD_ERR_NO_TMP_DIR => 'Trūkst pagaidu direktorijas',
                UPLOAD_ERR_CANT_WRITE => 'Neizdevās ierakstīt failu diskā',
                UPLOAD_ERR_EXTENSION => 'PHP paplašinājums apturēja faila augšupielādi'
            ];
            
            $error_code = $_FILES['attels']['error'];
            $error_message = $error_messages[$error_code] ?? 'Nezināma kļūda';
            
            error_log("Edit figura - Upload error: " . $error_code . " - " . $error_message);
            throw new Exception('Faila augšupielādes kļūda: ' . $error_message);
        }
        
        if ($update_image) {

            $sql = "UPDATE sparkly_malu_figura SET nosaukums = ?, attels = ?, red_liet = ?, red_dat = NOW() WHERE id_malu_figura = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
            }
            

            $null = NULL;
            $stmt->bind_param("sbii", $nosaukums, $null, $user_id, $id);
            $stmt->send_long_data(1, $attels);
            
        } else {
            
            $sql = "UPDATE sparkly_malu_figura SET nosaukums = ?, red_liet = ?, red_dat = NOW() WHERE id_malu_figura = ?";
            $stmt = $savienojums->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $savienojums->error);
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
            error_log("Edit figura - SQL Error: " . $stmt->error);
            throw new Exception('Neizdevās atjaunināt malu figūru: ' . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}


if (isset($_POST['edit_forma'])) {
    $id = $_POST['id'];
    $forma = $_POST['forma'];
    $lietotajvards = $_SESSION['lietotajvardsSIN'] ?? null;
    $user_id = getUserIdFromUsername($savienojums, $lietotajvards);
    
    $sql = "UPDATE sparkly_formas SET forma = ?, red_liet = ?, red_dat = NOW() WHERE id_forma = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("sii", $forma, $user_id, $id);
    
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Forma ir atjaunināta.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Neizdevās atjaunināt formu: ' . $stmt->error];
    }
    $stmt->close();
    
    echo json_encode($response);
    exit;
}
?>