<?php
// Start or resume session
session_start();

// Check if user is logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    // Set message and redirect to login
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai pievienotu preces grozam";
    header("Location: ../../login.php");
    exit();
}

// Include database connection
require "con_db.php";

// Get current user's username
$username = $_SESSION['lietotajvardsSIN'];

// Check if form data exists
if (isset($_POST['add_to_cart']) && isset($_POST['id']) && isset($_POST['nosaukums']) && isset($_POST['cena'])) {
    // Get product data
    $id = $_POST['id'];
    
    // Check if product already exists in user's cart
    $checkQuery = "SELECT * FROM grozs_sparkly WHERE lietotajvards = ? AND bumba_id = ? AND statuss = 'aktīvs'";
    $stmt = $savienojums->prepare($checkQuery);
    $stmt->bind_param("si", $username, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Product already in cart, increase quantity
        $row = $result->fetch_assoc();
        $newQuantity = $row['daudzums'] + 1;
        
        $updateQuery = "UPDATE grozs_sparkly SET daudzums = ? WHERE id_grozs = ?";
        $stmt = $savienojums->prepare($updateQuery);
        $stmt->bind_param("ii", $newQuantity, $row['id_grozs']);
        $stmt->execute();
    } else {
        // Product not in cart, add it
        $insertQuery = "INSERT INTO grozs_sparkly (lietotajvards, bumba_id, daudzums, statuss) VALUES (?, ?, 1, 'aktīvs')";
        $stmt = $savienojums->prepare($insertQuery);
        $stmt->bind_param("si", $username, $id);
        $stmt->execute();
    }
    
    $_SESSION['pazinojums'] = "Prece pievienota grozam!";
    header("Location: ../../produkts.php?id=" . $id);
    exit();
} else {
    // Error: Missing form data
    $_SESSION['pazinojums'] = "Kļūda: Trūkst datu par preci";
    header("Location: ../../produkcija.php");
    exit();
}

// Close the statement and connection
$stmt->close();
$savienojums->close();
?>