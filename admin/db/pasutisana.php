<?php
// Pārbauda vai sesija ir sākta
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pievieno datubāzes savienojumu
require "con_db.php";

// Pārbauda vai lietotājs ir ielogojies
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai pabeigtu pasūtījumu";
    $_SESSION['redirect_after_login'] = "pasutisana.php";
    header("Location: ../../login.php");
    exit();
}

$username = $_SESSION['lietotajvardsSIN'];

// Iegūst lietotāja datus
if (isset($username)) {
    $user_query = "SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $user_stmt = $savienojums->prepare($user_query);
    $user_stmt->bind_param("s", $username);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
}

// Pārbauda groza saturu
if (isset($username)) {
    $cart_count_query = "SELECT COUNT(*) as count FROM grozs_sparkly WHERE lietotajvards = ? AND statuss = 'aktīvs'";
    $cart_count_stmt = $savienojums->prepare($cart_count_query);
    $cart_count_stmt->bind_param("s", $username);
    $cart_count_stmt->execute();
    $cart_count_result = $cart_count_stmt->get_result();
    $cart_count = $cart_count_result->fetch_assoc()['count'];
    
    // Ja grozs ir tukšs, pārvirza uz groza lapu
    if ($cart_count == 0) {
        $_SESSION['pazinojums'] = "Jūsu grozs ir tukšs";
        header("Location: ../../grozs.php");
        exit();
    }
}

// Ģenerē unikālu pasūtījuma numuru
if (isset($_POST['generate_order_number'])) {
    $max_attempts = 12;
    $attempts = 0;
    $unique_number_found = false;
    
    do {
        // Ģenerē 12 ciparu nejauša skaitļa (100000000000 līdz 999999999999)
        $order_number = rand(100000000000, 999999999999);
        
        // Pārbauda vai šis numurs jau eksistē
        $check_query = "SELECT COUNT(*) as count FROM sparkly_pasutijumi WHERE pasutijuma_numurs = ?";
        $check_stmt = $savienojums->prepare($check_query);
        $check_stmt->bind_param("i", $order_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->fetch_assoc()['count'] > 0;
        
        $attempts++;
        
        if (!$exists) {
            $unique_number_found = true;
            $unique_order_number = $order_number;
        }
        
    } while (!$unique_number_found && $attempts < $max_attempts);
    
    // Ja neizdevās ģenerēt unikālu numuru
    if (!$unique_number_found) {
        $error_message = "Neizdevās ģenerēt unikālu pasūtījuma numuru pēc $max_attempts mēģinājumiem";
    }
}

// Apstrādā pasūtījuma iesniegšanu
if (isset($_POST['submit_order'])) {
    error_log("Pasūtījuma iesniegšana sākta");
    error_log("POST dati: " . print_r($_POST, true));
    
    // Sagatavo un attīra ievadītos datus
    $vards = htmlspecialchars($_POST['vards']);
    $uzvards = htmlspecialchars($_POST['uzvards']);
    $epasts = htmlspecialchars($_POST['epasts']);
    $telefons = htmlspecialchars($_POST['telefons']);
    $piezimes = htmlspecialchars($_POST['piezimes']);
    


    // Iegūst groza preces
    if (isset($username)) {
        $items_query = "SELECT g.*, p.nosaukums, p.cena 
                      FROM grozs_sparkly g 
                      JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
                      WHERE g.lietotajvards = ? AND g.statuss = 'aktīvs'";
        $items_stmt = $savienojums->prepare($items_query);
        $items_stmt->bind_param("s", $username);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $total = 0;
        $product_count = 0;
        $cart_items = [];
        
        // Aprēķina kopējo summu un produktu skaitu
        while ($item = $items_result->fetch_assoc()) {
            $cart_items[] = $item;
            $total += $item['cena'] * $item['daudzums'];
            $product_count += $item['daudzums'];
        }
    }
    
    $status = 'Iesniegts';
    
    // Sāk transakiju, lai nodrošinātu datu integritāti
    if (isset($savienojums)) {
        $savienojums->autocommit(FALSE);
        $transaction_success = true;
        
        // Ģenerē unikālu pasūtījuma numuru
        $max_attempts = 12;
        $attempts = 0;
        $unique_number_found = false;
        
        do {
            $order_number = rand(100000000000, 999999999999);
            
            $check_query = "SELECT COUNT(*) as count FROM sparkly_pasutijumi WHERE pasutijuma_numurs = ?";
            $check_stmt = $savienojums->prepare($check_query);
            $check_stmt->bind_param("i", $order_number);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $exists = $check_result->fetch_assoc()['count'] > 0;
            
            $attempts++;
            
            if (!$exists) {
                $unique_number_found = true;
                $unique_order_number = $order_number;
            }
            
        } while (!$unique_number_found && $attempts < $max_attempts);
        
        if (isset($unique_order_number)) {
            error_log("Ģenerēts unikāls pasūtījuma numurs: " . $unique_order_number);
        }
        
        // Ievieto pasūtījumu datubāzē
        if (isset($unique_order_number) && isset($user)) {
            $insert_order_query = "INSERT INTO sparkly_pasutijumi 
                                  (lietotajs_id, pasutijuma_numurs, kopeja_cena, produktu_skaits, vards, uzvards, epasts, talrunis, statuss) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insert_order_stmt = $savienojums->prepare($insert_order_query);
            $insert_order_stmt->bind_param("iidisssss", 
                $user['id_lietotajs'],
                $unique_order_number,
                $total,
                $product_count,
                $vards,
                $uzvards,
                $epasts,
                $telefons,
                $status
            );
            
            if ($insert_order_stmt->execute()) {
                $pasutijums_id = $savienojums->insert_id;
                error_log("Pasūtījums izveidots ar ID: " . $pasutijums_id);
            } else {
                $transaction_success = false;
                error_log("Kļūda ievietojot pasūtījumu: " . $insert_order_stmt->error);
            }
        }
        
        // Ievieto pasūtījuma preces
        if (isset($pasutijums_id) && isset($cart_items) && $transaction_success) {
            foreach ($cart_items as $item) {
                $insert_items_query = "INSERT INTO sparkly_pasutijuma_vienumi 
                                      (pasutijuma_id, produkta_id, daudzums_no_groza, cena) 
                                      VALUES (?, ?, ?, ?)";
                
                $insert_items_stmt = $savienojums->prepare($insert_items_query);
                $insert_items_stmt->bind_param("iiid", 
                    $pasutijums_id, 
                    $item['bumba_id'], 
                    $item['daudzums'], 
                    $item['cena']
                );
                
                if (!$insert_items_stmt->execute()) {
                    $transaction_success = false;
                    error_log("Kļūda ievietojot pasūtījuma preci: " . $insert_items_stmt->error);
                    break;
                }
            }
        }
        
        // Atjaunina groza statusu
        if (isset($username) && $transaction_success) {
            $update_cart_query = "UPDATE grozs_sparkly SET statuss = 'pasūtīts' WHERE lietotajvards = ? AND statuss = 'aktīvs'";
            $update_cart_stmt = $savienojums->prepare($update_cart_query);
            $update_cart_stmt->bind_param("s", $username);
            
            if (!$update_cart_stmt->execute()) {
                $transaction_success = false;
                error_log("Kļūda atjauninot grozu: " . $update_cart_stmt->error);
            }
        }
        
        // Palielina lietotāja pasūtījumu skaitu
        if (isset($user) && $transaction_success) {
            $update_order_count_query = "UPDATE lietotaji_sparkly SET pas_skaits = pas_skaits + 1 WHERE id_lietotajs = ?";
            $update_order_count_stmt = $savienojums->prepare($update_order_count_query);
            $update_order_count_stmt->bind_param("i", $user['id_lietotajs']);
            
            if ($update_order_count_stmt->execute()) {
                error_log("Lietotāja pasūtījumu skaits palielināts lietotāja ID: " . $user['id_lietotajs']);
            } else {
                $transaction_success = false;
                error_log("Kļūda atjauninot lietotāja pasūtījumu skaitu: " . $update_order_count_stmt->error);
            }
        }
        
        // Apstiprina vai atsauc transakiju
        if ($transaction_success) {
            $savienojums->commit();
            $savienojums->autocommit(TRUE);
            
            $_SESSION['pazinojums'] = "Pasūtījums veiksmīgi noformēts!";
            error_log("Pārvirza uz apstiprinājuma lapu");
            
            if (isset($pasutijums_id)) {
                header("Location: pasutijums_apstiprinats.php?id=" . $pasutijums_id);
                exit();
            }
        } else {
            // Atsauc transakiju kļūdas gadījumā
            $savienojums->rollback();
            $savienojums->autocommit(TRUE);
            
            error_log("Kļūda veidojot pasūtījumu");
            $error_message = "Kļūda veidojot pasūtījumu. Lūdzu, mēģiniet vēlreiz.";
        }
    }
}

// Iegūst groza preces attēlošanai
if (isset($username)) {
    $display_items_query = "SELECT g.*, p.nosaukums, p.cena, p.attels1 
                          FROM grozs_sparkly g 
                          JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
                          WHERE g.lietotajvards = ? AND g.statuss = 'aktīvs'";
    $display_items_stmt = $savienojums->prepare($display_items_query);
    $display_items_stmt->bind_param("s", $username);
    $display_items_stmt->execute();
    $display_items_result = $display_items_stmt->get_result();

    $cart_items_display = [];
    $subtotal = 0;

    // Sagatavo datus attēlošanai
    while ($item = $display_items_result->fetch_assoc()) {
        $cart_items_display[] = $item;
        $subtotal += $item['cena'] * $item['daudzums'];
    }
}
?>