<?php
require 'con_db.php';

// Iegūst visus auduma ierakstus ar lietotāju informāciju
if (isset($_GET['fetch_audums'])) {

    // SQL vaicājums, lai iegūtu audumus ar redaktoru un izveidotāju informāciju
    $sql = "SELECT a.*, 
                   m.lietotajvards as red_liet_username,
                   m.vards as red_liet_first_name,
                   m.uzvards as red_liet_last_name,
                   c.lietotajvards as izveidots_liet_username,
                   c.vards as izveidots_liet_first_name,
                   c.uzvards as izveidots_liet_last_name,
                   a.red_dat,
                   a.datums
            FROM sparkly_audums a
            LEFT JOIN lietotaji_sparkly m ON a.red_liet = m.id_lietotajs
            LEFT JOIN lietotaji_sparkly c ON a.izveidots_liet = c.id_lietotajs
            ORDER BY a.id_audums";
    $result = $savienojums->query($sql);

    // Sagatavo audumu masīvu rezultātiem
    $audums = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Formatē redaktora vārdu - prioritāte vārds/uzvārds, pēc tam lietotājvārds
            if (!empty($row['red_liet_first_name']) && !empty($row['red_liet_last_name'])) {
                $row['red_liet_name'] = $row['red_liet_first_name'] . ' ' . $row['red_liet_last_name'];
            } else if (!empty($row['red_liet_username'])) {
                $row['red_liet_name'] = $row['red_liet_username'];
            }

            // Formatē izveidotāja vārdu - prioritāte vārds/uzvārds, pēc tam lietotājvārds
            if (!empty($row['izveidots_liet_first_name']) && !empty($row['izveidots_liet_last_name'])) {
                $row['izveidots_liet_name'] = $row['izveidots_liet_first_name'] . ' ' . $row['izveidots_liet_last_name'];
            } else if (!empty($row['izveidots_liet_username'])) {
                $row['izveidots_liet_name'] = $row['izveidots_liet_username'];
            }

            $audums[] = $row;
        }
    }

    // Atgriež JSON formātā un beidz skriptu
    echo json_encode($audums); 
    exit;
}

// Iegūst vienu konkrētu auduma ierakstu pēc ID
if (isset($_GET['fetch_audums_single']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Drošs SQL vaicājums ar parametru saistīšanu
    $sql = "SELECT * FROM sparkly_audums WHERE id_audums = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $audums = $result->fetch_assoc();
        echo json_encode($audums);
    } else {
        echo json_encode(null);
    }
    $stmt->close();
    exit;
}

// Iegūst visus dekorējuma ierakstus ar lietotāju informāciju un attēliem
if (isset($_GET['fetch_dekorejums1'])) {
    // SQL vaicājums dekorējumiem ar lietotāju informāciju
    $sql = "SELECT d.*, 
                   m.lietotajvards as red_liet_username,
                   m.vards as red_liet_first_name,
                   m.uzvards as red_liet_last_name,
                   c.lietotajvards as izveidots_liet_username,
                   c.vards as izveidots_liet_first_name,
                   c.uzvards as izveidots_liet_last_name,
                   d.red_dat,
                   d.datums
            FROM sparkly_dekorejums1 d
            LEFT JOIN lietotaji_sparkly m ON d.red_liet = m.id_lietotajs
            LEFT JOIN lietotaji_sparkly c ON d.izveidots_liet = c.id_lietotajs
            ORDER BY d.id_dekorejums1";
    $result = $savienojums->query($sql);

    // Sagatavo dekorējumu masīvu rezultātiem
    $dekorejums = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Konvertē binārās attēla datus uz base64 formātu priekš JSON
            if (!empty($row['attels'])) {
                $row['attels'] = base64_encode($row['attels']);
            }
            
            // Formatē redaktora vārdu
            if (!empty($row['red_liet_first_name']) && !empty($row['red_liet_last_name'])) {
                $row['red_liet_name'] = $row['red_liet_first_name'] . ' ' . $row['red_liet_last_name'];
            } else if (!empty($row['red_liet_username'])) {
                $row['red_liet_name'] = $row['red_liet_username'];
            }

            // Formatē izveidotāja vārdu
            if (!empty($row['izveidots_liet_first_name']) && !empty($row['izveidots_liet_last_name'])) {
                $row['izveidots_liet_name'] = $row['izveidots_liet_first_name'] . ' ' . $row['izveidots_liet_last_name'];
            } else if (!empty($row['izveidots_liet_username'])) {
                $row['izveidots_liet_name'] = $row['izveidots_liet_username'];
            }
            
            $dekorejums[] = $row;
        }
    }

    echo json_encode($dekorejums);
    exit;
}

// Iegūst vienu konkrētu dekorējuma ierakstu pēc ID
if (isset($_GET['fetch_dekorejums1_single']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Drošs SQL vaicājums vienam dekorējumam
    $sql = "SELECT * FROM sparkly_dekorejums1 WHERE id_dekorejums1 = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dekorejums = $result->fetch_assoc();
        // Konvertē attēlu uz base64, ja tas eksistē
        if (!empty($dekorejums['attels'])) {
            $dekorejums['attels'] = base64_encode($dekorejums['attels']);
        }
        echo json_encode($dekorejums);
    } else {
        echo json_encode(null);
    }
    $stmt->close();
    exit;
}

// Iegūst visas malu figūras ar lietotāju informāciju un attēliem
if (isset($_GET['fetch_figuras'])) {
    // SQL vaicājums malu figūrām ar lietotāju informāciju
    $sql = "SELECT f.*, 
                   m.lietotajvards as red_liet_username,
                   m.vards as red_liet_first_name,
                   m.uzvards as red_liet_last_name,
                   c.lietotajvards as izveidots_liet_username,
                   c.vards as izveidots_liet_first_name,
                   c.uzvards as izveidots_liet_last_name,
                   f.red_dat,
                   f.datums
            FROM sparkly_malu_figura f
            LEFT JOIN lietotaji_sparkly m ON f.red_liet = m.id_lietotajs
            LEFT JOIN lietotaji_sparkly c ON f.izveidots_liet = c.id_lietotajs
            ORDER BY f.id_malu_figura";
    $result = $savienojums->query($sql);

    // Sagatavo figūru masīvu rezultātiem
    $figuras = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Konvertē attēlu uz base64 formātu
            if (!empty($row['attels'])) {
                $row['attels'] = base64_encode($row['attels']);
            }
            
            // Formatē redaktora vārdu
            if (!empty($row['red_liet_first_name']) && !empty($row['red_liet_last_name'])) {
                $row['red_liet_name'] = $row['red_liet_first_name'] . ' ' . $row['red_liet_last_name'];
            } else if (!empty($row['red_liet_username'])) {
                $row['red_liet_name'] = $row['red_liet_username'];
            }
            
            // Formatē izveidotāja vārdu
            if (!empty($row['izveidots_liet_first_name']) && !empty($row['izveidots_liet_last_name'])) {
                $row['izveidots_liet_name'] = $row['izveidots_liet_first_name'] . ' ' . $row['izveidots_liet_last_name'];
            } else if (!empty($row['izveidots_liet_username'])) {
                $row['izveidots_liet_name'] = $row['izveidots_liet_username'];
            }
            $figuras[] = $row;
        }
    }

    echo json_encode($figuras);
    exit;
}

// Iegūst vienu konkrētu malu figūru pēc ID
if (isset($_GET['fetch_figuras_single']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Drošs SQL vaicājums vienai figūrai
    $sql = "SELECT * FROM sparkly_malu_figura WHERE id_malu_figura = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $figura = $result->fetch_assoc();
        // Konvertē attēlu uz base64, ja tas eksistē
        if (!empty($figura['attels'])) {
            $figura['attels'] = base64_encode($figura['attels']);
        }
        echo json_encode($figura);
    } else {
        echo json_encode(null);
    }
    $stmt->close();
    exit;
}

// Iegūst visas formas ar lietotāju informāciju
if (isset($_GET['fetch_formas'])) {
    // SQL vaicājums formām ar lietotāju informāciju
    $sql = "SELECT f.*, 
                   m.lietotajvards as red_liet_username,
                   m.vards as red_liet_first_name,
                   m.uzvards as red_liet_last_name,
                   c.lietotajvards as izveidots_liet_username,
                   c.vards as izveidots_liet_first_name,
                   c.uzvards as izveidots_liet_last_name,
                   f.red_dat,
                   f.datums
            FROM sparkly_formas f
            LEFT JOIN lietotaji_sparkly m ON f.red_liet = m.id_lietotajs
            LEFT JOIN lietotaji_sparkly c ON f.izveidots_liet = c.id_lietotajs
            ORDER BY f.id_forma";
    $result = $savienojums->query($sql);

    // Sagatavo formu masīvu rezultātiem
    $formas = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Formatē redaktora vārdu
            if (!empty($row['red_liet_first_name']) && !empty($row['red_liet_last_name'])) {
                $row['red_liet_name'] = $row['red_liet_first_name'] . ' ' . $row['red_liet_last_name'];
            } else if (!empty($row['red_liet_username'])) {
                $row['red_liet_name'] = $row['red_liet_username'];
            }
            
            // Formatē izveidotāja vārdu
            if (!empty($row['izveidots_liet_first_name']) && !empty($row['izveidots_liet_last_name'])) {
                $row['izveidots_liet_name'] = $row['izveidots_liet_first_name'] . ' ' . $row['izveidots_liet_last_name'];
            } else if (!empty($row['izveidots_liet_username'])) {
                $row['izveidots_liet_name'] = $row['izveidots_liet_username'];
            }
            $formas[] = $row;
        }
    }

    echo json_encode($formas);
    exit;
}

// Iegūst vienu konkrētu formu pēc ID
if (isset($_GET['fetch_formas_single']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Drošs SQL vaicājums vienai formai
    $sql = "SELECT * FROM sparkly_formas WHERE id_forma = ?";
    $stmt = $savienojums->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $forma = $result->fetch_assoc();
        echo json_encode($forma);
    } else {
        echo json_encode(null);
    }
    $stmt->close();
    exit;
}
?>