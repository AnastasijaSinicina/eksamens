<?php
// Sāk sesiju
session_start();

// Pārbauda, vai lietotājs ir ielogojies sistēmā
if (!isset($_SESSION['lietotajvardsSIN'])) {
    // Ja nav ielogojies, uzstāda brīdinājuma ziņojumu un novirza uz login lapu
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai pievienotu preces grozam";
    header("Location: ../../login.php");
    exit();
}

// Iekļauj datubāzes savienojuma failu
require "con_db.php";

// Iegūst pašreizējā lietotāja vārdu no sesijas
$username = $_SESSION['lietotajvardsSIN'];

// Pārbauda, vai ir saņemti visi nepieciešamie POST dati
if (isset($_POST['add_to_cart']) && isset($_POST['id']) && isset($_POST['nosaukums']) && isset($_POST['cena'])) {
    // Iegūst produkta ID no formas
    $id = $_POST['id'];
    
    // Pārbauda, vai šis produkts jau eksistē lietotāja grozā ar statusu 'aktīvs'
    $checkQuery = "SELECT * FROM grozs_sparkly WHERE lietotajvards = ? AND bumba_id = ? AND statuss = 'aktīvs'";
    $stmt = $savienojums->prepare($checkQuery);
    $stmt->bind_param("si", $username, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Ja produkts jau ir grozā, iegūst pašreizējo ierakstu
        $row = $result->fetch_assoc();
        // Palielina daudzumu par 1
        $newQuantity = $row['daudzums'] + 1;
        
        // Atjaunina produkta daudzumu datubāzē
        $updateQuery = "UPDATE grozs_sparkly SET daudzums = ? WHERE id_grozs = ?";
        $stmt = $savienojums->prepare($updateQuery);
        $stmt->bind_param("ii", $newQuantity, $row['id_grozs']);
        $stmt->execute();
    } else {
        // Ja produkts nav grozā, pievieno jaunu ierakstu ar daudzumu 1
        $insertQuery = "INSERT INTO grozs_sparkly (lietotajvards, bumba_id, daudzums, statuss) VALUES (?, ?, 1, 'aktīvs')";
        $stmt = $savienojums->prepare($insertQuery);
        $stmt->bind_param("si", $username, $id);
        $stmt->execute();
    }
    
    // Uzstāda veiksmīgu paziņojumu un novirza lietotāju atpakaļ uz produkta lapu
    $_SESSION['pazinojums'] = "Prece pievienota grozam!";
    header("Location: ../../produkts.php?id=" . $id);
    exit();
} else {
    // Ja trūkst nepieciešamo formas datu, uzstāda kļūdas ziņojumu
    $_SESSION['pazinojums'] = "Kļūda: Trūkst datu par preci";
    header("Location: ../../produkcija.php");
    exit();
}

// Aizver datubāzes savienojumus
$stmt->close();
$savienojums->close();
?>