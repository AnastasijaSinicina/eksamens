<?php
// Start or resume session
session_start();

// Check if user is logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    // Store custom order info in session to add after login
    $_SESSION['pending_custom_order'] = $_POST;
    
    // Set redirect URL to come back to materials page
    $_SESSION['redirect_after_login'] = 'materiali.php';
    
    // Set message
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai pasūtītu rotājumu";
    
    // Redirect to login
    header("Location: login.php");
    exit();
}

// Include database connection
require "admin/db/con_db.php";

// Get form data
$forma_id = isset($_POST['forma_id']) ? $_POST['forma_id'] : '';
$audums_id = isset($_POST['audums_id']) ? $_POST['audums_id'] : '';
$figura_id = isset($_POST['figura_id']) ? $_POST['figura_id'] : '';
$dekorejums1_id = isset($_POST['dekorejums1_id']) ? $_POST['dekorejums1_id'] : '';
$dekorejums2_id = isset($_POST['dekorejums2_id']) ? $_POST['dekorejums2_id'] : '';
$papildu_info = isset($_POST['papildu_info']) ? $_POST['papildu_info'] : '';

// Validate required fields
if (empty($forma_id) || empty($audums_id)) {
    $_SESSION['pazinojums'] = "Lūdzu, izvēlieties vismaz formu un audumu";
    header("Location: materiali.php");
    exit();
}

// Get the current user's lietotajvards
$lietotajvards = $_SESSION['lietotajvardsSIN'];

// Create a custom product name
$nosaukums_unikals = "Individuāls rotājums";

// Get details from individual elements to create a apraksts
$apraksts = "Individuāli pasūtīts rotājums:\n";

// Get forma name
if (!empty($forma_id)) {
    $query = "SELECT forma FROM sparkly_formas WHERE id_forma = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("i", $forma_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $apraksts .= "Forma: " . $row['forma'] . "\n";
    }
}

// Get audums name
if (!empty($audums_id)) {
    $query = "SELECT nosaukums FROM sparkly_audums WHERE id_audums = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("i", $audums_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $apraksts .= "Audums: " . $row['nosaukums'] . "\n";
    }
}

// Get figura name if selected
if (!empty($figura_id)) {
    $query = "SELECT nosaukums FROM sparkly_malu_figura WHERE id_malu_figura = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("i", $figura_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $apraksts .= "Mālu figūra: " . $row['nosaukums'] . "\n";
    }
}

// Get dekorejums1 name if selected
if (!empty($dekorejums1_id)) {
    $query = "SELECT nosaukums FROM sparkly_dekorejums1 WHERE id_dekorejums1 = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("i", $dekorejums1_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $apraksts .= "Dekorējums 1: " . $row['nosaukums'] . "\n";
    }
}

// Get dekorejums2 name if selected
if (!empty($dekorejums2_id)) {
    $query = "SELECT nosaukums FROM sparkly_dekorejums2 WHERE id_dekorejums2 = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("i", $dekorejums2_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $apraksts .= "Dekorējums 2: " . $row['nosaukums'] . "\n";
    }
}

// Add papildu_info if provided
if (!empty($papildu_info)) {
    $apraksts .= "Papildu norādes: " . $papildu_info;
}

// Set base cena for custom ornament
$cena = 10.00; // Base cena for custom ornament

// Add extra cena for optional elements
if (!empty($figura_id)) $cena += 2.00;
if (!empty($dekorejums1_id)) $cena += 1.50;
if (!empty($dekorejums2_id)) $cena += 1.50;

// Insert custom product into custom_products table (create this table if needed)
// Check if the custom_products table exists
$check_table_sql = "SHOW TABLES LIKE 'sparkly_sava_rotala'";
$table_exists = $savienojums->query($check_table_sql)->num_rows > 0;

// Create the table if it doesn't exist
if (!$table_exists) {
    $create_table_sql = "
    CREATE TABLE sparkly_sava_rotala (
        id_custom INT AUTO_INCREMENT PRIMARY KEY,
        lietotajvards VARCHAR(255) NOT NULL,
        nosaukums VARCHAR(255) NOT NULL,
        apraksts TEXT,
        forma_id INT,
        audums_id INT,
        figura_id INT NULL,
        dekorejums1_id INT NULL,
        dekorejums2_id INT NULL,
        cena DECIMAL(10,2) NOT NULL,
        datums TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $savienojums->query($create_table_sql);
}

// Insert the custom product
$insert_sql = "INSERT INTO sparkly_sava_rotala 
    (lietotajvards, nosaukums, apraksts, forma_id, audums_id, figura_id, dekorejums1_id, dekorejums2_id, cena) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $savienojums->prepare($insert_sql);
$stmt->bind_param("sssiiiiid", $lietotajvards, $nosaukums_unikals, $apraksts, $forma_id, $audums_id, $figura_id, $dekorejums1_id, $dekorejums2_id, $cena);
$stmt->execute();

// Get the ID of the inserted custom product
$custom_product_id = $savienojums->insert_id;

// Add the custom product to the user's cart
// Check if grozs_sparkly table exists
$check_table_sql = "SHOW TABLES LIKE 'grozs_sparkly'";
$table_exists = $savienojums->query($check_table_sql)->num_rows > 0;

// Create the cart table if it doesn't exist
if (!$table_exists) {
    $create_table_sql = "
    CREATE TABLE grozs_sparkly (
        id_grozs INT AUTO_INCREMENT PRIMARY KEY,
        lietotajvards VARCHAR(255) NOT NULL,
        bumba_id INT NULL,
        sava_rotala_id INT NULL,
        daudzums INT NOT NULL DEFAULT 1,
        statuss VARCHAR(50) NOT NULL DEFAULT 'active',
        datums TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sava_rotala_id) REFERENCES sparkly_sava_rotala(id_custom) ON DELETE SET NULL
    )";
    $savienojums->query($create_table_sql);
}

// Insert into cart
$cart_sql = "INSERT INTO grozs_sparkly (lietotajvards, sava_rotala_id, daudzums, statuss) VALUES (?, ?, 1, 'active')";
$stmt = $savienojums->prepare($cart_sql);
$stmt->bind_param("si", $lietotajvards, $custom_product_id);
$stmt->execute();

// Close statement and connection
$stmt->close();
$savienojums->close();

// Set success message
$_SESSION['pazinojums'] = "Jūsu individuāli pasūtītais rotājums ir pievienots grozam!";

// Redirect to cart
header("Location: grozs.php");
exit();
?>