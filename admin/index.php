<?php
session_start();
if (!isset($_SESSION['lietotajvardsSIN']) || ($_SESSION['loma'] !== 'admin' && $_SESSION['loma'] !== 'moder')) {
    header("Location: ../login.php");
    exit();
}

require 'header.php';
require 'db/kopsavilkums.php';


// Get dashboard statistics
try {
    // Total counts
    $stats = [];
    
    // Product counts
    $product_count = $savienojums->query("SELECT COUNT(*) as count FROM produkcija_sprarkly")->fetch_assoc()['count'];
    $stats['products'] = $product_count;
    
    // Order counts
    $order_count = $savienojums->query("SELECT COUNT(*) as count FROM sparkly_pasutijumi")->fetch_assoc()['count'];
    $stats['orders'] = $order_count;
    
    // Client counts
    $client_count = $savienojums->query("SELECT COUNT(*) as count FROM lietotaji_sparkly WHERE loma = 'klients'")->fetch_assoc()['count'];
    $stats['clients'] = $client_count;
    
    // Order statuses
    $status_query = "SELECT statuss, COUNT(*) as count FROM sparkly_pasutijumi GROUP BY statuss";
    $status_result = $savienojums->query($status_query);
    $order_statuses = [];
    while ($row = $status_result->fetch_assoc()) {
        $order_statuses[$row['statuss']] = $row['count'];
    }
    
    // Recent orders (last 5)
    $recent_orders_query = "SELECT p.*, l.lietotajvards 
                           FROM sparkly_pasutijumi p 
                           JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs 
                           ORDER BY p.pas_datums DESC 
                           LIMIT 5";
    $recent_orders = $savienojums->query($recent_orders_query);
    
    // Revenue calculations
    $today_revenue = $savienojums->query("SELECT COALESCE(SUM(kopeja_cena), 0) as revenue FROM sparkly_pasutijumi WHERE DATE(pas_datums) = CURDATE()")->fetch_assoc()['revenue'];
    $month_revenue = $savienojums->query("SELECT COALESCE(SUM(kopeja_cena), 0) as revenue FROM sparkly_pasutijumi WHERE MONTH(pas_datums) = MONTH(CURDATE()) AND YEAR(pas_datums) = YEAR(CURDATE())")->fetch_assoc()['revenue'];
    $total_revenue = $savienojums->query("SELECT COALESCE(SUM(kopeja_cena), 0) as revenue FROM sparkly_pasutijumi")->fetch_assoc()['revenue'];
    
    // Low stock alerts (products with less than 5 items - if you have stock tracking)
    // Placeholder for future implementation
    $low_stock_count = 0;
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = ['products' => 0, 'orders' => 0, 'clients' => 0];
    $order_statuses = [];
    $recent_orders = null;
    $today_revenue = $month_revenue = $total_revenue = 0;
}
?>

<main class="dashboard-main">
    <section class="admin-content">
        <div class="dashboard-header">
            <h1>
                <i class="fas fa-tachometer-alt"></i>
                Administrācijas pārvaldības panelis
            </h1>
            <p class="dashboard-subtitle">
                Sveicināts, <strong><?php echo htmlspecialchars($_SESSION['lietotajvardsSIN']); ?></strong>! 
                Šodien ir <?php echo date('d.m.Y'); ?>
            </p>
        </div>

        <!-- Quick Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon products">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <h3>Produkti</h3>
                    <p class="stat-number"><?php echo number_format($stats['products']); ?></p>
                    <a href="produkcija.php" class="stat-link">Skatīt visus →</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-content">
                    <h3>Pasūtījumi</h3>
                    <p class="stat-number"><?php echo number_format($stats['orders']); ?></p>
                    <a href="pasutijumi.php" class="stat-link">Pārvaldīt →</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon clients">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Klienti</h3>
                    <p class="stat-number"><?php echo number_format($stats['clients']); ?></p>
                    <a href="klienti.php" class="stat-link">Skatīt visus →</a>

                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>Kopējie ieņēmumi</h3>
                    <p class="stat-number">€<?php echo number_format($total_revenue, 2); ?></p>
                    <small>Šomēnes: €<?php echo number_format($month_revenue, 2); ?></small>
                </div>
            </div>
        </div>

        <!-- Revenue and Order Status Overview -->
        <div class="dashboard-content-grid">
            <div class="dashboard-card">
                <h2><i class="fas fa-chart-line"></i> Ieņēmumu pārskats</h2>
                <div class="revenue-summary">
                    <div class="revenue-item">
                        <span class="revenue-label">Šodienas ieņēmumi:</span>
                        <span class="revenue-value">€<?php echo number_format($today_revenue, 2); ?></span>
                    </div>
                    <div class="revenue-item">
                        <span class="revenue-label">Šī mēneša ieņēmumi:</span>
                        <span class="revenue-value">€<?php echo number_format($month_revenue, 2); ?></span>
                    </div>
                    <div class="revenue-item">
                        <span class="revenue-label">Kopējie ieņēmumi:</span>
                        <span class="revenue-value total">€<?php echo number_format($total_revenue, 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <h2><i class="fas fa-chart-pie"></i> Pasūtījumu statusu sadalījums</h2>
                <div class="status-overview">
                    <?php foreach (['Iesniegts', 'Apstiprināts', 'Nosūtīts', 'Saņemts', 'Atcelts'] as $status): ?>

                    <div class="status-item">
                        <span class="status-badge <?php echo strtolower($status); ?>">
                            <?php echo $status; ?>
                        </span>
                        <span class="status-count">
                            <?php echo isset($order_statuses[$status]) ? $order_statuses[$status] : 0; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Orders and Quick Actions -->
        <div class="dashboard-content-grid">
            <div class="dashboard-card">
                <h2><i class="fas fa-clock"></i> Jaunākie pasūtījumi</h2>
                <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                <div class="recent-orders">
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <div class="order-item">
                        <div class="order-info">
                            <strong>#<?php echo $order['id_pasutijums']; ?></strong>
                            <span class="order-client"><?php echo htmlspecialchars($order['lietotajvards']); ?></span>
                        </div>
                        <div class="order-details">
                            <span class="order-amount">€<?php echo number_format($order['kopeja_cena'], 2); ?></span>
                            <span class="status-badge <?php echo strtolower($order['statuss']); ?>">
                                <?php echo $order['statuss']; ?>
                            </span>
                        </div>
                        <div class="order-date">
                            <?php echo date('d.m.Y H:i', strtotime($order['pas_datums'])); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <a href="pasutijumi.php" class="view-all-link">Skatīt visus pasūtījumus →</a>
                <?php else: ?>
                <p class="no-data">Nav atrasts neviens pasūtījums</p>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h2><i class="fas fa-bolt"></i> Ātras darbības</h2>
                <div class="quick-actions">
            <a href="produkcija.php?action=add" class="quick-action-btn add-product">
                        <i class="fas fa-plus"></i>
                        <span>Pievienot produktu</span>
                    </a>
                    <a href="klienti.php?action=add" class="quick-action-btn add-client">

                        <i class="fas fa-user-plus"></i>
                        <span>Pievienot klientu</span>
                    </a>
                    <a href="pasutijumi.php" class="quick-action-btn manage-orders">
                        <i class="fas fa-list-ul"></i>
                        <span>Pārvaldīt pasūtījumus</span>
                    </a>
                    <a href="audums.php" class="quick-action-btn manage-fabric">
                        <i class="fas fa-palette"></i>
                        <span>Pārvaldīt audumu</span>
                    </a>
                    <a href="formas.php" class="quick-action-btn manage-shapes">
                        <i class="fas fa-shapes"></i>
                        <span>Pārvaldīt formas</span>
                    </a>
                    <a href="figuras.php" class="quick-action-btn manage-figures">
                        <i class="fas fa-star"></i>
                        <span>Pārvaldīt figūras</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="dashboard-card system-info">
            <h2><i class="fas fa-info-circle"></i> Sistēmas informācija</h2>
            <div class="system-details">
                <div class="system-item">
                    <span class="system-label">Pēdējā darbībā:</span>
                    <span class="system-value"><?php echo date('d.m.Y H:i'); ?></span>
                </div>
                <div class="system-item">
                    <span class="system-label">Jūsu loma:</span>
                    <span class="system-value role-badge"><?php echo ucfirst($_SESSION['loma']); ?></span>
                </div>
            </div>
        </div>
    </section>
</main>