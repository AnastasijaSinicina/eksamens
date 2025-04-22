<?php
    // Start session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['lietotajvardsSIN'])) {
        // Set message and redirect to login
        $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai pabeigtu pasūtījumu";
        $_SESSION['redirect_after_login'] = "pasutisana.php";
        header("Location: login.php");
        exit();
    }

    // Include database connection
    require "admin/db/con_db.php";

    // Get current user's username and information
    $username = $_SESSION['lietotajvardsSIN'];
    $query = "SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();

    // Check if cart is empty
    $cart_query = "SELECT COUNT(*) as count FROM grozs_sparkly WHERE lietotajvards = ? AND statuss = 'active'";
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

    // Process form submission
    if (isset($_POST['submit_order'])) {
        // Get form data
        $vards = htmlspecialchars($_POST['vards']);
        $uzvards = htmlspecialchars($_POST['uzvards']);
        $epasts = htmlspecialchars($_POST['epasts']);
        $telefons = htmlspecialchars($_POST['telefons']);
        $adrese = htmlspecialchars($_POST['adrese']);
        $pilseta = htmlspecialchars($_POST['pilseta']);
        $pasta_indekss = htmlspecialchars($_POST['pasta_indekss']);
        $piegades_veids = htmlspecialchars($_POST['piegades_veids']);
        $maksajuma_veids = htmlspecialchars($_POST['maksajuma_veids']);
        $piezimes = htmlspecialchars($_POST['piezimes']);
        
        // Update user information if needed
        $update_user = $savienojums->prepare("UPDATE lietotaji_sparkly SET vards = ?, uzvards = ?, epasts = ?, telefons = ?, adrese = ? WHERE lietotajvards = ?");
        $update_user->bind_param("ssssss", $vards, $uzvards, $epasts, $telefons, $adrese, $username);
        $update_user->execute();
        
        // Get cart total
        $total_query = "SELECT SUM(g.daudzums * p.cena) as total 
                        FROM grozs_sparkly g 
                        JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
                        WHERE g.lietotajvards = ? AND g.statuss = 'active'";
        $total_stmt = $savienojums->prepare($total_query);
        $total_stmt->bind_param("s", $username);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $total = $total_result->fetch_assoc()['total'];
        
        // Calculate shipping cost
        $piegades_cena = ($piegades_veids == 'pasts') ? 3.99 : 2.99;
        $kopeja_summa = $total + $piegades_cena;
        
        // Create new order
        $statuss = 'jauns';
        $datums = date('Y-m-d H:i:s');
        
        $insert_order = $savienojums->prepare("INSERT INTO pasutijumi (lietotajs_id, vards, uzvards, epasts, telefons, adrese, pilseta, pasta_indekss, piegades_veids, maksajuma_veids, piezimes, summa, piegades_cena, kopeja_summa, statuss, datums) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_order->bind_param("issssssssssdddss", $user['id_lietotajs'], $vards, $uzvards, $epasts, $telefons, $adrese, $pilseta, $pasta_indekss, $piegades_veids, $maksajuma_veids, $piezimes, $total, $piegades_cena, $kopeja_summa, $statuss, $datums);
        
        if ($insert_order->execute()) {
            $pasutijums_id = $savienojums->insert_id;
            
            // Add order items
            $items_query = "SELECT g.*, p.nosaukums, p.cena 
                          FROM grozs_sparkly g 
                          JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
                          WHERE g.lietotajvards = ? AND g.statuss = 'active'";
            $items_stmt = $savienojums->prepare($items_query);
            $items_stmt->bind_param("s", $username);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();
            
            while ($item = $items_result->fetch_assoc()) {
                $insert_item = $savienojums->prepare("INSERT INTO pasutijuma_preces (pasutijums_id, produkts_id, nosaukums, cena, daudzums) VALUES (?, ?, ?, ?, ?)");
                $insert_item->bind_param("iisdi", $pasutijums_id, $item['bumba_id'], $item['nosaukums'], $item['cena'], $item['daudzums']);
                $insert_item->execute();
            }
            
            // Update cart status to 'ordered'
            $update_cart = $savienojums->prepare("UPDATE grozs_sparkly SET statuss = 'ordered' WHERE lietotajvards = ? AND statuss = 'active'");
            $update_cart->bind_param("s", $username);
            $update_cart->execute();
            
            // Success message and redirect
            $_SESSION['pazinojums'] = "Pasūtījums veiksmīgi noformēts!";
            header("Location: pasutijums_apstiprinats.php?id=" . $pasutijums_id);
            exit();
        } else {
            $error_message = "Kļūda veidojot pasūtījumu. Lūdzu, mēģiniet vēlreiz.";
        }
    }

    // Get cart items and total
    $items_query = "SELECT g.*, p.nosaukums, p.cena, p.attels1 
                  FROM grozs_sparkly g 
                  JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
                  WHERE g.lietotajvards = ? AND g.statuss = 'active'";
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
    
    // Include header
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
        <!-- Order Summary Column -->
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
                    <span>Starpsumma:</span>
                    <span><?= number_format($subtotal, 2) ?>€</span>
                </div>
                <div class="total-row">
                    <span>Piegāde:</span>
                    <span id="shipping-cost">2.99€</span>
                </div>
                <div class="total-row grand-total">
                    <span>Kopā:</span>
                    <span id="grand-total"><?= number_format($subtotal + 2.99, 2) ?>€</span>
                </div>
            </div>
            
            <a href="grozs.php" class="btn">Atgriezties grozā</a>
        </div>
        
        <!-- Checkout Form Column -->
        <div class="checkout-form">
            <h2>Piegādes informācija</h2>
            
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
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
                
                <div class="form-group">
                    <label for="adrese">Adrese*</label>
                    <input type="text" id="adrese" name="adrese" value="<?= htmlspecialchars($user['adrese'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="pilseta">Pilsēta*</label>
                    <input type="text" id="pilseta" name="pilseta" value="<?= htmlspecialchars($user['pilseta'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="pasta_indekss">Pasta indekss*</label>
                    <input type="text" id="pasta_indekss" name="pasta_indekss" value="<?= htmlspecialchars($user['pasta_indekss'] ?? '') ?>" required>
                </div>
                
                <h2>Piegādes metode</h2>
                
                <div class="form-group radio-group">
                    <label class="radio-container">
                        <input type="radio" name="piegades_veids" value="pasts" checked>
                        <span class="radio-label">Latvijas Pasts (3.99€)</span>
                    </label>
                    
                    <label class="radio-container">
                        <input type="radio" name="piegades_veids" value="pakomats">
                        <span class="radio-label">Pakomāts (2.99€)</span>
                    </label>
                </div>
                
                <h2>Maksājuma metode</h2>
                
                <div class="form-group radio-group">
                    <label class="radio-container">
                        <input type="radio" name="maksajuma_veids" value="bankas_parskaitijums" checked>
                        <span class="radio-label">Bankas pārskaitījums</span>
                    </label>
                    
                    <label class="radio-container">
                        <input type="radio" name="maksajuma_veids" value="apmaksa_sanemsana">
                        <span class="radio-label">Apmaksa saņemšanas brīdī</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="piezimes">Piezīmes par pasūtījumu</label>
                    <textarea id="piezimes" name="piezimes" rows="4"></textarea>
                </div>
                
                <button type="submit" name="submit_order" class="btn checkout-btn">Apstiprināt pasūtījumu</button>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update shipping cost and total based on selected shipping method
    const shippingRadios = document.querySelectorAll('input[name="piegades_veids"]');
    const shippingCostElement = document.getElementById('shipping-cost');
    const grandTotalElement = document.getElementById('grand-total');
    const subtotal = <?= $subtotal ?>;
    
    shippingRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            let shippingCost = this.value === 'pasts' ? 3.99 : 2.99;
            shippingCostElement.textContent = shippingCost.toFixed(2) + '€';
            
            let grandTotal = subtotal + shippingCost;
            grandTotalElement.textContent = grandTotal.toFixed(2) + '€';
        });
    });
});
</script>

<style>
#pasutisana {
    padding: 4rem 6%;
}

#pasutisana h1 {
    text-align: center;
    margin-bottom: 2rem;
}

.error-message {
    background-color: #ffebee;
    color: #d32f2f;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
    text-align: center;
}

.checkout-container {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    margin-bottom: 2rem;
}

.order-summary {
    flex: 1 1 30%;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    height: fit-content;
}

.checkout-form {
    flex: 1 1 60%;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
}

.order-summary h2,
.checkout-form h2 {
    color: var(--tumsa);
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    text-align: left;
    border-bottom: 1px solid var(--light3);
    padding-bottom: 0.5rem;
}

.summary-items {
    max-height: 400px;
    overflow-y: auto;
    margin-bottom: 1.5rem;
    padding-right: 0.5rem;
}

.summary-item {
    display: flex;
    padding: 0.8rem 0;
    border-bottom: 1px solid var(--light3);
}

.summary-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 5rem;
    margin-right: 1rem;
}

.item-image img {
    width: 100%;
    border-radius: 0.3rem;
}

.item-details {
    flex: 1;
}

.item-details h3 {
    color: var(--tumsa);
    font-size: 1.1rem;
    margin-bottom: 0.3rem;
}

.item-details p {
    color: var(--text);
    font-size: 0.9rem;
    margin: 0.2rem 0;
}

.item-price {
    font-weight: bold;
    color: var(--tumsa);
}

.summary-totals {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid var(--light3);
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.8rem;
    font-size: 1.1rem;
}

.grand-total {
    font-weight: bold;
    font-size: 1.3rem;
    color: var(--tumsa);
    border-top: 1px solid var(--light3);
    padding-top: 0.8rem;
    margin-top: 0.8rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 500;
    color: var(--tumsa);
    margin-bottom: 0.5rem;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid var(--light3);
    border-radius: 0.5rem;
    font-size: 1rem;
    color: var(--text);
    background-color: white;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: var(--maincolor);
    box-shadow: 0 0 0.5rem rgba(3, 135, 206, 0.3);
    outline: none;
}

.radio-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.radio-container {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.radio-container input[type="radio"] {
    width: auto;
    margin-right: 0.8rem;
}

.radio-label {
    font-size: 1.1rem;
    color: var(--text);
}

.checkout-btn {
    width: 100%;
    padding: 1rem;
    font-size: 1.2rem;
    background-color: var(--tumsa);
    color: white;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 1rem;
}

.checkout-btn:hover {
    background-color: var(--maincolor);
}

@media (max-width: 992px) {
    .checkout-container {
        flex-direction: column;
    }
    
    .order-summary,
    .checkout-form {
        flex-basis: 100%;
    }
}
</style>

<?php
// Include footer
include 'footer.php';
?>