<?php
// admin/db/update_grozs.php
// Handle cart update operations

session_start();
require_once "con_db.php";

// Check if user is logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties!";
    header("Location: ../../login.php");
    exit();
}

$username = $_SESSION['lietotajvardsSIN'];

// Handle increase quantity
if (isset($_POST['increase']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Update quantity (increase by 1)
    $updateQuery = "UPDATE grozs_sparkly SET daudzums = daudzums + 1 WHERE id_grozs = ? AND lietotajvards = ? AND statuss = 'aktīvs'";
    $stmt = $savienojums->prepare($updateQuery);
    $stmt->bind_param("is", $id, $username);
    
    if ($stmt->execute()) {
        $_SESSION['pazinojums'] = "Daudzums palielināts";
    } else {
        $_SESSION['pazinojums'] = "Kļūda palielinot daudzumu";
    }
    $stmt->close();
}

// Handle decrease quantity
elseif (isset($_POST['decrease']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Get current quantity
    $query = "SELECT daudzums FROM grozs_sparkly WHERE id_grozs = ? AND lietotajvards = ? AND statuss = 'aktīvs'";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($row['daudzums'] > 1) {
            // Decrease quantity
            $updateQuery = "UPDATE grozs_sparkly SET daudzums = daudzums - 1 WHERE id_grozs = ? AND lietotajvards = ? AND statuss = 'aktīvs'";
            $stmt2 = $savienojums->prepare($updateQuery);
            $stmt2->bind_param("is", $id, $username);
            $stmt2->execute();
            $stmt2->close();
            
            $_SESSION['pazinojums'] = "Daudzums samazināts";
        } else {
            // Remove item if quantity would be 0
            $deleteQuery = "UPDATE grozs_sparkly SET statuss = 'neaktīvs' WHERE id_grozs = ? AND lietotajvards = ? AND statuss = 'aktīvs'";
            $stmt2 = $savienojums->prepare($deleteQuery);
            $stmt2->bind_param("is", $id, $username);
            $stmt2->execute();
            $stmt2->close();
            
            $_SESSION['pazinojums'] = "Prece izņemta no groza";
        }
    }
    $stmt->close();
}

// Handle remove item
elseif (isset($_POST['remove']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Set item status to inactive instead of deleting
    $deleteQuery = "UPDATE grozs_sparkly SET statuss = 'neaktīvs' WHERE id_grozs = ? AND lietotajvards = ? AND statuss = 'aktīvs'";
    $stmt = $savienojums->prepare($deleteQuery);
    $stmt->bind_param("is", $id, $username);
    
    if ($stmt->execute()) {
        $_SESSION['pazinojums'] = "Prece izņemta no groza";
    } else {
        $_SESSION['pazinojums'] = "Kļūda izņemot preci";
    }
    $stmt->close();
}

// Handle clear cart
elseif (isset($_POST['clear']) && isset($_POST['user'])) {
    $user = $_POST['user'];
    
    // Check if the current user is the same as in the form
    if ($user === $username) {
        // Set all items status to inactive instead of deleting
        $clearQuery = "UPDATE grozs_sparkly SET statuss = 'neaktīvs' WHERE lietotajvards = ? AND statuss = 'aktīvs'";
        $stmt = $savienojums->prepare($clearQuery);
        $stmt->bind_param("s", $username);
        
        if ($stmt->execute()) {
            $_SESSION['pazinojums'] = "Grozs iztīrīts";
        } else {
            $_SESSION['pazinojums'] = "Kļūda iztīrot grozu";
        }
        $stmt->close();
    }
}

// Close the connection
$savienojums->close();

// Redirect back to cart page
header("Location: ../../grozs.php");
exit();
?>