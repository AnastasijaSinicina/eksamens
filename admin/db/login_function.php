<?php
require "con_db.php"; // Ensure database connection

if (isset($_POST['ielogoties'])) {
    session_start();

    // Get form data safely
    $lietotajvards = htmlspecialchars($_POST['lietotajvards']);
    $parole = $_POST['parole'];

    // Prepare query to find the user
    $vaicajums = $savienojums->prepare("SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?");
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

            // Redirect based on role
            if ($lietotajs['loma'] === "admin" || $lietotajs['loma'] === "moder") {
                header("Location: ../index.php"); // Redirect to admin dashboard
            } elseif ($lietotajs['loma'] === "klients") {
                header("Location: ../../index.php"); // Redirect to client dashboard
            } else {
                header("Location: ../../login.php"); // Redirect back to login on unknown role
            }
            exit();
        } else {
            // Invalid password
            $_SESSION['pazinojums'] = "Nepareizs lietotājvārds vai parole!";
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
