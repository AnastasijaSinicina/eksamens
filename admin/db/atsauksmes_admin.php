<?php
    require 'header.php';
    require 'con_db.php';

    // Apstrādā atsauksmju apstiprināšanu/noraidīšanu
    if (isset($_GET['approve'])) {
        $id = $_GET['approve'];
        // SQL vaicājums atsauksmes apstiprināšanai (apstiprinats = 1)
        $sql = "UPDATE sparkly_atsauksmes SET apstiprinats = 1 WHERE id_atsauksme = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Parāda veiksmīgu ziņojumu
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('success', 'Veiksmīgi!', 'Atsauksme ir apstiprināta.');
                    });
                  </script>";
        } else {
            // Parāda kļūdas ziņojumu
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('error', 'Kļūda!', 'Neizdevās apstiprināt atsauksmi.');
                    });
                  </script>";
        }
        $stmt->close();
    }

    // Apstrādā atsauksmes noraidīšanu
    if (isset($_GET['reject'])) {
        $id = $_GET['reject'];
        // SQL vaicājums atsauksmes noraidīšanai (apstiprinats = 0)
        $sql = "UPDATE sparkly_atsauksmes SET apstiprinats = 0 WHERE id_atsauksme = ?";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Parāda veiksmīgu ziņojumu
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('success', 'Veiksmīgi!', 'Atsauksme ir noraidīta.');
                    });
                  </script>";
        } else {
            // Parāda kļūdas ziņojumu
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showNotification('error', 'Kļūda!', 'Neizdevās noraidīt atsauksmi.');
                    });
                  </script>";
        }
        $stmt->close();
    }

    // Iegūst filtrēšanas parametrus no URL
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $rating_filter = isset($_GET['rating']) ? $_GET['rating'] : '';
    
    // Izveido WHERE nosacījumu masīvu filtrēšanai
    $where_conditions = [];
    $params = [];
    $types = '';
    
    // Pārbauda statusu filtru
    if ($status_filter === 'approved') {
        // Tikai apstiprinātas atsauksmes
        $where_conditions[] = "a.apstiprinats = 1";
    } elseif ($status_filter === 'pending') {
        // Tikai neapstiprinātas atsauksmes (gaida apstiprinājumu)
        $where_conditions[] = "a.apstiprinats = 0";
    }
    
    // Pārbauda vērtējumu filtru
    if (!empty($rating_filter)) {
        $where_conditions[] = "a.zvaigznes = ?";
        $params[] = $rating_filter;
        $types .= 'i'; // integer tips parametram
    }
    
    // Izveido galīgo SQL vaicājumu
    $query = "SELECT a.*, l.lietotajvards, l.foto
              FROM sparkly_atsauksmes a
              LEFT JOIN lietotaji_sparkly l ON a.lietotajs_id = l.id_lietotajs";
    
    // Pievieno WHERE nosacījumus, ja tie eksistē
    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Kārto pēc datuma (jaunākās vispirms)
    $query .= " ORDER BY a.datums DESC";
    
    // Sagatavo un izpilda vaicājumu
    $stmt = $savienojums->prepare($query);
    if ($stmt === false) {
        // Pārtrauc izpildi, ja vaicājuma sagatavošana neizdevās
        die('Prepare failed: ' . $savienojums->error);
    }
    
    // Piesaista parametrus, ja tie eksistē
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $feedback_result = $stmt->get_result();
?>