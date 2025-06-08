<?php
/**
 * Pasūtījuma datu iegūšanas vaicājumi
 * Šis fails satur visus vaicājumus pasūtījuma lapas attēlošanai
 */

// Pārbaudām vai ir savienojums ar datu bāzi
if (!isset($savienojums)) {
    require_once "con_db.php";
}

// 1. LIETOTĀJA INFORMĀCIJAS IEGŪŠANA
// Pārbaudām vai ir lietotājvārds sesijā
if (isset($_SESSION['lietotajvardsSIN'])) {
    $lietotajvards = $_SESSION['lietotajvardsSIN'];
    
    // Sagatavojam vaicājumu lietotāja informācijas iegūšanai
    $lietotaja_vaicajums = "SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $lietotaja_stmt = $savienojums->prepare($lietotaja_vaicajums);
    $lietotaja_stmt->bind_param("s", $lietotajvards);
    $lietotaja_stmt->execute();
    $lietotaja_rezultats = $lietotaja_stmt->get_result();
    
    // Pārbaudām vai lietotājs tika atrasts
    if ($lietotaja_rezultats->num_rows > 0) {
        $lietotajs = $lietotaja_rezultats->fetch_assoc();
    } else {
        // Ja lietotājs nav atrasts, pārvirzām uz sākumlapu
        header("Location: index.php");
        exit();
    }
    
    // Atbrīvojam atmiņu
    $lietotaja_stmt->close();
} else {
    // Ja nav lietotājvārda sesijā, pārvirzām uz pieteikšanās lapu
    header("Location: login.php");
    exit();
}

// 2. PASŪTĪJUMA INFORMĀCIJAS IEGŪŠANA
// Pārbaudām vai ir pasūtījuma ID un lietotāja informācija
if (isset($order_id) && isset($lietotajs['id_lietotajs'])) {
    
    // Sagatavojam vaicājumu pasūtījuma informācijas iegūšanai
    $pasutijuma_vaicajums = "SELECT * FROM sparkly_pasutijumi 
                           WHERE id_pasutijums = ? AND lietotajs_id = ?";
    $pasutijuma_stmt = $savienojums->prepare($pasutijuma_vaicajums);
    $pasutijuma_stmt->bind_param("ii", $order_id, $lietotajs['id_lietotajs']);
    $pasutijuma_stmt->execute();
    $pasutijuma_rezultats = $pasutijuma_stmt->get_result();
    
    // Pārbaudām vai pasūtījums tika atrasts un pieder pašreizējam lietotājam
    if ($pasutijuma_rezultats->num_rows > 0) {
        $pasutijums = $pasutijuma_rezultats->fetch_assoc();
    } else {
        // Ja pasūtījums nav atrasts vai nepieder lietotājam, pārvirzām uz sākumlapu
        header("Location: index.php");
        exit();
    }
    
    // Atbrīvojam atmiņu
    $pasutijuma_stmt->close();
    
} else {
    // Ja nav nepieciešamo datu, pārvirzām uz sākumlapu
    header("Location: index.php");
    exit();
}

// 3. PASŪTĪJUMA VIENUMU INFORMĀCIJAS IEGŪŠANA
// Pārbaudām vai ir pasūtījuma ID
if (isset($order_id)) {
    
    // Sagatavojam vaicājumu pasūtījuma vienumu iegūšanai ar produktu informāciju
    $vienumi_vaicajums = "SELECT pv.*, p.attels1, p.nosaukums
                         FROM sparkly_pasutijuma_vienumi pv
                         LEFT JOIN produkcija_sprarkly p ON pv.produkta_id = p.id_bumba
                         WHERE pv.pasutijuma_id = ?";
    $vienumi_stmt = $savienojums->prepare($vienumi_vaicajums);
    $vienumi_stmt->bind_param("i", $order_id);
    $vienumi_stmt->execute();
    $vienumi_rezultats = $vienumi_stmt->get_result();
    
    // Iestatām mainīgo vai ir vienumi
    $ir_vienumi = ($vienumi_rezultats->num_rows > 0);
    
    // Piezīme: $vienumi_stmt netiek aizvērts šeit, jo $vienumi_rezultats tiek izmantots galvenajā failā
    
} else {
    // Ja nav pasūtījuma ID, pārvirzām uz sākumlapu
    header("Location: index.php");
    exit();
}

?>