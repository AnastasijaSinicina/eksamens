<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    header("Location: ../../login.php");
    exit();
}

// Include database connection
require "con_db.php";

// Get current user's username
$username = $_SESSION['lietotajvardsSIN'];

// Handle increase quantity
if (isset($_POST['increase']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Get current quantity
    $query = "SELECT daudzums FROM grozs_sparkly WHERE id_grozs = ? AND lietotajvards = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $newQuantity = $row['daudzums'] + 1;
        
        // Update quantity
        $updateQuery = "UPDATE grozs_sparkly SET daudzums = ? AND statuss = 'aktīvs' WHERE id_grozs = ?";
        $stmt = $savienojums->prepare($updateQuery);
        $stmt->bind_param("ii", $newQuantity, $id);
        $stmt->execute();
        
        $_SESSION['pazinojums'] = "Daudzums palielināts";
    }
}

// Handle decrease quantity
if (isset($_POST['decrease']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Get current quantity
    $query = "SELECT daudzums FROM grozs_sparkly WHERE id_grozs = ? AND lietotajvards = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($row['daudzums'] > 1) {
            // Decrease quantity
            $newQuantity = $row['daudzums'] - 1;
            $updateQuery = "UPDATE grozs_sparkly SET daudzums = ? WHERE id_grozs = ?";
            $stmt = $savienojums->prepare($updateQuery);
            $stmt->bind_param("ii", $newQuantity, $id);
            $stmt->execute();
            
            $_SESSION['pazinojums'] = "Daudzums samazināts";
        } else {
            // Remove item if quantity would be 0
            $deleteQuery = "DELETE FROM grozs_sparkly WHERE id_grozs = ?";
            $stmt = $savienojums->prepare($deleteQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $_SESSION['pazinojums'] = "Prece izņemta no groza";
        }
    }
}

// Handle remove item
if (isset($_POST['remove']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Delete item from cart
    $deleteQuery = "DELETE FROM grozs_sparkly WHERE id_grozs = ? AND lietotajvards = ?";
    $stmt = $savienojums->prepare($deleteQuery);
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    
    $_SESSION['pazinojums'] = "Prece izņemta no groza";
}

// Handle clear cart
if (isset($_POST['clear']) && isset($_POST['user'])) {
    $user = $_POST['user'];
    
    // Check if the current user is the same as in the form
    if ($user === $username) {
        // Delete all items from user's cart
        $clearQuery = "DELETE FROM grozs_sparkly WHERE lietotajvards = ? AND statuss = 'active'";
        $stmt = $savienojums->prepare($clearQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        $_SESSION['pazinojums'] = "Grozs iztīrīts";
    }
}

// Close the statement and connection
$stmt->close();
$savienojums->close();

// Redirect back to cart page
header("Location: ../../grozs.php");
exit();
?>