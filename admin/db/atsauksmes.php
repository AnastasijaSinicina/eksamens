<?php

require 'con_db.php';
$_SESSION['redirect_after_login'] = "atstajatsauksmi.php";

// Parādīt sesijas paziņojumu, ja tāds pastāv
if (isset($_SESSION['pazinojums'])) {
    echo '<div class="success-message">' . htmlspecialchars($_SESSION['pazinojums']) . '</div>';
    unset($_SESSION['pazinojums']);
}

// Pārbaudīt, vai lietotājs ir ielogojies, lai noteiktu atsauksmju pieejamību
$user_can_leave_feedback = false;
$user_id = null;

if (isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['redirect_after_login'] = "atsauksmes.php";
    $username = $_SESSION['lietotajvardsSIN'];
    $user_query = "SELECT id_lietotajs, pas_skaits FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $user_stmt = $savienojums->prepare($user_query);
    
    if ($user_stmt) {
        $user_stmt->bind_param("s", $username);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        
        if ($user && $user['pas_skaits'] > 0) {
            $user_can_leave_feedback = true;
            $user_id = $user['id_lietotajs'];
        }
        $user_stmt->close();
    }
}

// Iegūt apstiprinātas atsauksmes ar uzlabotu vaicājumu, ieskaitot lietotāju fotogrāfijas
$feedback_sql = "SELECT a.id_atsauksme, a.vards_uzvards, a.zvaigznes, a.atsauksme, a.datums,
                        l.lietotajvards, l.foto, a.apstiprinats
                 FROM sparkly_atsauksmes a
                 LEFT JOIN lietotaji_sparkly l ON a.lietotajs_id = l.id_lietotajs
                 WHERE a.apstiprinats = 1
                 ORDER BY a.datums DESC";

$result = $savienojums->query($feedback_sql);
$approved_feedback = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $approved_feedback[] = $row;
    }
}

// Aprēķināt vidējo vērtējumu
$average_rating = 0;
$total_ratings = 0;
if (!empty($approved_feedback)) {
    $sum_ratings = array_sum(array_column($approved_feedback, 'zvaigznes'));
    $total_ratings = count($approved_feedback);
    $average_rating = $sum_ratings / $total_ratings;
}

$savienojums->close();


?>