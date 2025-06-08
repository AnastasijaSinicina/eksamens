<?php
require "con_db.php"; // Nodrošina datubāzes savienojumu

if (isset($_POST['ielogoties'])) {
    session_start();

    // Iegūst formas datus droši
    $lietotajvards = htmlspecialchars($_POST['lietotajvards']);
    $parole = $_POST['parole'];

    // Sagatavo vaicājumu lietotāja atrašanai
    $vaicajums = $savienojums->prepare("SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ? AND dzests = 0");
    $vaicajums->bind_param("s", $lietotajvards);
    $vaicajums->execute();
    $rezultats = $vaicajums->get_result();
    $lietotajs = $rezultats->fetch_assoc();

    if ($lietotajs) {
        // Pārbauda, vai parole atbilst
        if (password_verify($parole, $lietotajs['parole'])) {
            // Ielogošanās veiksmīga, saglabā sesiju
            $_SESSION['lietotajvardsSIN'] = $lietotajs['lietotajvards'];
            $_SESSION['loma'] = $lietotajs['loma']; // Saglabā lomu sesijā
            // Pārbauda, vai ir gaidāma prece, ko pievienot grozam
if (isset($_SESSION['pending_product'])) {
    // Inicializē groza, ja tas neeksistē
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    $product = $_SESSION['pending_product'];
    $id = $product['id'];
    
    // Pārbauda, vai prece jau ir grozā
    if (isset($_SESSION['cart'][$id])) {
        // Palielina daudzumu
        $_SESSION['cart'][$id]['quantity']++;
    } else {
        // Pievieno preci grozam
        $_SESSION['cart'][$id] = array(
            'nosaukums' => $product['nosaukums'],
            'cena' => $product['cena'],
            'attels' => $product['attels'],
            'quantity' => 1
        );
    }
    
    // Notīra
    unset($_SESSION['pending_product']);
    
    // Uzstāda veiksmīgo ziņojumu
    $_SESSION['pazinojums'] = "Prece pievienota grozam!";
}
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: ../../" . $redirect); 
            } else {
                // Noklusējuma novirzīšana atkarībā no lomas
                if ($lietotajs['loma'] === "admin" || $lietotajs['loma'] === "moder") {
                    header("Location: ../index.php"); 
                } elseif ($lietotajs['loma'] === "klients") {
                    header("Location: ../../index.php"); // Novirza uz klienta paneļa lapu
                }
            }
            exit();
        } else {
            // Nepareiza parole
            $_SESSION['pazinojums'] = "Nepareiza parole!";
            header("Location: ../../login.php");
            exit();
        }
    } else {
        // Lietotājs neeksistē
        $_SESSION['pazinojums'] = "Jūsu konts neeksistē vai ir dzēsts!";
        header("Location: ../../login.php");
        exit();
    }

    // Aizver vaicājumu un datubāzes savienojumu
    $vaicajums->close();
    $savienojums->close();
}
?>