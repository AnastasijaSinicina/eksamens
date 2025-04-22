<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
<<<<<<< HEAD
// Inside header.php
$cartCount = 0;
if (isset($_SESSION['lietotajvardsSIN'])) {
    // Include database connection
    require_once "admin/db/con_db.php";
    
    // Get user's cart count - only count active items with quantity > 0
    $username = $_SESSION['lietotajvardsSIN'];
    $query = "SELECT SUM(daudzums) as total FROM grozs_sparkly 
              WHERE lietotajvards = ? AND statuss = 'active' AND daudzums > 0";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $cartCount = intval($row['total'] ?: 0);
    }
    
    // Close the statement
    $stmt->close();
}

// Make sure $cartCount is a valid number
$cartCount = $cartCount ?: 0;

=======
>>>>>>> 2693dfa7b12716cdcc4ed99fa269d70868694183
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/style.css">
    <script src="./assets/script_animate.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/css/splide.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/js/splide.min.js"></script>
    <script src="./assets/script.js" defer></script>
    <link rel="shortcut icon" href="images/bumba.png" type="image/x-icon">
    <title>Sparkly Dream</title>
</head>
<body>
        <!-- Notification Container -->
        <div class="notification-container" id="notification-container">
        <div class="notification" id="notification">
            <i class="fa-solid fa-circle-check success" id="notification-icon"></i>
            <h3 id="notification-title">Paziņojums</h3>
            <p id="notification-message"></p>
        </div>
    </div>

    <header>
        <div class="logo1">
            <a href="index.php"><img src="images/logo_balts.png" alt=""></a>
        </div>
        <div class="logo2">
            <img src="images/bumba.png" alt="">       
    </div>
    <nav class="nav">
        <a href="index.php">Sākums</a>
        <a href="jaunumi.php">Jaunumi</a>
        <a href="produkcija.php">Produkcija</a>
        <a href="materiali.php">Izveido pats</a>
        <a href="atsauksmes.php">Atsauksmes</a>
        <a href="parMums.php">Par mums</a>
        <a href="kontakti.php">Kontakti</a>
    </nav>
<<<<<<< HEAD
    <a href="grozs.php" class="btn navBtn cart-btn">
    <i class="fa-solid fa-cart-shopping"></i>
    <?php if ($cartCount > 0): ?>
    <span class="cart-count"><?php echo $cartCount; ?></span>
    <?php endif; ?>
</a>
=======
    <a href="grozs.php" class="btn navBtn"><i class="fa-solid fa-cart-shopping"></i></a>
>>>>>>> 2693dfa7b12716cdcc4ed99fa269d70868694183
    
    <?php if(isset($_SESSION['lietotajvardsSIN'])): ?>
        <a href="profils.php" class="btn navBtn user-btn">
            <i class="fas fa-user"></i>
            <span class="username"><?php echo $_SESSION['lietotajvardsSIN']; ?></span>
        </a>
        <a href="logout.php" class="btn navBtn"><i class="fa-solid fa-right-from-bracket"></i></a>
    <?php else: ?>
        <a href="login.php" class="btn navBtn">
            <i class="fas fa-user"></i>
        </a>
    <?php endif; ?>
    
<<<<<<< HEAD
    <button id="menu-btn" class="btn navBtn">
    <i class="fa-solid fa-bars"></i>
</button>
=======
    <!-- <div id="menu-btn" class="fas fa-bars"></div> -->
>>>>>>> 2693dfa7b12716cdcc4ed99fa269d70868694183
</header>