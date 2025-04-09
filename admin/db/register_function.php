<?php
require "con_db.php";
session_start();

if (isset($_POST['registreties'])) {
    // Get and sanitize input data
    $epasts = htmlspecialchars($_POST['epasts']);
    $vards = htmlspecialchars($_POST['vards']);
    $uzvards = htmlspecialchars($_POST['uzvards']);
    $lietotajvards = htmlspecialchars($_POST['lietotajvards']);
    $parole = $_POST['parole'];
    $parole_atkartoti = $_POST['parole_atkartoti'];
    
    // Validate if passwords match
    if ($parole !== $parole_atkartoti) {
        $_SESSION['pazinojums'] = "Paroles nesakrīt!";
        header("Location: ../../register.php");
        exit();
    }
    
    // Check if email already exists
    $check_email = $savienojums->prepare("SELECT * FROM lietotaji_sparkly WHERE epasts = ?");
    $check_email->bind_param("s", $epasts);
    $check_email->execute();
    $email_result = $check_email->get_result();
    
    if ($email_result->num_rows > 0) {
        $_SESSION['pazinojums'] = "Šis e-pasts jau ir reģistrēts!";
        header("Location: ../../register.php");
        exit();
    }
    
    // Check if username already exists
    $check_username = $savienojums->prepare("SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?");
    $check_username->bind_param("s", $lietotajvards);
    $check_username->execute();
    $username_result = $check_username->get_result();
    
    if ($username_result->num_rows > 0) {
        $_SESSION['pazinojums'] = "Šis lietotājvārds jau ir aizņemts!";
        header("Location: ../../register.php");
        exit();
    }
    
    // Hash the password
    $hashed_password = password_hash($parole, PASSWORD_DEFAULT);
    
    // Insert new user
    $insert_user = $savienojums->prepare("INSERT INTO lietotaji_sparkly (epasts, vards, uzvards, lietotajvards, parole, loma) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Default role is "klients"
    $loma = "klients";
    
    $insert_user->bind_param("ssssss", $epasts, $vards, $uzvards, $lietotajvards, $hashed_password, $loma);
    
    if ($insert_user->execute()) {
        $_SESSION['pazinojums'] = "Reģistrācija veiksmīga! Varat ielogoties.";
        header("Location: ../../login.php");
        exit();
    } else {
        $_SESSION['pazinojums'] = "Reģistrācijas kļūda: " . $savienojums->error;
        header("Location: ../../register.php");
        exit();
    }
    
    // Close statements
    $check_email->close();
    $check_username->close();
    $insert_user->close();
    $savienojums->close();
}
?>