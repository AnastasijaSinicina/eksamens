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

if (isset($_POST['submit_order'])) {
    $vards = htmlspecialchars($_POST['vards']);
    $uzvards = htmlspecialchars($_POST['uzvards']);
    $epasts = htmlspecialchars($_POST['epasts']);
    $telefons = htmlspecialchars($_POST['telefons']);
    $adrese = htmlspecialchars($_POST['adrese']);
    $pilseta = htmlspecialchars($_POST['pilseta']);
    $pasta_indekss = htmlspecialchars($_POST['pasta_indekss']);
    $apmaksas_veids = htmlspecialchars($_POST['apmaksas_veids']);
    $piegades_veids = htmlspecialchars($_POST['piegades_veids']); 
    $piezimes = htmlspecialchars($_POST['piezimes']);
    

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
    
    $insert_order = $savienojums->prepare("INSERT INTO sparkly_pasutijumi (lietotajs_id, kopeja_cena, apmaksas_veids, piegades_veids, produktu_skaits, vards, uzvards, epasts, talrunis, pilseta, adrese, pasta_indeks, statuss) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_order->bind_param("idssissssssss", 
        $user['id_lietotajs'],
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
        $update_cart->execute();
        
        $_SESSION['pazinojums'] = "Pasūtījums veiksmīgi noformēts!";
        header("Location: pasutijums_apstiprinats.php?id=" . $pasutijums_id);
        exit();
    } else {
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
                    <input type="text" id="pasta_indekss" name="pasta_indekss" value="<?= htmlspecialchars($user['pasta_indeks'] ?? '') ?>" required>
                </div>
                
                <h2>Maksājuma metode</h2>
                
                <div class="form-group radio-group">
                    <label class="radio-container">
                        <input type="radio" name="apmaksas_veids" value="Bankas karte" checked>
                        <span class="radio-label">Bankas karte</span>
                    </label>
                    
                    <label class="radio-container">
                        <input type="radio" name="apmaksas_veids" value="Skaidra nauda">
                        <span class="radio-label">Skaidra nauda</span>
                    </label>
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
                
                <div class="form-group">
                    <label for="piezimes">Piezīmes par pasūtījumu</label>
                    <textarea id="piezimes" name="piezimes" rows="4"></textarea>
                </div>
                
                <button type="submit" name="submit_order" class="btn full">Apstiprināt pasūtījumu</button>
            </form>
        </div>
    </div>
</section>

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
    border-radius: 0.75rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 1.5rem;
    height: fit-content;
}

.checkout-form {
    flex: 1 1 60%;
    background-color: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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

.total-row span:last-child {
    font-weight: 600;
    color: var(--tumsa);
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
include 'footer.php';
?>