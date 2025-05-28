<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once "con_db.php";

function getUserOrders($savienojums, $lietotajs_id) {
    $order_query = "SELECT p.*, COUNT(pv.vienums_id) as total_items
                    FROM sparkly_pasutijumi p
                    LEFT JOIN sparkly_pasutijuma_vienumi pv ON p.id_pasutijums = pv.pasutijuma_id
                    WHERE p.lietotajs_id = ?
                    GROUP BY p.id_pasutijums
                    ORDER BY p.pas_datums DESC";
    $order_stmt = $savienojums->prepare($order_query);
    $order_stmt->bind_param("i", $lietotajs_id);
    $order_stmt->execute();
    return $order_stmt->get_result();
}

function getUserInfo($savienojums, $lietotajvards) {
    $vaicajums = $savienojums->prepare("SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?");
    $vaicajums->bind_param("s", $lietotajvards);
    $vaicajums->execute();
    $rezultats = $vaicajums->get_result();
    return $rezultats->fetch_assoc();
}

function getOrderItems($savienojums, $order_id) {
    $items_query = "SELECT pv.*, p.nosaukums, p.attels1
                   FROM sparkly_pasutijuma_vienumi pv
                   JOIN produkcija_sprarkly p ON pv.produkta_id = p.id_bumba
                   WHERE pv.pasutijuma_id = ?";
    $items_stmt = $savienojums->prepare($items_query);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    return $items_stmt->get_result();
}
header("Location: ../../profils.php");
exit();
?>