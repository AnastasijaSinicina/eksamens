<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    // Redirect to login
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = intval($_GET['id']);

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

// Get order information - UPDATED: Added piegades_veids
$order_query = "SELECT * FROM sparkly_pasutijumi WHERE id_pasutijums = ? AND lietotajs_id = ?";
$order_stmt = $savienojums->prepare($order_query);
$order_stmt->bind_param("ii", $order_id, $user['id_lietotajs']);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

// If order not found or doesn't belong to current user, redirect
if ($order_result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_query = "SELECT pv.*, p.attels1, p.nosaukums
               FROM sparkly_pasutijuma_vienumi pv
               JOIN produkcija_sprarkly p ON pv.produkta_id = p.id_bumba
               WHERE pv.pasutijuma_id = ?";
$items_stmt = $savienojums->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Include header
include 'header.php';
?>

<section id="pasutijums-apstiprinats">
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Pasūtījums pieņemts!</h1>
        <p>Jūsu pasūtījums Nr. <?= $order_id ?> ir veiksmīgi pieņemts.</p>
        
        <?php if (isset($_SESSION['pazinojums'])): ?>
            <div class="success-message">
                <p><?= $_SESSION['pazinojums'] ?></p>
            </div>
            <?php unset($_SESSION['pazinojums']); ?>
        <?php endif; ?>
        
        <div class="order-details">
            <h2>Pasūtījuma detaļas</h2>
            
            <div class="order-info">
                <div class="info-row">
                    <span>Pasūtījuma numurs:</span>
                    <span>#<?= $order_id ?></span>
                </div>
                <div class="info-row">
                    <span>Datums:</span>
                    <span><?= date('d.m.Y H:i', strtotime($order['pas_datums'])) ?></span>
                </div>
                <div class="info-row">
                    <span>Statuss:</span>
                    <span class="status <?= strtolower($order['statuss']) ?>"><?= $order['statuss'] ?></span>
                </div>
                <div class="info-row">
                    <span>Apmaksas veids:</span>
                    <span><?= $order['apmaksas_veids'] ?></span>
                </div>
                <!-- NEW: Display delivery method -->
                <div class="info-row">
                    <span>Piegādes veids:</span>
                    <span><?= $order['piegades_veids'] ?></span>
                </div>
                <div class="info-row">
                    <span>Kopējā summa:</span>
                    <span class="total-price"><?= number_format($order['kopeja_cena'], 2) ?>€</span>
                </div>
            </div>
            
            <h3>Pasūtītās preces</h3>
            
            <div class="order-items">
                <?php while ($item = $items_result->fetch_assoc()): ?>
                    <div class="order-item">
                        <div class="item-image">
                            <img src="data:image/jpeg;base64,<?= base64_encode($item['attels1']) ?>">
                        </div>
                        <div class="item-details">
                            <h4><?= htmlspecialchars($item['nosaukums']) ?></h4>
                            <p>Cena: <?= number_format($item['cena'], 2) ?>€</p>
                            <p>Daudzums: <?= $item['daudzums_no_groza'] ?></p>
                        </div>
                        <div class="item-total">
                            <p><?= number_format($item['cena'] * $item['daudzums_no_groza'], 2) ?>€</p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="shipping-info">
                <h3>Piegādes informācija</h3>
                <p><?= htmlspecialchars($order['vards'] . ' ' . $order['uzvards']) ?></p>
                <!-- Display address only if delivery method is 'Kurjers' -->
                <?php if ($order['piegades_veids'] == 'Kurjers'): ?>
                    <p><?= htmlspecialchars($order['adrese']) ?></p>
                    <p><?= htmlspecialchars($order['pilseta'] . ', ' . $order['pasta_indeks']) ?></p>
                <?php endif; ?>
                <p>Tel: <?= htmlspecialchars($order['talrunis']) ?></p>
                <p>E-pasts: <?= htmlspecialchars($order['epasts']) ?></p>
                
                <!-- Display pickup info if customer chose self-pickup -->
                <?php if ($order['piegades_veids'] == 'Pats'): ?>
                    <div class="pickup-info">
                        <h4>Izņemšanas vieta:</h4>
                        <p>Mūsu veikals: Brīvības iela 123, Rīga</p>
                        <p>Darba laiks: P-Pk 10:00-18:00, S 10:00-15:00</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="actions">
            <a href="index.php" class="btn">Atgriezties sākumlapā</a>
            <a href="produkcija.php" class="btn">Turpināt iepirkties</a>
        </div>
    </div>
</section>

<style>
#pasutijums-apstiprinats {
    padding: 4rem 6%;
}

.success-container {
    max-width: 800px;
    margin: 0 auto;
    background-color: white;
    border-radius: 1rem;
    box-shadow: var(--box-shadow);
    padding: 2rem;
    text-align: center;
}

.success-icon {
    font-size: 5rem;
    color: #28a745;
    margin-bottom: 1rem;
}

.success-container h1 {
    color: var(--tumsa);
    margin-bottom: 1rem;
}

.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 1rem;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

.order-details {
    text-align: left;
    margin-top: 2rem;
    border-top: 1px solid var(--light3);
    padding-top: 2rem;
}

.order-details h2, .order-details h3 {
    color: var(--tumsa);
    margin-bottom: 1.5rem;
}

.order-info {
    background-color: var(--light2);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.8rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid var(--light3);
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.status {
    font-weight: bold;
    padding: 0.3rem 0.8rem;
    border-radius: 1rem;
    font-size: 0.9rem;
}

.status.iesniegt {
    background-color: #e3f2fd;
    color: #1976d2;
}

.status.apstiprināts {
    background-color: #fff8e1;
    color: #ffa000;
}

.status.aizsūtīts {
    background-color: #e8f5e9;
    color: #388e3c;
}

.status.saņemts {
    background-color: #ffebee;
    color: #d32f2f;
}

.total-price {
    font-weight: bold;
    color: var(--tumsa);
    font-size: 1.2rem;
}

.order-items {
    margin-bottom: 2rem;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--light3);
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 5rem;
    height: 5rem;
    overflow: hidden;
    border-radius: 0.5rem;
    margin-right: 1.5rem;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    color: var(--tumsa);
    margin-bottom: 0.5rem;
}

.item-details p {
    color: var(--text);
    margin: 0.2rem 0;
}

.item-total {
    font-weight: bold;
    color: var(--tumsa);
    margin-left: 1rem;
}

.shipping-info {
    background-color: var(--light2);
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.shipping-info p {
    margin: 0.5rem 0;
    color: var(--text);
}

/* NEW: Styling for pickup information */
.pickup-info {
    margin-top: 1rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    border-left: 4px solid var(--maincolor);
}

.pickup-info h4 {
    color: var(--tumsa);
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.pickup-info p {
    margin: 0.3rem 0;
    color: var(--text);
}

.actions {
    margin-top: 3rem;
    display: flex;
    justify-content: center;
    gap: 1.5rem;
}

.actions .btn {
    min-width: 200px;
}

@media (max-width: 768px) {
    .actions {
        flex-direction: column;
        gap: 1rem;
    }
    
    .actions .btn {
        width: 100%;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
    }
    
    .item-image {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .item-total {
        margin-left: 0;
        margin-top: 1rem;
    }
}
</style>

<?php
// Include footer
include 'footer.php';
?>