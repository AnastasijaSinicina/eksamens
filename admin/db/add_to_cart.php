<?php
session_start();

// Pārbauda, vai lietotājs ir ielogojies
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai pievienotu preces grozam";
    header("Location: ../../login.php");
    exit();
}

require "con_db.php";

// Iegūst pašreizējā lietotāja vārdu
$username = $_SESSION['lietotajvardsSIN'];

// Pārbauda, vai pastāv formas dati
if (isset($_POST['add_to_cart']) && isset($_POST['id']) && isset($_POST['nosaukums']) && isset($_POST['cena'])) {
    // Iegūst produkta datus
    $id = $_POST['id'];
    
    // Pārbauda, vai produkts jau eksistē lietotāja grozā
    $checkQuery = "SELECT * FROM grozs_sparkly WHERE lietotajvards = ? AND bumba_id = ? AND statuss = 'aktīvs'";
    $stmt = $savienojums->prepare($checkQuery);
    $stmt->bind_param("si", $username, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Produkts jau ir grozā, palielina daudzumu
        $row = $result->fetch_assoc();
        $newQuantity = $row['daudzums'] + 1;
        
        // Atjaunina daudzumu datubāzē
        $updateQuery = "UPDATE grozs_sparkly SET daudzums = ? WHERE id_grozs = ?";
        $stmt = $savienojums->prepare($updateQuery);
        $stmt->bind_param("ii", $newQuantity, $row['id_grozs']);
        $stmt->execute();
    } else {
        // Produkts nav grozā, pievieno to
        $insertQuery = "INSERT INTO grozs_sparkly (lietotajvards, bumba_id, daudzums, statuss) VALUES (?, ?, 1, 'aktīvs')";
        $stmt = $savienojums->prepare($insertQuery);
        $stmt->bind_param("si", $username, $id);
        $stmt->execute();
    }
    
    // Uzstāda paziņojumu un novirza atpakaļ uz produkta lapu
    $_SESSION['pazinojums'] = "Prece pievienota grozam!";
    header("Location: ../../produkts.php?id=" . $id);
    exit();
} else {
    // Kļūda: Trūkst formas datu
    $_SESSION['pazinojums'] = "Kļūda: Trūkst datu par preci";
    header("Location: ../../produkcija.php");
    exit();
}

$stmt->close();
$savienojums->close();
?>