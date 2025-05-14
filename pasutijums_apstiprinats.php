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
                <?php if ($order['piegades_veids'] == 'Kurjers'): ?>
                    <p><?= htmlspecialchars($order['adrese']) ?></p>
                    <p><?= htmlspecialchars($order['pilseta'] . ', ' . $order['pasta_indeks']) ?></p>
                <?php endif; ?>
                <p>Tel: <?= htmlspecialchars($order['talrunis']) ?></p>
                <p>E-pasts: <?= htmlspecialchars($order['epasts']) ?></p>
                

                <?php if ($order['piegades_veids'] == 'Pats'): ?>
                    <div class="pickup-info">
                        <h4>Izņemšanas vieta:</h4>
                        <p><b>Mēs paziņosim, kad jūsu pasūtījums būs gatavs saņemšanai!</b></p>
                        <p>Mūsu veikals: Lielā iela 14-11, Liepāja</p>
                        <p>Darba laiks: P-Pk 10:00-16:00, S 10:00-14:00</p>
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
<?php
// Include footer
include 'footer.php';
?>