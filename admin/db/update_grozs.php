<?php
// admin/db/update_grozs.php
// Apstrādā groza atjaunošanas operācijas

session_start();
require_once "con_db.php";

// Iegūst lietotājvārdu no sesijas
$username = $_SESSION['lietotajvardsSIN'];

// Apstrādā daudzuma palielināšanu
if (isset($_POST['increase']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Atjaunina daudzumu (palielina par 1)
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

// Apstrādā daudzuma samazināšanu
elseif (isset($_POST['decrease']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Iegūst pašreizējo daudzumu
    $query = "SELECT daudzums FROM grozs_sparkly WHERE id_grozs = ? AND lietotajvards = ? AND statuss = 'aktīvs'";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($row['daudzums'] > 1) {
            // Samazina daudzumu
            $updateQuery = "UPDATE grozs_sparkly SET daudzums = daudzums - 1 WHERE id_grozs = ? AND lietotajvards = ? AND statuss = 'aktīvs'";
            $stmt2 = $savienojums->prepare($updateQuery);
            $stmt2->bind_param("is", $id, $username);
            $stmt2->execute();
            $stmt2->close();
            
            $_SESSION['pazinojums'] = "Daudzums samazināts";
        } else {
            // Izņem preci, ja daudzums būtu 0
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

// Apstrādā preces izņemšanu
elseif (isset($_POST['remove']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Iestata preces statusu kā neaktīvu, nevis dzēš
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

// Apstrādā groza iztīrīšanu
elseif (isset($_POST['clear']) && isset($_POST['user'])) {
    $user = $_POST['user'];
    
    // Pārbauda, vai pašreizējais lietotājs ir tas pats, kas formā
    if ($user === $username) {
        // Iestata visu preču statusu kā neaktīvu, nevis dzēš
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

// Aizver savienojumu
$savienojums->close();

// Novirza atpakaļ uz groza lapu
header("Location: ../../grozs.php");
exit();
?>