<?php
session_start();

// Pārbauda, vai lietotājs ir pieslēdzies
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu, ielogojieties, lai mainītu paroli!";
    header("Location: ../../login.php");
    exit();
}

// Iekļauj datubāzes savienojumu
require_once "con_db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $lietotajvards = $_SESSION['lietotajvardsSIN'];
    
    // Pārbauda pašreizējo paroli un iegūst lietotāja lomu
    $vaicajums = $savienojums->prepare("SELECT parole, loma FROM lietotaji_sparkly WHERE lietotajvards = ?");
    $vaicajums->bind_param("s", $lietotajvards);
    $vaicajums->execute();
    $rezultats = $vaicajums->get_result();
    $lietotajs = $rezultats->fetch_assoc();
    
    if (!$lietotajs) {
        $_SESSION['pazinojums'] = "Lietotājs nav atrasts!";
        // Novirza atkarībā no sesijas lomas, ja lietotājs nav atrasts
        $redirect_url = ($_SESSION['loma'] === 'admin' || $_SESSION['loma'] === 'moder') ? "../profils.php" : "../../profils.php";
        header("Location: " . $redirect_url);
        exit();
    }
    
    // Verificē pašreizējo paroli
    if (!password_verify($current_password, $lietotajs['parole'])) {
        $_SESSION['pazinojums'] = "KĻŪDA! Pašreizējā parole ir nepareiza!";
        // Novirza atkarībā no lietotāja lomas
        $redirect_url = ($lietotajs['loma'] === 'admin' || $lietotajs['loma'] === 'moder') ? "../profils.php" : "../../profils.php";
        header("Location: " . $redirect_url);
        exit();
    }

    // Validē ievades datus
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['pazinojums'] = "Visi lauki ir obligāti!";
        // Novirza atkarībā no lietotāja lomas
        $redirect_url = ($lietotajs['loma'] === 'admin' || $lietotajs['loma'] === 'moder') ? "../profils.php" : "../../profils.php";
        header("Location: " . $redirect_url);
        exit();
    }
    
    // Pārbauda, vai jaunās paroles sakrīt
    if ($new_password !== $confirm_password) {
        $_SESSION['pazinojums'] = "KĻŪDA! Jaunās paroles nesakrīt!";
        // Novirza atkarībā no lietotāja lomas
        $redirect_url = ($lietotajs['loma'] === 'admin' || $lietotajs['loma'] === 'moder') ? "../profils.php" : "../../profils.php";
        header("Location: " . $redirect_url);
        exit();
    }
    
    // Pārbauda paroles garumu
    if (strlen($new_password) < 8) {
        $_SESSION['pazinojums'] = "Parolei jābūt vismaz 8 simbolus garai!";
        // Novirza atkarībā no lietotāja lomas
        $redirect_url = ($lietotajs['loma'] === 'admin' || $lietotajs['loma'] === 'moder') ? "../profils.php" : "../../profils.php";
        header("Location: " . $redirect_url);
        exit();
    }
    
    // Šifrē jauno paroli
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Atjaunina paroli datubāzē
    $update_query = $savienojums->prepare("UPDATE lietotaji_sparkly SET parole = ? WHERE lietotajvards = ?");
    $update_query->bind_param("ss", $hashed_password, $lietotajvards);
    
    if ($update_query->execute()) {
        $_SESSION['pazinojums'] = "Parole veiksmīgi nomainīta!";
    } else {
        $_SESSION['pazinojums'] = "Radās kļūda, mainot paroli. Lūdzu, mēģiniet vēlreiz!";
    }
    
    $update_query->close();
    $vaicajums->close();
    $savienojums->close();
    
    // Nosaka pareizo novirzīšanas URL atkarībā no lietotāja lomas
    $redirect_url = ($lietotajs['loma'] === 'admin' || $lietotajs['loma'] === 'moder') ? "../profils.php" : "../../profils.php";
    header("Location: " . $redirect_url);
    exit();
} else {
    // Ja tiek piekļūts tieši bez POST pieprasījuma
    $_SESSION['pazinojums'] = "Nederīgs pieprasījums!";
    // Novirza atkarībā no sesijas lomas
    $redirect_url = ($_SESSION['loma'] === 'admin' || $_SESSION['loma'] === 'moder') ? "../profils.php" : "../../profils.php";
    header("Location: " . $redirect_url);
    exit();
}
?>