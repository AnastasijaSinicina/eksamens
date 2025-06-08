<?php
require_once "con_db.php";

// Inicializē mainīgos, kas tiks izmantoti klienta pusē
$cart_items = [];
$has_items = false;

// Iegūst groza preces no datubāzes
$query = "SELECT g.*, p.nosaukums, p.cena, p.attels1 
          FROM grozs_sparkly g 
          JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
          WHERE g.lietotajvards = ? AND g.statuss = 'aktīvs'";

// Izpilda vaicājumu vienu reizi
$stmt = $savienojums->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Saglabā rezultātus masīvā
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}

// Uzstāda karodziņu tam, vai grozā ir preces
$has_items = (count($cart_items) > 0);

$stmt->close();
?>