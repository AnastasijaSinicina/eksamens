<?php
require "con_db.php"; // Ensure database connection

if (isset($_POST['ielogoties'])) {
    session_start();

    // Get form data safely
    $lietotajvards = htmlspecialchars($_POST['lietotajvards']);
    $parole = $_POST['parole'];

    // Prepare query to find the user
    $vaicajums = $savienojums->prepare("SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ? AND dzests = 0");
    $vaicajums->bind_param("s", $lietotajvards);
    $vaicajums->execute();
    $rezultats = $vaicajums->get_result();
    $lietotajs = $rezultats->fetch_assoc();

    if ($lietotajs) {
        // Check if password matches
        if (password_verify($parole, $lietotajs['parole'])) {
            // Login success, store session
            $_SESSION['lietotajvardsSIN'] = $lietotajs['lietotajvards'];
            $_SESSION['loma'] = $lietotajs['loma']; // Store role in session
            // Check if there's a pending product to add to cart
if (isset($_SESSION['pending_product'])) {
    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    $product = $_SESSION['pending_product'];
    $id = $product['id'];
    
    // Check if product is already in cart
    if (isset($_SESSION['cart'][$id])) {
        // Increment quantity
        $_SESSION['cart'][$id]['quantity']++;
    } else {
        // Add product to cart
        $_SESSION['cart'][$id] = array(
            'nosaukums' => $product['nosaukums'],
            'cena' => $product['cena'],
            'attels' => $product['attels'],
            'quantity' => 1
        );
    }
    
    // Clean up
    unset($_SESSION['pending_product']);
    
    // Set success message
    $_SESSION['pazinojums'] = "Prece pievienota grozam!";
}
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: ../../" . $redirect); 
            } else {
                // Default redirects based on role
                if ($lietotajs['loma'] === "admin" || $lietotajs['loma'] === "moder") {
                    header("Location: ../index.php"); 
                } elseif ($lietotajs['loma'] === "klients") {
                    header("Location: ../../index.php"); // Redirect to client dashboard
                }
            }
            exit();
        } else {
            // Invalid password
            $_SESSION['pazinojums'] = "Nepareiza parole!";
            header("Location: ../../login.php");
            exit();
        }
    } else {
        // User does not exist
        $_SESSION['pazinojums'] = "Jūsu konts neeksistē vai ir dzēsts!";
        header("Location: ../../login.php");
        exit();
    }

    // Close the statement and database connection
    $vaicajums->close();
    $savienojums->close();
}
?>
