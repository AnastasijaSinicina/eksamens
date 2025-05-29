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
>>>>>>> 1a021a866269243fba74051c1e39ad0d4862fad7
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
<<<<<<< HEAD
                    <p class="stat-number"><?php echo number_format($stats['spec_pas']); ?></p>
                    <a href="spec.pas.php" class="stat-link">Skatīt visus →</a>
=======
                    <p class="stat-number"><?php echo number_format($stats['clients']); ?></p>
                    <a href="klienti.php" class="stat-link">Skatīt visus →</a>
>>>>>>> 1a021a866269243fba74051c1e39ad0d4862fad7
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
<<<<<<< HEAD
                    <?php foreach (['Iesniegts', 'Apstiprināts', 'Nosūtīts', 'Saņemts', 'Atcelts'] as $status): ?>
=======
                    <?php foreach (['Iesniegts', 'Apstiprināts', 'Nosūtīts', 'Saņemts'] as $status): ?>
>>>>>>> 1a021a866269243fba74051c1e39ad0d4862fad7
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
<<<<<<< HEAD
                    <a href="produkcija.php" class="quick-action-btn add-product">
                        <i class="fas fa-plus"></i>
                        <span>Pievienot produktu</span>
                    </a>
                    <a href="klienti.php" class="quick-action-btn add-client">
=======
                    <a href="produkcija.php?action=add" class="quick-action-btn add-product">
                        <i class="fas fa-plus"></i>
                        <span>Pievienot produktu</span>
                    </a>
                    <a href="klienti.php?action=add" class="quick-action-btn add-client">
>>>>>>> 1a021a866269243fba74051c1e39ad0d4862fad7
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
<<<<<<< HEAD
=======

<style>
/* Dashboard-specific styles */
.dashboard-main {
    background: var(--gradient);
    min-height: 100vh;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem 0;
}

.dashboard-header h1 {
    color: var(--tumsa);
    font-size: 2.5rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.dashboard-subtitle {
    color: var(--text);
    font-size: 1.2rem;
    margin: 0;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--box-shadow);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow2);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-icon.products { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.orders { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-icon.clients { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-icon.revenue { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

.stat-content h3 {
    color: var(--tumsa);
    font-size: 1rem;
    margin: 0 0 0.5rem 0;
    font-weight: 500;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--maincolor);
    margin: 0 0 0.5rem 0;
}

.stat-link {
    color: var(--maincolor);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.stat-link:hover {
    color: var(--tumsa);
}

/* Dashboard Content Grid */
.dashboard-content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: var(--box-shadow);
}

.dashboard-card h2 {
    color: var(--tumsa);
    font-size: 1.3rem;
    margin: 0 0 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--light3);
}

/* Revenue Summary */
.revenue-summary {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.revenue-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--light3);
    border-radius: 0.5rem;
}

.revenue-label {
    color: var(--text);
    font-weight: 500;
}

.revenue-value {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--maincolor);
}

.revenue-value.total {
    font-size: 1.5rem;
    color: var(--tumsa);
}

/* Status Overview */
.status-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.status-item {
    text-align: center;
    padding: 1rem;
    background: var(--light3);
    border-radius: 0.5rem;
}

.status-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 1rem;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.status-badge.iesniegts { background-color: #e3f2fd; color: #1976d2; box-shadow: var(--box-shadow);}
.status-badge.apstiprināts { background-color: #fff8e1; color: #ffa000; box-shadow: var(--box-shadow);}
.status-badge.nosūtīts { background-color: #e8f5e9; color: #388e3c; box-shadow: var(--box-shadow);}
.status-badge.saņemts { background-color: #d1c4e9; color: #5e35b1; box-shadow: var(--box-shadow);}

.status-count {
    display: block;
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--maincolor);
}

/* Recent Orders */
.recent-orders {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item {
    padding: 1rem;
    background: var(--light3);
    border-radius: 0.5rem;
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 1rem;
    align-items: center;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.order-client {
    color: var(--text);
    font-size: 0.9rem;
}

.order-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
}

.order-amount {
    font-weight: bold;
    color: var(--maincolor);
}

.order-date {
    color: var(--text);
    font-size: 0.9rem;
    text-align: right;
}

.view-all-link {
    display: inline-block;
    margin-top: 1rem;
    color: var(--maincolor);
    text-decoration: none;
    font-weight: 500;
}

.view-all-link:hover {
    color: var(--tumsa);
}

.no-data {
    text-align: center;
    color: var(--text);
    padding: 2rem;
    font-style: italic;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem 1rem;
    background: var(--light3);
    border-radius: 0.8rem;
    text-decoration: none;
    color: var(--tumsa);
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    background: var(--maincolor);
    color: white;
    transform: translateY(-3px);
}

.quick-action-btn i {
    font-size: 1.5rem;
}

.quick-action-btn span {
    font-size: 0.9rem;
    font-weight: 500;
    text-align: center;
}

/* System Information */
.system-info {
    grid-column: 1 / -1;
}

.system-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.system-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1rem;
    background: var(--light3);
    border-radius: 0.5rem;
}

.system-label {
    color: var(--text);
    font-size: 0.9rem;
}

.system-value {
    font-weight: bold;
    color: var(--maincolor);
}

.role-badge {
    background: var(--maincolor);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 1rem;
    font-size: 0.9rem;
    display: inline-block;
    width: fit-content;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header h1 {
        font-size: 2rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .dashboard-subtitle {
        font-size: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .dashboard-content-grid {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .order-details {
        align-items: center;
    }
    
    .quick-actions {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    }
    
    .system-details {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .admin-content {
        padding: 1rem;
    }
    
    .dashboard-card {
        padding: 1rem;
    }
    
    .status-overview {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
>>>>>>> 1a021a866269243fba74051c1e39ad0d4862fad7
