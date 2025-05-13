<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pārbauda, vai lietotājs ir pieslēdzies
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Nepieciešama autorizācija!";
    header("Location: login.php");
    exit();
}

// Pārbauda, vai saņemts pasūtījuma ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['pazinojums'] = "Nekorrekts pasūtījuma ID!";
    header("Location: profils.php#orders");
    exit();
}

require_once "con_db.php";

$pasutijuma_id = intval($_GET['id']);
$lietotajvards = $_SESSION['lietotajvardsSIN'];

// Iegūst lietotāja ID
$lietotaja_vaicajums = $savienojums->prepare("SELECT id_lietotajs FROM lietotaji_sparkly WHERE lietotajvards = ?");
$lietotaja_vaicajums->bind_param("s", $lietotajvards);
$lietotaja_vaicajums->execute();
$lietotaja_rezultats = $lietotaja_vaicajums->get_result();
$lietotajs = $lietotaja_rezultats->fetch_assoc();

if (!$lietotajs) {
    $_SESSION['pazinojums'] = "Lietotājs nav atrasts!";
    header("Location: ../../profils.php");
    exit();
}

// Pārbauda, vai pasūtījums pieder šim lietotājam un vai to var mainīt uz "Saņemts"
$pasutijuma_vaicajums = $savienojums->prepare(
    "SELECT * FROM sparkly_pasutijumi 
     WHERE id_pasutijums = ? AND lietotajs_id = ? AND statuss = 'Aizsūtīts'"
);
$pasutijuma_vaicajums->bind_param("ii", $pasutijuma_id, $lietotajs['id_lietotajs']);
$pasutijuma_vaicajums->execute();
$pasutijuma_rezultats = $pasutijuma_vaicajums->get_result();
$pasutijums = $pasutijuma_rezultats->fetch_assoc();

if (!$pasutijums) {
    $_SESSION['pazinojums'] = "Pasūtījums nav atrasts vai to nevar mainīt!";
    header("Location: ../../profils.php");
    exit();
}

// Atjaunina pasūtījuma statusu uz "Saņemts"
$atjauninat_vaicajums = $savienojums->prepare(
    "UPDATE sparkly_pasutijumi 
     SET statuss = 'Saņemts', 
         sanemsanas_datums = CURRENT_TIMESTAMP 
     WHERE id_pasutijums = ?"
);
$atjauninat_vaicajums->bind_param("i", $pasutijuma_id);

if ($atjauninat_vaicajums->execute()) {
    $_SESSION['pazinojums'] = "Pasūtījums #" . $pasutijuma_id . " ir veiksmīgi atzīmēts kā saņemts!";

    
} else {
    $_SESSION['pazinojums'] = "Radās kļūda, mēģinot atjaunināt pasūtījuma statusu. Lūdzu, mēģiniet vēlreiz!";
}

// Novirza atpakaļ uz profila lapas pasūtījumu cilni
header("Location: ../../profils.php");
exit();
?>