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
            'audums' => htmlspecialchars($bumba['audums']),
            'malu_figura' => htmlspecialchars($bumba['malu_figura']),
            'dekorejums' => htmlspecialchars($bumba['dekorejums']),
            'dekorejums2' => htmlspecialchars($bumba['dekorejums2']),
            'attels1' => base64_encode($bumba['attels1']),
            'attels2' => base64_encode($bumba['attels2']),
            'attels3' => base64_encode($bumba['attels3']),
            'cena' => htmlspecialchars($bumba['cena']),
        );
    }

    // Output the results as JSON
    echo json_encode($json);
?>