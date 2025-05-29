<?php

require_once "con_db.php";

// Inicializē mainīgos, kas tiks izmantoti klientu pusē
$cart_items = []; // Masīvs, kas saturēs visas groza preces
$has_items = false; // Būla vērtība, kas norāda, vai grozā ir preces

// Iegūst groza preces no datubāzes
// JOIN vaicājums apvieno groza tabulu ar produkcijas tabulu, lai iegūtu pilnu informāciju

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

// Sagatavo SQL vaicājumu, lai novērstu SQL injection uzbrukumus
$stmt = $savienojums->prepare($query);
$stmt->bind_param("s", $username); // Piesaista lietotājvārdu kā string parametru
$stmt->execute(); // Izpilda vaicājumu
$result = $stmt->get_result(); // Iegūst rezultātus

// Saglabā rezultātus masīvā, lai tos varētu izmantot klientu pusē
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row; // Pievieno katru preci masīvam
}

// Uzstāda karodziņu, kas norāda, vai grozā ir kādas preces
$has_items = (count($cart_items) > 0);

// Aizvēr prepared statement, lai atbrīvotu resursus
=======
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