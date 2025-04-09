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

// Get current user's username
$username = $_SESSION['lietotajvardsSIN'];

// Include header
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
    
    <?php
    // Include database connection
    require "admin/db/con_db.php";
    
    // Get cart items from database
    $query = "SELECT g.*, p.nosaukums, p.cena, p.attels1 
              FROM grozs_sparkly g 
              JOIN produkcija_sprarkly p ON g.bumba_id = p.id_bumba 
              WHERE g.lietotajvards = ? AND g.statuss = 'active'";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Empty cart
        ?>
        <div class="empty-cart">
            <i class="fa-solid fa-cart-shopping empty-cart-icon"></i>
            <h2>Jūsu grozs ir tukšs</h2>
            <p>Pievienojiet preces savam grozam, lai veiktu pasūtījumu</p>
            <a href="produkcija.php" class="btn">Turpināt iepirkties</a>
        </div>
        <?php
    } else {
        // Display cart items
        $totalPrice = 0;
        ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php
                while ($item = $result->fetch_assoc()) {
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
                            <form action="admin/db/update_cart.php" method="post" class="quantity-form">
                                <input type="hidden" name="id" value="<?= $item['id_grozs'] ?>">
                                <button type="submit" name="increase" class="quantity-btn">+</button>
                                <span><?= $item['daudzums'] ?></span>
                                <button type="submit" name="decrease" class="quantity-btn">-</button>
                            </form>
                        </div>
                        
                        <div class="cart-item-total">
                            <p><?= number_format($itemTotal, 2) ?>€</p>
                        </div>
                        
                        <div class="cart-item-remove">
                            <form action="admin/db/update_cart.php" method="post">
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
                
                <a href="checkout.php" class="btn full">Pasūtīt</a>
                <a href="produkcija.php" class="btn">Turpināt iepirkties</a>
                
                <form action="admin/db/update_cart.php" method="post">
                    <input type="hidden" name="user" value="<?= $username ?>">
                    <button type="submit" name="clear" class="btn clear-btn">Iztīrīt grozu</button>
                </form>
            </div>
        </div>
    <?php
    }
    
    // Close the statement and connection
    $stmt->close();
    $savienojums->close();
    ?>
</section>

<?php
// Include footer
include 'footer.php';
?>