<?php
// Include admin header
require 'header.php';

// Database connection
require 'db/con_db.php';

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE sparkly_pasutijumi SET statuss = ? WHERE id_pasutijums = ?";
    $update_stmt = $savienojums->prepare($update_query);
    $update_stmt->bind_param("si", $new_status, $order_id);
    
    if ($update_stmt->execute()) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNotification('success', 'Veiksmīgi!', 'Pasūtījuma statuss ir atjaunināts.');
                });
              </script>";
    } else {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNotification('error', 'Kļūda!', 'Neizdevās atjaunināt pasūtījuma statusu.');
                });
              </script>";
    }
}

// Handle view order details
$view_order = null;
if (isset($_GET['view'])) {
    $order_id = $_GET['view'];
    
    // Get order information
    $order_query = "SELECT p.*, l.lietotajvards
                   FROM sparkly_pasutijumi p
                   JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs
                   WHERE p.id_pasutijums = ?";
    $order_stmt = $savienojums->prepare($order_query);
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    
    if ($order_result->num_rows > 0) {
        $view_order = $order_result->fetch_assoc();
        
        // Get order items
        $items_query = "SELECT i.*, p.attels1
                       FROM sparkly_pasutijuma_vienumi i
                       JOIN produkcija_sprarkly p ON i.bumba_id = p.id_bumba
                       WHERE i.pasutijums_id = ?";
        $items_stmt = $savienojums->prepare($items_query);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $order_items = [];
        while ($item = $items_result->fetch_assoc()) {
            $order_items[] = $item;
        }
        
        $view_order['items'] = $order_items;
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build the WHERE clause for filtering
$where_conditions = [];
$params = [];
$types = '';

if (!empty($status_filter)) {
    $where_conditions[] = "p.statuss = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $search_term = "%$search%";
    $where_conditions[] = "(p.id_pasutijums LIKE ? OR l.lietotajvards LIKE ? OR p.vards LIKE ? OR p.uzvards LIKE ? OR p.epasts LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sssss';
}

if (!empty($date_from)) {
    $where_conditions[] = "p.pas_datums >= ?";
    $params[] = $date_from . ' 00:00:00';
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "p.pas_datums <= ?";
    $params[] = $date_to . ' 23:59:59';
    $types .= 's';
}

// Build the final SQL query
$query = "SELECT p.*, l.lietotajvards
          FROM sparkly_pasutijumi p
          JOIN lietotaji_sparkly l ON p.lietotajs_id = l.id_lietotajs";

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY p.pas_datums DESC";

// Prepare and execute query
$stmt = $savienojums->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders_result = $stmt->get_result();
?>

<main>
    <!-- Notification container -->
    <div class="notification-container">
        <div class="notification">
            <i class="fas fa-check-circle success"></i>
            <h3>Veiksmīgi!</h3>
            <p>Darbība veiksmīgi izpildīta.</p>
        </div>
    </div>

    <section class="admin-content">
        <h1>Pasūtījumu pārvaldība</h1>
        
        <?php if ($view_order): ?>
            <!-- Order Details View -->
            <div class="order-details-container">
                <div class="back-link">
                <a href="pasutijumi.php" class="btn"><i class="fas fa-arrow-left"></i> Atgriezties pie pasūtījumu saraksta</a>
                </div>
                
                <div class="order-header">
                    <h2>Pasūtījums #<?= $view_order['id_pasutijums'] ?></h2>
                    <div class="order-status-section">
                        <span class="status-label">Pašreizējais statuss:</span>
                        <span class="status <?= strtolower($view_order['statuss']) ?>"><?= $view_order['statuss'] ?></span>
                        
                        <form method="post" action="pasutijumi.php" class="status-form">
                            <input type="hidden" name="order_id" value="<?= $view_order['id_pasutijums'] ?>">
                            <select name="new_status" class="status-select">
                                <option value="Iesniegt" <?= $view_order['statuss'] == 'Iesniegt' ? 'selected' : '' ?>>Iesniegt</option>
                                <option value="Apstiprināts" <?= $view_order['statuss'] == 'Apstiprināts' ? 'selected' : '' ?>>Apstiprināts</option>
                                <option value="Aizsūtīts" <?= $view_order['statuss'] == 'Aizsūtīts' ? 'selected' : '' ?>>Aizsūtīts</option>
                                <option value="Saņemts" <?= $view_order['statuss'] == 'Saņemts' ? 'selected' : '' ?>>Saņemts</option>
                            </select>
                            <button type="submit" name="update_status" class="btn">Atjaunināt statusu</button>
                        </form>
                    </div>
                </div>
                
                <div class="order-info-grid">
                    <div class="order-info-card">
                        <h3>Pasūtījuma informācija</h3>
                        <div class="info-row">
                            <span>Pasūtījuma datums:</span>
                            <span><?= date('d.m.Y H:i', strtotime($view_order['pas_datums'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span>Pasūtījuma statuss:</span>
                            <span class="status <?= strtolower($view_order['statuss']) ?>"><?= $view_order['statuss'] ?></span>
                        </div>
                        <div class="info-row">
                            <span>Apmaksas veids:</span>
                            <span><?= $view_order['apmaksas_veids'] ?></span>
                        </div>
                        <div class="info-row">
                            <span>Kopējā summa:</span>
                            <span class="total-price"><?= number_format($view_order['kopeja_cena'], 2) ?>€</span>
                        </div>
                        <div class="info-row">
                            <span>Produktu skaits:</span>
                            <span><?= $view_order['produktu_skaits'] ?></span>
                        </div>
                    </div>
                    
                    <div class="order-info-card">
                        <h3>Klienta informācija</h3>
                        <div class="info-row">
                            <span>Lietotājvārds:</span>
                            <span><?= htmlspecialchars($view_order['lietotajvards']) ?></span>
                        </div>
                        <div class="info-row">
                            <span>Vārds, uzvārds:</span>
                            <span><?= htmlspecialchars($view_order['vards'] . ' ' . $view_order['uzvards']) ?></span>
                        </div>
                        <div class="info-row">
                            <span>E-pasts:</span>
                            <span><?= htmlspecialchars($view_order['epasts']) ?></span>
                        </div>
                        <div class="info-row">
                            <span>Telefons:</span>
                            <span><?= htmlspecialchars($view_order['talrunis']) ?></span>
                        </div>
                    </div>
                    
                    <div class="order-info-card">
                        <h3>Piegādes informācija</h3>
                        <div class="info-row">
                            <span>Adrese:</span>
                            <span><?= htmlspecialchars($view_order['adrese']) ?></span>
                        </div>
                        <div class="info-row">
                            <span>Pilsēta:</span>
                            <span><?= htmlspecialchars($view_order['pilseta']) ?></span>
                        </div>
                        <div class="info-row">
                            <span>Pasta indekss:</span>
                            <span><?= htmlspecialchars($view_order['pasta_indeks']) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="order-items-section">
                    <h3>Pasūtītās preces</h3>
                    
                    <div class="table-responsive">
                        <table class="product-table order-items-table">
                            <thead>
                                <tr>
                                    <th>Attēls</th>
                                    <th>Nosaukums</th>
                                    <th>Cena</th>
                                    <th>Daudzums</th>
                                    <th>Kopā</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($view_order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <img src="data:image/jpeg;base64,<?= base64_encode($item['attels1']) ?>" alt="<?= htmlspecialchars($item['nosaukums']) ?>" width="50">
                                        </td>
                                        <td><?= htmlspecialchars($item['nosaukums']) ?></td>
                                        <td><?= number_format($item['cena'], 2) ?>€</td>
                                        <td><?= $item['daudzums'] ?></td>
                                        <td><?= number_format($item['cena'] * $item['daudzums'], 2) ?>€</td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="4" class="text-right"><strong>Kopā:</strong></td>
                                    <td><strong><?= number_format($view_order['kopeja_cena'], 2) ?>€</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="order-actions">
                    <a href="pasutijumi.php" class="btn"><i class="fas fa-arrow-left"></i> Atgriezties pie pasūtījumu saraksta</a>
                    <a href="#" onclick="printOrderDetails()" class="btn"><i class="fas fa-print"></i> Drukāt pasūtījumu</a>
                </div>
            </div>
            
            <script>
                function printOrderDetails() {
                    window.print();
                }
            </script>
            
        <?php else: ?>
            <!-- Orders List View -->
            <div class="filters-container">
                <form method="get" action="pasutijumi.php" class="filters-form">
                    <div class="filter-group">
                        <label for="status">Statuss:</label>
                        <select id="status" name="status">
                            <option value="">Visi statusi</option>
                            <option value="Iesniegt" <?= $status_filter == 'Iesniegt' ? 'selected' : '' ?>>Iesniegt</option>
                            <option value="Apstiprināts" <?= $status_filter == 'Apstiprināts' ? 'selected' : '' ?>>Apstiprināts</option>
                            <option value="Aizsūtīts" <?= $status_filter == 'Aizsūtīts' ? 'selected' : '' ?>>Aizsūtīts</option>
                            <option value="Saņemts" <?= $status_filter == 'Saņemts' ? 'selected' : '' ?>>Saņemts</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Meklēt:</label>
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Pasūtījuma #, klienta vārds vai e-pasts">
                    </div>
                    
                    <div class="filter-group date-range">
                        <label>Datuma diapazons:</label>
                        <div class="date-inputs">
                            <input type="date" name="date_from" value="<?= $date_from ?>">
                            <span>līdz</span>
                            <input type="date" name="date_to" value="<?= $date_to ?>">
                        </div>
                    </div>
                    
                    <div class="filter-buttons">
                        <button type="submit" class="btn"><i class="fas fa-filter"></i> Filtrēt</button>
                        <a href="pasutijumi.php" class="btn clear-btn"><i class="fas fa-times"></i> Notīrīt</a>
                    </div>
                </form>
            </div>
            
            <div class="orders-table-container">
                <div class="table-responsive">
                    <table class="product-table orders-table">
                        <thead>
                            <tr>
                                <th>Pasūtījuma #</th>
                                <th>Klients</th>
                                <th>Datums</th>
                                <th>Summa</th>
                                <th>Produktu skaits</th>
                                <th>Statuss</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders_result->num_rows > 0): ?>
                                <?php while ($order = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $order['id_pasutijums'] ?></td>
                                        <td>
                                            <div class="client-info">
                                                <div><?= htmlspecialchars($order['vards'] . ' ' . $order['uzvards']) ?></div>
                                                <small><?= htmlspecialchars($order['lietotajvards']) ?></small>
                                            </div>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($order['pas_datums'])) ?></td>
                                        <td><?= number_format($order['kopeja_cena'], 2) ?>€</td>
                                        <td><?= $order['produktu_skaits'] ?></td>
                                        <td>
                                            <span class="status <?= strtolower($order['statuss']) ?>"><?= $order['statuss'] ?></span>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="pasutijumi.php?view=<?= $order['id_pasutijums'] ?>" class="btn view-btn"><i class="fas fa-eye"></i> Skatīt</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="no-records">Nav atrasts neviens pasūtījums ar norādītajiem parametriem</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>

<style>
    .admin-content{
        padding-left: 20rem;
    }
/* Orders management styles */
.filters-container {
    background-color: white;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
}

.filters-form {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    align-items: flex-end;
}

.filter-group {
    flex: 1 1 200px;
}

.filter-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--tumsa);
}

.filter-group select,
.filter-group input {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--light3);
    border-radius: 0.5rem;
    font-size: 1rem;
}

.date-range {
    flex: 1 1 400px;
}

.date-inputs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.date-inputs input {
    flex: 1;
}

.date-inputs span {
    color: var(--tumsa);
    font-weight: 500;
}

.filter-buttons {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
}

.orders-table-container {
    background-color: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--box-shadow);
}

.orders-table th,
.orders-table td {
    padding: 1rem 0.8rem;
}

.client-info {
    display: flex;
    flex-direction: column;
}

.client-info small {
    color: #666;
    font-size: 0.8rem;
}

.status {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 1rem;
    font-size: 0.9rem;
    font-weight: 500;
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
    background-color: #d1c4e9;
    color: #5e35b1;
}

.view-btn {
    background-color: var(--maincolor);
    color: white;
}

.view-btn:hover {
    background-color: #0268a2;
}

/* Order details styles */
.order-details-container {
    background-color: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: var(--box-shadow);
}

.back-link {
    margin-bottom: 2rem;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--light3);
}

.order-header h2 {
    margin: 0;
    color: var(--tumsa);
}

.order-status-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.status-label {
    font-weight: 500;
    color: var(--tumsa);
}

.status-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-select {
    padding: 0.5rem;
    border: 1px solid var(--light3);
    border-radius: 0.3rem;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.order-info-card {
    background-color: var(--light2);
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.order-info-card h3 {
    color: var(--tumsa);
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid var(--light3);
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.8rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.info-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.info-row span:first-child {
    color: #666;
}

.info-row span:last-child {
    font-weight: 500;
    color: var(--tumsa);
}

.total-price {
    font-weight: bold;
    color: var(--tumsa);
}

.order-items-section {
    margin-bottom: 2rem;
}

.order-items-section h3 {
    color: var(--tumsa);
    margin-bottom: 1.5rem;
}

.order-items-table img {
    border-radius: 0.3rem;
    object-fit: cover;
}

.total-row {
    background-color: var(--light2);
}

.text-right {
    text-align: right;
}

.order-actions {
    display: flex;
    gap: 1rem;
    justify-content: space-between;
}

@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
        gap: 1rem;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .date-inputs {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-buttons {
        flex-direction: column;
        width: 100%;
    }
    
    .filter-buttons .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .order-status-section {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
    }
    
    .status-form {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
    }
    
    .status-select {
        margin-bottom: 0.5rem;
    }
    
    .order-actions {
        flex-direction: column;
    }
    
    .order-actions .btn {
        width: 100%;
    }
}

@media print {
    .sidebar, 
    .back-link,
    .order-status-section,
    .order-actions,
    .status-form {
        display: none !important;
    }
    
    .admin-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    
    .order-details-container {
        box-shadow: none;
        padding: 0;
    }
    
    .order-header h2 {
        font-size: 1.8rem;
    }
    
    .order-info-grid {
        grid-template-columns: 1fr;
    }
    
    .order-info-card,
    .order-items-table {
        break-inside: avoid;
    }
}
</style>
