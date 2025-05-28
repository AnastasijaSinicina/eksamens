<?php
/**
 * Database functions for product operations
 */

/**
 * Get product by ID with all related information
 * @param mysqli $savienojums Database connection
 * @param int $id Product ID
 * @return array|null Product data or null if not found
 */
function getProduktById($savienojums, $id) {
    // Check if tables and columns exist
    $schema_info = checkDatabaseSchema($savienojums);
    
    // Construct query based on available schema
    $query = buildProductQuery($schema_info);
    
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $bumba = $result->fetch_assoc();
        
        // Process display values based on schema
        $bumba = processDisplayValues($bumba, $schema_info);
        
        return $bumba;
    }
    
    return null;
}

/**
 * Check database schema for existing tables and columns
 * @param mysqli $savienojums Database connection
 * @return array Schema information
 */
function checkDatabaseSchema($savienojums) {
    $schema_info = [];
    
    // Check if tables exist
    $tables_to_check = [
        'sparkly_formas',
        'sparkly_audums',
        'sparkly_malu_figura',
        'sparkly_dekorejums1',
        'sparkly_dekorejums2'
    ];
    
    foreach ($tables_to_check as $table) {
        $check_sql = "SHOW TABLES LIKE '$table'";
        $schema_info[$table . '_exists'] = $savienojums->query($check_sql)->num_rows > 0;
    }
    
    // Check if columns exist in main table
    $columns_to_check = [
        'audums_id',
        'figura_id',
        'dekorejums1_id',
        'dekorejums2_id'
    ];
    
    foreach ($columns_to_check as $column) {
        $check_sql = "SHOW COLUMNS FROM produkcija_sprarkly LIKE '$column'";
        $schema_info[$column . '_exists'] = $savienojums->query($check_sql)->num_rows > 0;
    }
    
    return $schema_info;
}

/**
 * Build SQL query based on available schema
 * @param array $schema_info Schema information
 * @return string SQL query
 */
function buildProductQuery($schema_info) {
    $query = "SELECT p.* ";
    
    // Add columns from related tables if they exist
    if ($schema_info['sparkly_formas_exists']) {
        $query .= ", f.forma AS forma_name ";
    }
    
    if ($schema_info['sparkly_audums_exists'] && $schema_info['audums_id_exists']) {
        $query .= ", a.nosaukums AS audums_name ";
    }
    
    if ($schema_info['sparkly_malu_figura_exists'] && $schema_info['figura_id_exists']) {
        $query .= ", m.nosaukums AS malu_figura_name ";
    }
    
    if ($schema_info['sparkly_dekorejums1_exists'] && $schema_info['dekorejums1_id_exists']) {
        $query .= ", d1.nosaukums AS dekorejums1_name ";
    }
    
    if ($schema_info['sparkly_dekorejums2_exists'] && $schema_info['dekorejums2_id_exists']) {
        $query .= ", d2.nosaukums AS dekorejums2_name ";
    }
    
    $query .= " FROM produkcija_sprarkly p ";
    
    // Add JOINs based on available tables
    if ($schema_info['sparkly_formas_exists']) {
        $query .= " LEFT JOIN sparkly_formas f ON p.forma = f.id_forma ";
    }
    
    if ($schema_info['sparkly_audums_exists'] && $schema_info['audums_id_exists']) {
        $query .= " LEFT JOIN sparkly_audums a ON p.audums_id = a.id_audums ";
    }
    
    if ($schema_info['sparkly_malu_figura_exists'] && $schema_info['figura_id_exists']) {
        $query .= " LEFT JOIN sparkly_malu_figura m ON p.figura_id = m.id_malu_figura ";
    }
    
    if ($schema_info['sparkly_dekorejums1_exists'] && $schema_info['dekorejums1_id_exists']) {
        $query .= " LEFT JOIN sparkly_dekorejums1 d1 ON p.dekorejums1_id = d1.id_dekorejums1 ";
    }
    
    if ($schema_info['sparkly_dekorejums2_exists'] && $schema_info['dekorejums2_id_exists']) {
        $query .= " LEFT JOIN sparkly_dekorejums2 d2 ON p.dekorejums2_id = d2.id_dekorejums2 ";
    }
    
    $query .= " WHERE p.id_bumba = ?";
    
    return $query;
}

/**
 * Process display values based on available data
 * @param array $bumba Product data
 * @param array $schema_info Schema information
 * @return array Processed product data
 */
function processDisplayValues($bumba, $schema_info) {
    // Determine correct display values based on schema
    $bumba['forma_display'] = isset($bumba['forma_name']) ? $bumba['forma_name'] : $bumba['forma'];
    
    $bumba['audums_display'] = isset($bumba['audums_name']) ? $bumba['audums_name'] : 
                              (isset($bumba['audums']) ? $bumba['audums'] : 
                              (isset($bumba['audums_id']) ? $bumba['audums_id'] : 'Nav norādīts'));
    
    $bumba['malu_figura_display'] = isset($bumba['malu_figura_name']) ? $bumba['malu_figura_name'] : 
                                   (isset($bumba['malu_figura']) ? $bumba['malu_figura'] : 
                                   (isset($bumba['figura_id']) ? $bumba['figura_id'] : 'Nav norādīts'));
    
    $bumba['dekorejums1_display'] = isset($bumba['dekorejums1_name']) ? $bumba['dekorejums1_name'] : 
                                   (isset($bumba['dekorejums']) ? $bumba['dekorejums'] : 
                                   (isset($bumba['dekorejums1_id']) ? $bumba['dekorejums1_id'] : 'Nav norādīts'));
    
    $bumba['dekorejums2_display'] = isset($bumba['dekorejums2_name']) ? $bumba['dekorejums2_name'] : 
                                   (isset($bumba['dekorejums2']) ? $bumba['dekorejums2'] : 
                                   (isset($bumba['dekorejums2_id']) ? $bumba['dekorejums2_id'] : 'Nav norādīts'));
    
    return $bumba;
}

/**
 * Get all products (for potential future use)
 * @param mysqli $savienojums Database connection
 * @return array Products array
 */
function getAllProdukti($savienojums) {
    $schema_info = checkDatabaseSchema($savienojums);
    $query = buildProductQuery($schema_info);
    
    // Remove the WHERE clause for getting all products
    $query = str_replace(' WHERE p.id_bumba = ?', '', $query);
    
    $result = $savienojums->query($query);
    $produkti = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $produkti[] = processDisplayValues($row, $schema_info);
        }
    }
    
    return $produkti;
}

/**
 * Search products by name (for potential future use)
 * @param mysqli $savienojums Database connection
 * @param string $search_term Search term
 * @return array Products array
 */
function searchProdukti($savienojums, $search_term) {
    $schema_info = checkDatabaseSchema($savienojums);
    $query = buildProductQuery($schema_info);
    
    // Replace WHERE clause with search condition
    $query = str_replace(' WHERE p.id_bumba = ?', ' WHERE p.nosaukums LIKE ?', $query);
    
    $stmt = $savienojums->prepare($query);
    $search_param = '%' . $search_term . '%';
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $produkti = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $produkti[] = processDisplayValues($row, $schema_info);
        }
    }
    
    return $produkti;
}
?>