<?php
session_start();

// Check if user is logged in
// Check if user is logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    // Store product info in session to add after login
    $_SESSION['pending_product'] = [
        'id' => $_POST['id'],
        'nosaukums' => $_POST['nosaukums'],
        'cena' => $_POST['cena'],
        'attels' => $_POST['attels']
    ];
    
    // Set redirect URL to come back to product page
    $_SESSION['redirect_after_login'] = 'produkts.php?id=' . $_POST['id'];
    
    // Set message
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai pievienotu preces grozam";
    
    // Redirect to login
    header("Location: ../login.php");
    exit();
}

// Check if the add to cart button was clicked
if (isset($_POST['add_to_cart'])) {
    // Get product details
    $id = $_POST['id'];
    $nosaukums = $_POST['nosaukums'];
    $cena = $_POST['cena'];
    $attels = $_POST['attels'];
    
    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Check if product is already in cart
    if (isset($_SESSION['cart'][$id])) {
        // Increment quantity
        $_SESSION['cart'][$id]['quantity']++;
    } else {
        // Add product to cart
        $_SESSION['cart'][$id] = array(
            'nosaukums' => $nosaukums,
            'cena' => $cena,
            'attels' => $attels,
            'quantity' => 1
        );
    }
    
    // Set success message
    $_SESSION['pazinojums'] = "Prece pievienota grozam!";
    
    // Redirect back to the product page
    header("Location: ../produkts.php?id=" . $id);
    exit();
}
?>