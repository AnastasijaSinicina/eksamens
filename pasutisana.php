<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai pabeigtu pasūtījumu";
    $_SESSION['redirect_after_login'] = "pasutisana.php";
    header("Location: login.php");
    exit();
}
require "admin/db/con_db.php";

$username = $_SESSION['lietotajvardsSIN'];
$query = "SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?";
$stmt = $savienojums->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

$cart_query = "SELECT COUNT(*) as count FROM grozs_sparkly WHERE lietotajvards = ? AND statuss = 'aktīvs'";
$cart_stmt = $savienojums->prepare($cart_query);
$cart_stmt->bind_param("s", $username);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_count = $cart_result->fetch_assoc()['count'];

if ($cart_count == 0) {
    $_SESSION['pazinojums'] = "Jūsu grozs ir tukšs";
    header("Location: grozs.php");
    exit();
}

// Function to generate unique order number
function generateUniqueOrderNumber($connection) {
    $max_attempts = 12;
    $attempts = 0;
    
    do {
        // Generate 8-digit random number (10000000000 to 99999999999)
        $order_number = rand(100000000000, 999999999999);
        
        // Check if this number already exists
        $check_query = "SELECT COUNT(*) as count FROM sparkly_pasutijumi WHERE pasutijuma_numurs = ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("i", $order_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->fetch_assoc()['count'] > 0;
        
        $attempts++;
        
        if (!$exists) {
            return $order_number;
        }
        
    } while ($exists && $attempts < $max_attempts);
    
    // If we couldn't generate unique number after max attempts, throw error
    throw new Exception("Unable to generate unique order number after $max_attempts attempts");
}

if (isset($_POST['submit_order'])) {
    error_log("Order submission started");
    error_log("POST data: " . print_r($_POST, true));
    
    $vards = htmlspecialchars($_POST['vards']);
    $uzvards = htmlspecialchars($_POST['uzvards']);
    $epasts = htmlspecialchars($_POST['epasts']);
    $telefons = htmlspecialchars($_POST['telefons']);
    $adrese = htmlspecialchars($_POST['adrese']);
    $pilseta = htmlspecialchars($_POST['pilseta']);
    $pasta_indekss = htmlspecialchars($_POST['pasta_indekss']);
    $piegades_veids = htmlspecialchars($_POST['piegades_veids']); 
    $piezimes = htmlspecialchars($_POST['piezimes']);
    
    // Set payment method based on delivery method
    if ($piegades_veids == 'Pats') {
        $apmaksas_veids = htmlspecialchars($_POST['apmaksas_veids']);
    } else {
        $apmaksas_veids = 'Bankas karte';
    }
    
    error_log("Payment method set to: " . $apmaksas_veids);

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
    
    while ($item = $items_result->fetch_assoc()) {
        $cart_items[] = $item;
        $total += $item['cena'] * $item['daudzums'];
        $product_count += $item['daudzums'];
    }
    
    $status = 'Iesniegts';
    
    try {
        // Generate unique order number
        $unique_order_number = generateUniqueOrderNumber($savienojums);
        error_log("Generated unique order number: " . $unique_order_number);
        
        // Updated query to include pasutijuma_numurs
        $insert_order = $savienojums->prepare("INSERT INTO sparkly_pasutijumi (lietotajs_id, pasutijuma_numurs, kopeja_cena, apmaksas_veids, piegades_veids, produktu_skaits, vards, uzvards, epasts, talrunis, pilseta, adrese, pasta_indeks, statuss) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_order->bind_param("iidssissssssss", 
            $user['id_lietotajs'],
            $unique_order_number,
            $total,
            $apmaksas_veids,
            $piegades_veids,
            $product_count,
            $vards,
            $uzvards,
            $epasts,
            $telefons,
            $pilseta,
            $adrese,
            $pasta_indekss,
            $status
        );
        
        if ($insert_order->execute()) {
            $pasutijums_id = $savienojums->insert_id;
            error_log("Order created with ID: " . $pasutijums_id);

            foreach ($cart_items as $item) {
                
                $insert_items = $savienojums->prepare("INSERT INTO sparkly_pasutijuma_vienumi 
                                (pasutijuma_id, produkta_id, daudzums_no_groza, cena) 
                                VALUES (?, ?, ?, ?)");
                
                $insert_items->bind_param("iiid", 
                    $pasutijums_id, 
                    $item['bumba_id'], 
                    $item['daudzums'], 
                    $item['cena']
                );
                
                if (!$insert_items->execute()) {
                    error_log("SQL Error in order item insertion: " . $insert_items->error);
                    $error_message = "Kļūda veidojot pasūtījumu: " . $insert_items->error;
                }
            }
            
            $update_cart = $savienojums->prepare("UPDATE grozs_sparkly SET statuss = 'pasūtīts' WHERE lietotajvards = ? AND statuss = 'aktīvs'");
            $update_cart->bind_param("s", $username);
            
            if ($update_cart->execute()) {
                error_log("Cart updated to 'pasūtīts' status");
            } else {
                error_log("Error updating cart: " . $update_cart->error);
            }
            
            $_SESSION['pazinojums'] = "Pasūtījums veiksmīgi noformēts!";
            error_log("Redirecting to confirmation page");
            header("Location: pasutijums_apstiprinats.php?id=" . $pasutijums_id);
            exit();
        } else {
            error_log("Error inserting order: " . $insert_order->error);
            $error_message = "Kļūda veidojot pasūtījumu. Lūdzu, mēģiniet vēlreiz.";
        }
    } catch (Exception $e) {
        error_log("Error generating order number: " . $e->getMessage());
        $error_message = "Kļūda veidojot pasūtījumu. Lūdzu, mēģiniet vēlreiz.";
    }
}

$items_query = "SELECT g.*, p.nosaukums, p.cena, p.attels1 
              FROM grozs_sparkly g 
              JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
              WHERE g.lietotajvards = ? AND g.statuss = 'aktīvs'";
$items_stmt = $savienojums->prepare($items_query);
$items_stmt->bind_param("s", $username);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$cart_items = [];
$subtotal = 0;

while ($item = $items_result->fetch_assoc()) {
    $cart_items[] = $item;
    $subtotal += $item['cena'] * $item['daudzums'];
}

include 'header.php';
?>

<section id="pasutisana">
    <h1>Pasūtījuma noformēšana</h1>
    
    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>
    
    <div class="checkout-container">
        <div class="order-summary">
            <h2>Pasūtījuma kopsavilkums</h2>
            
            <div class="summary-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <div class="item-image">
                            <img src="data:image/jpeg;base64,<?= base64_encode($item['attels1']) ?>" alt="<?= htmlspecialchars($item['nosaukums']) ?>">
                        </div>
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['nosaukums']) ?></h3>
                            <p>Daudzums: <?= $item['daudzums'] ?></p>
                            <p class="item-price"><?= number_format($item['cena'], 2) ?>€</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="summary-totals">
                <div class="total-row">
                    <span>Kopā:</span>
                    <span><?= number_format($subtotal, 2) ?>€</span>
                </div>
            </div>
            
            <a href="grozs.php" class="btn">Atgriezties grozā</a>
        </div>

        <div class="checkout-form">
            <h2>Piegādes informācija</h2>
            
            <form id="checkout-form" method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                <div class="form-group">
                    <label for="vards">Vārds*</label>
                    <input type="text" id="vards" name="vards" value="<?= htmlspecialchars($user['vards'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="uzvards">Uzvārds*</label>
                    <input type="text" id="uzvards" name="uzvards" value="<?= htmlspecialchars($user['uzvards'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="epasts">E-pasts*</label>
                    <input type="email" id="epasts" name="epasts" value="<?= htmlspecialchars($user['epasts'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telefons">Telefons*</label>
                    <input type="text" id="telefons" name="telefons" value="<?= htmlspecialchars($user['telefons'] ?? '') ?>" required>
                </div>

                <h2>Piegāde</h2>
                <div class="form-group radio-group">
                    <label class="radio-container">
                        <input type="radio" name="piegades_veids" value="Kurjers" checked>
                        <span class="radio-label">Kurjers</span>
                    </label>
                    
                    <label class="radio-container">
                        <input type="radio" name="piegades_veids" value="Pats">
                        <span class="radio-label">Savākšu pats</span>
                    </label>
                </div>
                
                <div class="form-group" id="address-group">
                    <label for="adrese">Adrese*</label>
                    <input type="text" id="adrese" name="adrese" value="<?= htmlspecialchars($user['adrese'] ?? '') ?>" required>
                </div>
                
                <div class="form-group" id="city-group">
                    <label for="pilseta">Pilsēta*</label>
                    <input type="text" id="pilseta" name="pilseta" value="<?= htmlspecialchars($user['pilseta'] ?? '') ?>" required>
                </div>
                
                <div class="form-group" id="postal-group">
                    <label for="pasta_indekss">Pasta indekss*</label>
                    <input type="text" id="pasta_indekss" name="pasta_indekss" value="<?= htmlspecialchars($user['pasta_indeks'] ?? '') ?>" required>
                </div>
                
                
                <div class="form-group">
                    <label for="piezimes">Piezīmes par pasūtījumu</label>
                    <textarea id="piezimes" name="piezimes" rows="4"></textarea>
                </div>

                <div class="form-group" id="payment-method-display" style="display: none;">
                    <label for="selected-payment-method">Izvēlētais maksājuma veids</label>
                    <input type="text" id="selected-payment-method" value="" readonly class="readonly-field">
                </div>

                <input type="hidden" id="apmaksas-veids-input" name="apmaksas_veids" value="">
                
                <button type="button" id="payment-btn" class="btn full">Izvēlēties maksājuma veidu</button>
                <button type="submit" name="submit_order" id="confirm-order-btn" class="btn full" style="display: none;">Apstiprināt pasūtījumu</button>
            </form>
        </div>
    </div>


<!--Modālas logs maksājuma veidam-->
<div id="payment-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Izvēlieties maksājuma veidu</h2>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="payment-options">
                <label class="payment-option">
                    <input type="radio" name="modal_payment" value="Bankas karte" checked>
                    <span class="payment-label">
                        <i class="fas fa-credit-card"></i>
                        Bankas karte
                    </span>
                </label>
                
                <label class="payment-option">
                    <input type="radio" name="modal_payment" value="Skaidra nauda">
                    <span class="payment-label">
                        <i class="fas fa-money-bill"></i>
                        Skaidra nauda
                    </span>
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" id="cancel-payment" class="btn btn-secondary">Atcelt</button>
            <button type="button" id="confirm-payment" class="btn">Apstiprināt</button>
        </div>
    </div>
</div>
</section>
<script>

</script>

<?php
include 'footer.php';
?>