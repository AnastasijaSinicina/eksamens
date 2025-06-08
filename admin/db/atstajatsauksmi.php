<?php
require_once 'con_db.php';

// Inicializē mainīgos
$user_id = null;
$order_count = 0;
$custom_order_count = 0;
$error_message = '';
$success_message = '';

// Iegūst lietotāja datus, ieskaitot pabeigto pasūtījumu skaitu
if (isset($_SESSION['lietotajvardsSIN'])) {
    $username = $_SESSION['lietotajvardsSIN'];
    $user_query = "SELECT id_lietotajs, pas_skaits, spec_pas_skaits FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $user_stmt = $savienojums->prepare($user_query);

    if (!$user_stmt) {
        die("User query prepare failed: " . $savienojums->error);
    }

    $user_stmt->bind_param("s", $username);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    
    $user_stmt->close();
    
    if (!$user) {
        // Ja lietotājs nav atrasts, pārvirza uz pieteikšanās lapu
        session_destroy();
        header("Location: login.php");
        exit();
    }

    $user_id = $user['id_lietotajs'];
    $order_count = $user['pas_skaits'] ?? 0;
    $custom_order_count = $user['spec_pas_skaits'] ?? 0; 
}

// Apstrādā atsauksmes iesniegšanu
if (isset($_POST['submit_feedback']) && isset($user_id)) {
    $rating = intval($_POST['rating']);
    $feedback_text = htmlspecialchars($_POST['feedback']);
    $user_name = htmlspecialchars($_POST['user_name']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($feedback_text) && !empty($user_name)) {
        // Ievieto atsauksmi datubāzē
        $insert_feedback_sql = "INSERT INTO sparkly_atsauksmes (lietotajs_id, vards_uzvards, zvaigznes, atsauksme, datums, apstiprinats) 
                               VALUES (?, ?, ?, ?, NOW(), 0)";
        
        $feedback_stmt = $savienojums->prepare($insert_feedback_sql);
        
        if ($feedback_stmt) {
            $feedback_stmt->bind_param("isis", $user_id, $user_name, $rating, $feedback_text);
            
            if ($feedback_stmt->execute()) {
                $success_message = "Jūsu atsauksme ir veiksmīgi nosūtīta! Tā tiks pārskatīta un apstiprināta īsā laikā.";
                $_SESSION['pazinojums'] = $success_message;
                header("Location: atsauksmes.php");
                exit();
            } else {
                $error_message = "Kļūda saglabājot atsauksmi: " . $feedback_stmt->error;
            }
            $feedback_stmt->close();
        } else {
            $error_message = "Kļūda sagatavojot vaicājumu: " . $savienojums->error;
        }
    } else {
        $error_message = "Lūdzu aizpildiet visus laukus un izvēlieties vērtējumu.";
    }
}
?>