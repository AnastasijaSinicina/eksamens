<?php
require_once 'con_db.php'; 

$sql_formas = "SELECT id_forma, forma FROM sparkly_formas ORDER BY forma";
$result_formas = $savienojums->query($sql_formas);

$formas = [];
if ($result_formas && $result_formas->num_rows > 0) {
    while ($row = $result_formas->fetch_assoc()) {
        $formas[] = $row;
    }
}

$sql_audums = "SELECT id_audums, nosaukums FROM sparkly_audums ORDER BY nosaukums";
$result_audums = $savienojums->query($sql_audums);

$audumi = [];
if ($result_audums && $result_audums->num_rows > 0) {
    while ($row = $result_audums->fetch_assoc()) {
        $audumi[] = $row;
    }
}

$sql_malu_figuras = "SELECT id_malu_figura, nosaukums, attels FROM sparkly_malu_figura ORDER BY nosaukums";
$result_malu_figuras = $savienojums->query($sql_malu_figuras);

$malu_figuras = [];
if ($result_malu_figuras && $result_malu_figuras->num_rows > 0) {
    while ($row = $result_malu_figuras->fetch_assoc()) {
        $malu_figuras[] = $row;
    }
}

$sql_dekorejums1 = "SELECT id_dekorejums1, nosaukums, attels FROM sparkly_dekorejums1 ORDER BY nosaukums";
$result_dekorejums1 = $savienojums->query($sql_dekorejums1);

$dekorejumi1 = [];
if ($result_dekorejums1 && $result_dekorejums1->num_rows > 0) {
    while ($row = $result_dekorejums1->fetch_assoc()) {
        $dekorejumi1[] = $row;
    }
}

$sql_dekorejums2 = "SELECT id_dekorejums2, nosaukums, attels FROM sparkly_dekorejums2 ORDER BY nosaukums";
$result_dekorejums2 = $savienojums->query($sql_dekorejums2);

$dekorejumi2 = [];
if ($result_dekorejums2 && $result_dekorejums2->num_rows > 0) {
    while ($row = $result_dekorejums2->fetch_assoc()) {
        $dekorejumi2[] = $row;
    }
}

?>