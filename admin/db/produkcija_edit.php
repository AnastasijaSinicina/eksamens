<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'con_db.php';


if (!isset($savienojums)) {
    die("Database connection variable \$savienojums not found");
}


if ($savienojums->connect_error) {
    die("Database connection failed: " . $savienojums->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id = (int)$_POST['id'];
    $forma = isset($_POST['forma']) ? (int)$_POST['forma'] : 0;
    $nosaukums = isset($_POST['nosaukums']) ? trim($_POST['nosaukums']) : '';
    $audums_id = isset($_POST['audums_id']) ? (int)$_POST['audums_id'] : 0;
    $figura_id = isset($_POST['figura_id']) ? (int)$_POST['figura_id'] : 0;
    $dekorejums1_id = isset($_POST['dekorejums1_id']) ? (int)$_POST['dekorejums1_id'] : 0;
    $cena = isset($_POST['cena']) ? (float)$_POST['cena'] : 0.00;
    
    $red_liet = $_SESSION['user_id'] ?? 1;
    $red_dat = date('Y-m-d H:i:s');
    

    if (empty($nosaukums)) {
        $error_message = "Produkta nosaukums ir obligāts lauks.";
    } elseif ($forma <= 0) {
        $error_message = "Lūdzu, izvēlieties derīgu formu.";
    } elseif ($cena <= 0) {
        $error_message = "Cena jābūt lielākai par 0.";
    } else {

        $check_sql = "SELECT * FROM produkcija_sprarkly WHERE id_bumba = ?";
        $check_stmt = $savienojums->prepare($check_sql);
        
        if (!$check_stmt) {
            $error_message = "Kļūda sagatavošanas vaicājumā: " . $savienojums->error;
        } else {
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                $error_message = "Produkts ar ID $id netika atrasts.";
            } else {
                $current_data = $check_result->fetch_assoc();
                
                $data_changed = (
                    $current_data['forma'] != $forma ||
                    $current_data['nosaukums'] != $nosaukums ||
                    $current_data['audums_id'] != $audums_id ||
                    $current_data['figura_id'] != $figura_id ||
                    $current_data['dekorejums1_id'] != $dekorejums1_id ||
                    abs($current_data['cena'] - $cena) > 0.001 
                );
                
                $sql = "UPDATE produkcija_sprarkly SET 
                        forma = ?, 
                        nosaukums = ?, 
                        audums_id = ?, 
                        figura_id = ?, 
                        dekorejums1_id = ?,
                        cena = ?, 
                        red_liet = ?, 
                        red_dat = ? 
                        WHERE id_bumba = ?";
                
                $stmt = $savienojums->prepare($sql);
                
                if (!$stmt) {
                    $error_message = "Kļūda sagatavošanas atjaunināšanas vaicājumā: " . $savienojums->error;
                } else {
                    $stmt->bind_param("isiiiidisi", 
                        $forma, 
                        $nosaukums, 
                        $audums_id, 
                        $figura_id, 
                        $dekorejums1_id, 
                        $cena, 
                        $red_liet, 
                        $red_dat, 
                        $id
                    );
                    
                    if ($stmt->execute()) {
                        if ($data_changed) {
                            $success_message = "Produkts '$nosaukums' ir veiksmīgi atjaunināts.";
                        } 
                        $redirect_url = "produkcija.php";
                    } else {
                        $error_message = "Neizdevās atjaunināt produktu: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
            $check_stmt->close();
        }
    }
}

$editData = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "SELECT * FROM produkcija_sprarkly WHERE id_bumba = ?";
    $stmt = $savienojums->prepare($sql);
    
    if (!$stmt) {
        $error_message = "Kļūda ielādējot produkta datus: " . $savienojums->error;
    } else {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $editData = $result->fetch_assoc();
        } else {
            $error_message = "Produkts netika atrasts.";
        }
        $stmt->close();
    }
}
?>