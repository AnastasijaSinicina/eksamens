<?php
require_once 'con_db.php';


$sql = "SELECT p.*, ";


$check_formas_table_sql = "SHOW TABLES LIKE 'sparkly_formas'";
$formas_table_exists = $savienojums->query($check_formas_table_sql)->num_rows > 0;

$check_audumi_table_sql = "SHOW TABLES LIKE 'sparkly_audums'";
$audumi_table_exists = $savienojums->query($check_audumi_table_sql)->num_rows > 0;

$check_malu_figura_table_sql = "SHOW TABLES LIKE 'sparkly_malu_figura'";
$malu_figura_table_exists = $savienojums->query($check_malu_figura_table_sql)->num_rows > 0;

$check_dekorejums1_table_sql = "SHOW TABLES LIKE 'sparkly_dekorejums1'";
$dekorejums1_table_exists = $savienojums->query($check_dekorejums1_table_sql)->num_rows > 0;

$check_dekorejums2_table_sql = "SHOW TABLES LIKE 'sparkly_dekorejums2'";
$dekorejums2_table_exists = $savienojums->query($check_dekorejums2_table_sql)->num_rows > 0;

if ($formas_table_exists) {
    $sql .= "f.forma AS forma_name, ";
} else {
    $sql .= "p.forma AS forma_name, ";
}

if ($audumi_table_exists) {
    $sql .= "a.nosaukums AS audums_name, ";
} else {
    $sql .= "p.audums_id AS audums_name, ";
}

if ($malu_figura_table_exists) {
    $sql .= "m.nosaukums AS malu_figura_name, ";
} else {
    $sql .= "p.figura_id AS malu_figura_name, ";
}

if ($dekorejums1_table_exists) {
    $sql .= "d1.nosaukums AS dekorejums1_name, ";
} else {
    $sql .= "p.dekorejums1_id AS dekorejums1_name, ";
}

if ($dekorejums2_table_exists) {
    $sql .= "d2.nosaukums AS dekorejums2_name, ";
} else {
    $sql .= "p.dekorejums2_id AS dekorejums2_name, ";
}

$sql .= "creator.lietotajvards as created_username, ";
$sql .= "creator.vards as created_first_name, ";
$sql .= "creator.uzvards as created_last_name, ";
$sql .= "editor.lietotajvards as updated_username, ";
$sql .= "editor.vards as updated_first_name, ";
$sql .= "editor.uzvards as updated_last_name, ";
$sql .= "p.izveidots_liet, p.timestamp as created_at, p.red_liet, p.red_dat as updated_at ";
$sql .= "FROM produkcija_sprarkly p ";

if ($formas_table_exists) {
    $sql .= "LEFT JOIN sparkly_formas f ON p.forma = f.id_forma ";
}

if ($audumi_table_exists) {
    $sql .= "LEFT JOIN sparkly_audums a ON p.audums_id = a.id_audums ";
}

if ($malu_figura_table_exists) {
    $sql .= "LEFT JOIN sparkly_malu_figura m ON p.figura_id = m.id_malu_figura ";
}

if ($dekorejums1_table_exists) {
    $sql .= "LEFT JOIN sparkly_dekorejums1 d1 ON p.dekorejums1_id = d1.id_dekorejums1 ";
}

if ($dekorejums2_table_exists) {
    $sql .= "LEFT JOIN sparkly_dekorejums2 d2 ON p.dekorejums2_id = d2.id_dekorejums2 ";
}

$sql .= "LEFT JOIN lietotaji_sparkly creator ON p.izveidots_liet = creator.id_lietotajs ";
$sql .= "LEFT JOIN lietotaji_sparkly editor ON p.red_liet = editor.id_lietotajs ";

$sql .= "ORDER BY p.id_bumba DESC";

$products_result = $savienojums->query($sql);

if (!$products_result) {
    echo "Query Error: " . $savienojums->error;
}

$formas_options = [];
if ($formas_table_exists) {
    $forma_sql = "SELECT * FROM sparkly_formas ORDER BY forma";
    $forma_result = $savienojums->query($forma_sql);
    if ($forma_result && $forma_result->num_rows > 0) {
        while ($forma_row = $forma_result->fetch_assoc()) {
            $formas_options[] = $forma_row;
        }
    }
}

$audums_options = [];
if ($audumi_table_exists) {
    $audums_sql = "SELECT * FROM sparkly_audums ORDER BY nosaukums";
    $audums_result = $savienojums->query($audums_sql);
    if ($audums_result && $audums_result->num_rows > 0) {
        while ($audums_row = $audums_result->fetch_assoc()) {
            $audums_options[] = $audums_row;
        }
    }
}

$malu_figura_options = [];
if ($malu_figura_table_exists) {
    $malu_figura_sql = "SELECT * FROM sparkly_malu_figura ORDER BY nosaukums";
    $malu_figura_result = $savienojums->query($malu_figura_sql);
    if ($malu_figura_result && $malu_figura_result->num_rows > 0) {
        while ($malu_figura_row = $malu_figura_result->fetch_assoc()) {
            $malu_figura_options[] = $malu_figura_row;
        }
    }
}

$dekorejums1_options = [];
if ($dekorejums1_table_exists) {
    $dekorejums1_sql = "SELECT * FROM sparkly_dekorejums1 ORDER BY nosaukums";
    $dekorejums1_result = $savienojums->query($dekorejums1_sql);
    if ($dekorejums1_result && $dekorejums1_result->num_rows > 0) {
        while ($dekorejums1_row = $dekorejums1_result->fetch_assoc()) {
            $dekorejums1_options[] = $dekorejums1_row;
        }
    }
}

$dekorejums2_options = [];
if ($dekorejums2_table_exists) {
    $dekorejums2_sql = "SELECT * FROM sparkly_dekorejums2 ORDER BY nosaukums";
    $dekorejums2_result = $savienojums->query($dekorejums2_sql);
    if ($dekorejums2_result && $dekorejums2_result->num_rows > 0) {
        while ($dekorejums2_row = $dekorejums2_result->fetch_assoc()) {
            $dekorejums2_options[] = $dekorejums2_row;
        }
    }
}

if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $product_sql = "SELECT p.*, 
                    creator.lietotajvards as created_username,
                    creator.vards as created_first_name,
                    creator.uzvards as created_last_name,
                    editor.lietotajvards as updated_username,
                    editor.vards as updated_first_name,
                    editor.uzvards as updated_last_name,
                    p.izveidots_liet as created_by, 
                    p.timestamp as created_at, 
                    p.red_liet as updated_by, 
                    p.red_dat as updated_at 
                    FROM produkcija_sprarkly p
                    LEFT JOIN lietotaji_sparkly creator ON p.izveidots_liet = creator.id_lietotajs
                    LEFT JOIN lietotaji_sparkly editor ON p.red_liet = editor.id_lietotajs
                    WHERE p.id_bumba = ?";
    $product_stmt = $savienojums->prepare($product_sql);
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    $single_product = ($product_result->num_rows > 0) ? $product_result->fetch_assoc() : null;
    $product_stmt->close();
}
?>