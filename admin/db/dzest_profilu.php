<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu, ielogojieties!";
    header("Location: login.php");
    exit();
}

// Include database connection
require_once "con_db.php";

// Get current user
$lietotajvards = $_SESSION['lietotajvardsSIN'];

// Fetch user ID
$vaicajums = $savienojums->prepare("SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?");
$vaicajums->bind_param("s", $lietotajvards);
$vaicajums->execute();
$rezultats = $vaicajums->get_result();
$lietotajs = $rezultats->fetch_assoc();

if (!$lietotajs) {
    $_SESSION['pazinojums'] = "Lietotājs nav atrasts!";
    header("Location: profils.php");
    exit();
}

// Soft delete user by setting dzests column to 1
$dzest_vaicajums = $savienojums->prepare("UPDATE lietotaji_sparkly SET dzests = 1 WHERE id_lietotajs = ?");
$dzest_vaicajums->bind_param("i", $lietotajs['id_lietotajs']);

if ($dzest_vaicajums->execute()) {
    // Clear all session variables and destroy session
    session_unset();
    session_destroy();
    
    // Start new session for the notification
    session_start();
    $_SESSION['pazinojums'] = "Jūsu konts ir veiksmīgi izdzēsts!";
    header("Location: ../../login.php");
    exit();
} else {
    $_SESSION['pazinojums'] = "Kļūda konta dzēšanā. Lūdzu mēģiniet vēlreiz.";
    header("Location: profils.php");
    exit();
}
?>