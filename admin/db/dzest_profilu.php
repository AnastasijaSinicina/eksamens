<?php
    session_start();


// Pārbauda, vai lietotājs ir ielogojies
if (!isset($_SESSION['lietotajvardsSIN'])) {

    $_SESSION['pazinojums'] = "Lūdzu, ielogojieties!";
    header("Location: login.php");
    exit();
}

require_once "con_db.php";

// Iegūst pašreizējā lietotāja vārdu no sesijas
$lietotajvards = $_SESSION['lietotajvardsSIN'];

// Meklē lietotāja ID datubāzē pēc lietotājvārda
$vaicajums = $savienojums->prepare("SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?");
$vaicajums->bind_param("s", $lietotajvards);
$vaicajums->execute();
$rezultats = $vaicajums->get_result();
$lietotajs = $rezultats->fetch_assoc();

// Pārbauda, vai lietotājs tika atrasts datubāzē
if (!$lietotajs) {
    // Ja lietotājs nav atrasts, uzstāda kļūdas ziņojumu un novirza uz profila lapu
    $_SESSION['pazinojums'] = "Lietotājs nav atrasts!";
    header("Location: profils.php");
    exit();
}

// Veic "mīksto dzēšanu" - nevis izdzēš ierakstu, bet uzstāda dzests kolonu uz 1
// Tas ļauj saglabāt datus administrācijai, bet lietotājs vairs nevar piekļūt kontam
$dzest_vaicajums = $savienojums->prepare("UPDATE lietotaji_sparkly SET dzests = 1 WHERE id_lietotajs = ?");
$dzest_vaicajums->bind_param("i", $lietotajs['id_lietotajs']);

// Izpilda dzēšanas vaicājumu
if ($dzest_vaicajums->execute()) {
    // Ja dzēšana veiksmīga, notīra visus sesijas mainīgos un iznīcina sesiju
    session_unset();
    session_destroy();
    
    // Sāk jaunu sesiju, lai varētu parādīt paziņojumu
    session_start();
    $_SESSION['pazinojums'] = "Jūsu konts ir veiksmīgi izdzēsts!";
    
    // Novirza uz login lapu ar veiksmīgu ziņojumu
    header("Location: ../../login.php");
    exit();
} else {
    // Ja dzēšana neizdevās, uzstāda kļūdas ziņojumu un novirza uz profila lapu
    $_SESSION['pazinojums'] = "Kļūda konta dzēšanā. Lūdzu mēģiniet vēlreiz.";
    header("Location: profils.php");
    exit();
}
?>