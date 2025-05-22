<?php
// admin/db/grozs.php
// Get cart items from database

require_once "con_db.php";

// Initialize variables that will be used in the client side
$cart_items = [];
$has_items = false;

// Get cart items from database
$query = "SELECT g.*, p.nosaukums, p.cena, p.attels1 
          FROM grozs_sparkly g 
          JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
          WHERE g.lietotajvards = ? AND g.statuss = 'aktīvs'";
$stmt = $savienojums->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Store results in array for client side use
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}

// Set flag for whether cart has items
$has_items = (count($cart_items) > 0);

$stmt->close();
?>