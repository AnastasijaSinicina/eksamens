<?php
=

require_once 'con_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $forma = $_POST['forma'];
    $nosaukums = $_POST['nosaukums'];
    $audums_id = $_POST['audums_id'];
    $figura_id = $_POST['figura_id'];
    $dekorejums1_id = $_POST['dekorejums1_id'];
    $dekorejums2_id = $_POST['dekorejums2_id'];
    $cena = $_POST['cena'];
    $izveidots_liet = $_SESSION['user_id'] ?? 1; =
    $timestamp = date('Y-m-d H:i:s');
    
    if (isset($_FILES['attels1']) && $_FILES['attels1']['error'] == 0 &&
        isset($_FILES['attels2']) && $_FILES['attels2']['error'] == 0 &&
        isset($_FILES['attels3']) && $_FILES['attels3']['error'] == 0) {
        
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $images_valid = true;
        
        for ($i = 1; $i <= 3; $i++) {
            $file_type = $_FILES["attels$i"]['type'];
            if (!in_array($file_type, $allowed_types)) {
                $images_valid = false;
                $error_message = "Nepareizs attēla formāts. Atļauti tikai JPG, JPEG, PNG formāti.";
                break;
            }
            
            if ($_FILES["attels$i"]['size'] > 5 * 1024 * 1024) {
                $images_valid = false;
                $error_message = "Attēla izmērs pārsniedz 5MB limitu.";
                break;
            }
        }
        
        if ($images_valid) {
            $attels1 = file_get_contents($_FILES['attels1']['tmp_name']);
            $attels2 = file_get_contents($_FILES['attels2']['tmp_name']);
            $attels3 = file_get_contents($_FILES['attels3']['tmp_name']);
            
            $sql = "INSERT INTO produkcija_sprarkly (forma, nosaukums, audums_id, figura_id, dekorejums1_id, dekorejums2_id, attels1, attels2, attels3, cena, izveidots_liet, timestamp) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $savienojums->prepare($sql);
            $stmt->bind_param("isiiiibbidis", $forma, $nosaukums, $audums_id, $figura_id, $dekorejums1_id, $dekorejums2_id, $attels1, $attels2, $attels3, $cena, $izveidots_liet, $timestamp);
            
            if ($stmt->execute()) {
                $success_message = "Produkts ir veiksmīgi pievienots.";
                $redirect_url = "produkcija.php";
            } else {
                $error_message = "Neizdevās pievienot produktu: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = "Lūdzu, augšupielādējiet visus trīs attēlus.";
    }
}
?>