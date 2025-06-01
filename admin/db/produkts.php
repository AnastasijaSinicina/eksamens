<?php
require 'con_db.php'; // Database connection

$bumba = null;
$product_found = false;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input

    // Check if tables exist
    $formas_table_exists = false;
    $audums_table_exists = false;
    $malu_figura_table_exists = false;
    $dekorejums1_table_exists = false;
    
    $check_formas_table_sql = "SHOW TABLES LIKE 'sparkly_formas'";
    $result = $savienojums->query($check_formas_table_sql);
    if (isset($result) && $result->num_rows > 0) {
        $formas_table_exists = true;
    }
    
    $check_audums_table_sql = "SHOW TABLES LIKE 'sparkly_audums'";
    $result = $savienojums->query($check_audums_table_sql);
    if (isset($result) && $result->num_rows > 0) {
        $audums_table_exists = true;
    }
    
    $check_malu_figura_table_sql = "SHOW TABLES LIKE 'sparkly_malu_figura'";
    $result = $savienojums->query($check_malu_figura_table_sql);
    if (isset($result) && $result->num_rows > 0) {
        $malu_figura_table_exists = true;
    }
    
    $check_dekorejums1_table_sql = "SHOW TABLES LIKE 'sparkly_dekorejums1'";
    $result = $savienojums->query($check_dekorejums1_table_sql);
    if (isset($result) && $result->num_rows > 0) {
        $dekorejums1_table_exists = true;
    }

    // Check if columns exist
    $audums_id_exists = false;
    $figura_id_exists = false;
    $dekorejums1_id_exists = false;
    
    $check_audums_id_sql = "SHOW COLUMNS FROM produkcija_sprarkly LIKE 'audums_id'";
    $result = $savienojums->query($check_audums_id_sql);
    if (isset($result) && $result->num_rows > 0) {
        $audums_id_exists = true;
    }
    
    $check_figura_id_sql = "SHOW COLUMNS FROM produkcija_sprarkly LIKE 'figura_id'";
    $result = $savienojums->query($check_figura_id_sql);
    if (isset($result) && $result->num_rows > 0) {
        $figura_id_exists = true;
    }
    
    $check_dekorejums1_id_sql = "SHOW COLUMNS FROM produkcija_sprarkly LIKE 'dekorejums1_id'";
    $result = $savienojums->query($check_dekorejums1_id_sql);
    if (isset($result) && $result->num_rows > 0) {
        $dekorejums1_id_exists = true;
    }

    // Construct query based on existing schema
    $query = "SELECT p.* ";
    
    if ($formas_table_exists) {
        $query .= ", f.forma AS forma_name ";
    }
    
    if ($audums_table_exists && $audums_id_exists) {
        $query .= ", a.nosaukums AS audums_name ";
    }
    
    if ($malu_figura_table_exists && $figura_id_exists) {
        $query .= ", m.nosaukums AS malu_figura_name ";
    }
    
    if ($dekorejums1_table_exists && $dekorejums1_id_exists) {
        $query .= ", d1.nosaukums AS dekorejums1_name ";
    }

    $query .= " FROM produkcija_sprarkly p ";
    
    if ($formas_table_exists) {
        $query .= " LEFT JOIN sparkly_formas f ON p.forma = f.id_forma ";
    }
    
    if ($audums_table_exists && $audums_id_exists) {
        $query .= " LEFT JOIN sparkly_audums a ON p.audums_id = a.id_audums ";
    }
    
    if ($malu_figura_table_exists && $figura_id_exists) {
        $query .= " LEFT JOIN sparkly_malu_figura m ON p.figura_id = m.id_malu_figura ";
    }
    
    if ($dekorejums1_table_exists && $dekorejums1_id_exists) {
        $query .= " LEFT JOIN sparkly_dekorejums1 d1 ON p.dekorejums1_id = d1.id_dekorejums1 ";
    }

    $query .= " WHERE p.id_bumba = ?";

    $stmt = $savienojums->prepare($query);
    if (isset($stmt)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if (isset($result) && $result->num_rows > 0) {
            $bumba = $result->fetch_assoc();
            $product_found = true;
            
            // Prepare image sources
            if (isset($bumba['attels1'])) {
                $attels1Src = 'data:image/jpeg;base64,' . base64_encode($bumba['attels1']);
            }
            if (isset($bumba['attels2'])) {
                $attels2Src = 'data:image/jpeg;base64,' . base64_encode($bumba['attels2']);
            }
            if (isset($bumba['attels3'])) {
                $attels3Src = 'data:image/jpeg;base64,' . base64_encode($bumba['attels3']);
            }
            
            // Determine correct display values based on schema
            $forma_display = isset($bumba['forma_name']) ? $bumba['forma_name'] : (isset($bumba['forma']) ? $bumba['forma'] : 'Nav norādīts');
            
            $audums_display = isset($bumba['audums_name']) ? $bumba['audums_name'] : 
                             (isset($bumba['audums']) ? $bumba['audums'] : 
                             (isset($bumba['audums_id']) ? $bumba['audums_id'] : 'Nav norādīts'));
            
            $malu_figura_display = isset($bumba['malu_figura_name']) ? $bumba['malu_figura_name'] : 
                                  (isset($bumba['malu_figura']) ? $bumba['malu_figura'] : 
                                  (isset($bumba['figura_id']) ? $bumba['figura_id'] : 'Nav norādīts'));
            
            $dekorejums1_display = isset($bumba['dekorejums1_name']) ? $bumba['dekorejums1_name'] : 
                                  (isset($bumba['dekorejums']) ? $bumba['dekorejums'] : 
                                  (isset($bumba['dekorejums1_id']) ? $bumba['dekorejums1_id'] : 'Nav norādīts'));
        }
    }
}
?>