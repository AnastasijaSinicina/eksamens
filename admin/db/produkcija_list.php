<?php
    require 'con_db.php'; // Make sure you include your database connection

    // SQL query to fetch bumba data with optional search functionality
    $sql = "SELECT * FROM produkcija_sprarkly";

    $result = mysqli_query($savienojums, $sql);

    $json = [];
    // Fetch results and prepare them for JSON encoding
    while ($bumba = $result->fetch_assoc()) {
        $json[] = array(
            'id_bumba' => htmlspecialchars($bumba['id_bumba']),
            'forma' => htmlspecialchars($bumba['forma']),
            'nosaukums' => htmlspecialchars($bumba['nosaukums']),
            'audums_id' => htmlspecialchars($bumba['audums_id']),
            'figura_id' => htmlspecialchars($bumba['figura_id']),
            'dekorejums1_id' => htmlspecialchars($bumba['dekorejums1_id']),
            'attels1' => base64_encode($bumba['attels1']),
            'attels2' => base64_encode($bumba['attels2']),
            'attels3' => base64_encode($bumba['attels3']),
            'cena' => htmlspecialchars($bumba['cena']),
        );
    }

    // Output the results as JSON
    echo json_encode($json);
?>