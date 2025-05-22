<?php
// Handle AJAX requests for filters - FIXED: Check for POST data
if ((isset($_GET['ajax']) && $_GET['ajax'] == '1') || (isset($_POST['ajax']) && $_POST['ajax'] == '1')) {
    // Include only the database connection for AJAX requests
    require 'db/con_db.php';
    
    // Get orders list with filters - pass POST data to the filter script
    $_GET = array_merge($_GET, $_POST); // Merge POST into GET for pas_filtri.php compatibility
    require 'db/pas_filtri.php';
    
    // Return only the table rows
    if ($orders_result->num_rows > 0) {
        while ($order = $orders_result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $order['id_pasutijums'] . '</td>';
            echo '<td>';
            echo '<div class="client-info">';
            echo '<div>' . htmlspecialchars($order['vards'] . ' ' . $order['uzvards']) . '</div>';
            echo '<small>' . htmlspecialchars($order['lietotajvards']) . '</small>';
            echo '</div>';
            echo '</td>';
            echo '<td>' . date('d.m.Y H:i', strtotime($order['pas_datums'])) . '</td>';
            echo '<td>' . number_format($order['kopeja_cena'], 2) . '€</td>';
            echo '<td>' . $order['produktu_skaits'] . '</td>';
            echo '<td>';
            echo '<span class="status ' . strtolower($order['statuss']) . '">' . $order['statuss'] . '</span>';
            echo '</td>';
            echo '<td class="action-buttons">';
            echo '<a href="pasutijumi.php?view=' . $order['id_pasutijums'] . '" class="btn"><i class="fas fa-eye"></i> Skatīt</a>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr>';
        echo '<td colspan="7" class="no-records">Nav atrasts neviens pasūtījums ar norādītajiem parametriem</td>';
        echo '</tr>';
    }
    exit; // Stop execution after returning AJAX response
}

// Include admin header ONLY for non-AJAX requests
require 'header.php';

// Database connection
require 'db/con_db.php';

// Handle order status update
if (isset($_POST['update_status'])) {
    require 'db/update_status.php';
}

// Handle view order details
$view_order = null;
if (isset($_GET['view'])) {
    require 'db/pas_vienumi.php';
}

// Get orders list with filters (only if not AJAX request)
require 'db/pas_filtri.php';
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
                                <option value="Iesniegts" <?= $view_order['statuss'] == 'Iesniegts' ? 'selected' : '' ?>>Iesniegts</option>
                                <option value="Apstiprināts" <?= $view_order['statuss'] == 'Apstiprināts' ? 'selected' : '' ?>>Apstiprināts</option>
                                <option value="Nosūtīts" <?= $view_order['statuss'] == 'Nosūtīts' ? 'selected' : '' ?>>Nosūtīts</option>
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
                                        <td><?= $item['daudzums_no_groza'] ?></td>
                                        <td><?= number_format($item['cena'] * $item['daudzums_no_groza'], 2) ?>€</td>
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
                <form id="filters-form" class="filters-form">
                    <div class="filter-group">
                        <label for="status">Statuss:</label>
                        <select id="status" name="status">
                            <option value="">Visi statusi</option>
                            <option value="Iesniegts" <?= $status_filter == 'Iesniegts' ? 'selected' : '' ?>>Iesniegts</option>
                            <option value="Apstiprināts" <?= $status_filter == 'Apstiprināts' ? 'selected' : '' ?>>Apstiprināts</option>
                            <option value="Nosūtīts" <?= $status_filter == 'Nosūtīts' ? 'selected' : '' ?>>Nosūtīts</option>
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
                            <input type="date" id="date_from" name="date_from" value="<?= $date_from ?>">
                            <span>līdz</span>
                            <input type="date" id="date_to" name="date_to" value="<?= $date_to ?>">
                        </div>
                    </div>
                    
                </form>
            </div>
            
            <div class="orders-table-container">
                <div id="loading-indicator" style="display: none; text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin"></i> Ielādē...
                </div>
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
                        <tbody id="orders-tbody">
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
                                            <a href="pasutijumi.php?view=<?= $order['id_pasutijums'] ?>" class="btn"><i class="fas fa-eye"></i> Skatīt</a>
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

            <script>
                // Function to load orders with AJAX
                function loadOrders() {
                    const formData = new FormData();
                    formData.append('ajax', '1');
                    formData.append('status', document.getElementById('status').value);
                    formData.append('search', document.getElementById('search').value);
                    formData.append('date_from', document.getElementById('date_from').value);
                    formData.append('date_to', document.getElementById('date_to').value);

                    // Show loading indicator
                    document.getElementById('loading-indicator').style.display = 'block';
                    document.getElementById('orders-tbody').style.opacity = '0.5';

                    fetch('pasutijumi.php?' + new URLSearchParams(formData).toString())
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('orders-tbody').innerHTML = html;
                            document.getElementById('loading-indicator').style.display = 'none';
                            document.getElementById('orders-tbody').style.opacity = '1';
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            document.getElementById('loading-indicator').style.display = 'none';
                            document.getElementById('orders-tbody').style.opacity = '1';
                        });
                }

                // Auto-filter on input change (optional - can be removed if too frequent)
                document.getElementById('search').addEventListener('input', function() {
                    // Debounce the search input
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        loadOrders();
                    }, 500);
                });

                // Auto-filter on status change
                document.getElementById('status').addEventListener('change', function() {
                    loadOrders();
                });

                // Auto-filter on date change
                document.getElementById('date_from').addEventListener('change', function() {
                    loadOrders();
                });

                document.getElementById('date_to').addEventListener('change', function() {
                    loadOrders();
                });
            </script>
        <?php endif; ?>
    </section>
</main>

</document_content>