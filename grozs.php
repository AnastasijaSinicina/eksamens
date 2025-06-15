<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    // Set message
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai piekļūtu grozam";
    
    // Redirect to login
    header("Location: login.php");
    exit();
}

$username = $_SESSION['lietotajvardsSIN'];

require "admin/db/grozs.php";

include 'header.php';
?>

<section id="grozs">
    <h1>Mans grozs</h1>
    
    <?php if (isset($_SESSION['pazinojums'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('<?= addslashes($_SESSION['pazinojums']) ?>', 'success');
        });
    </script>
    <?php unset($_SESSION['pazinojums']); ?>
    <?php endif; ?>
    
    <?php if (!$has_items): ?>
        <div class="empty-cart">
            <i class="fa-solid fa-cart-shopping empty-cart-icon"></i>
            <h2>Jūsu grozs ir tukšs</h2>
            <p>Pievienojiet preces savam grozam, lai veiktu pasūtījumu</p>
            <a href="produkcija.php" class="btn">Turpināt iepirkties</a>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php
                $totalPrice = 0;
                foreach ($cart_items as $item) {
                    $itemTotal = $item['cena'] * $item['daudzums'];
                    $totalPrice += $itemTotal;
                    ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="data:image/jpeg;base64,<?= base64_encode($item['attels1']) ?>" alt="<?= htmlspecialchars($item['nosaukums']) ?>">
                        </div>
                        
                        <div class="cart-item-details">
                            <h3><?= htmlspecialchars($item['nosaukums']) ?></h3>
                            <p><?= number_format($item['cena'], 2) ?>€</p>
                        </div>
                        
                        <div class="cart-item-quantity">
                            <form action="admin/db/update_grozs.php" method="post" class="quantity-form">
                                <input type="hidden" name="id" value="<?= $item['id_grozs'] ?>">
                                <button type="submit" name="decrease" class="quantity-btn">-</button>
                                <span><?= $item['daudzums'] ?></span>
                                <button type="submit" name="increase" class="quantity-btn">+</button>
                            </form>
                        </div>
                        
                        <div class="cart-item-total">
                            <p><?= number_format($itemTotal, 2) ?>€</p>
                        </div>
                        
                        <div class="cart-item-remove">
                            <form action="admin/db/update_grozs.php" method="post">
                                <input type="hidden" name="id" value="<?= $item['id_grozs'] ?>">
                                <button type="submit" name="remove" class="remove-btn">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
            <div class="cart-summary">
                <h2>Kopējā summa</h2>
                <div class="summary-row">
                    <span>Kopā:</span>
                    <span><?= number_format($totalPrice, 2) ?>€</span>
                </div>
                <a href="pasutisana.php" class="btn full">Pasūtīt</a>
                <a href="produkcija.php" class="btn full">Turpināt iepirkties</a>
                
                <form action="admin/db/update_grozs.php" method="post">
                    <input type="hidden" name="user" value="<?= $username ?>">
                    <button type="submit" name="clear" class="btn clear-btn">Iztīrīt grozu</button>
                </form>
            </div>
            
        </div>
    <?php endif; ?>
</section>

<?php
// Close database connection
$savienojums->close();

// Include footer
include 'footer.php';
?>