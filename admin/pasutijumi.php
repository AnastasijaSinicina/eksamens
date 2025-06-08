<?php

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
        
        <?php if (isset($_GET['view'])): ?>
            <!-- Order Details View -->
            <div class="order-details-container">
                <div class="back-link">
                    <a href="pasutijumi.php" class="btn"><i class="fas fa-arrow-left"></i> Atgriezties pie pasūtījumu saraksta</a>
                </div>
                
                <div class="order-header">
                    <h2>Pasūtījums #<span id="order-id"><?= $_GET['view'] ?></span></h2>
                    <div class="order-status-section">
                        <span class="status-label">Pašreizējais statuss:</span>
                        <span class="status" id="current-status"><?= htmlspecialchars($view_order['statuss'] ?? '') ?></span>
                        
                        <form method="post" action="pasutijumi.php" class="status-form" id="status-form">
                            <input type="hidden" name="order_id" id="order_id" value="<?= $view_order['id_pasutijums'] ?>">
                            <select name="new_status" id="new_status" class="status-select">
                                <option value="Iesniegts" <?= ($view_order['statuss'] ?? '') == 'Iesniegts' ? 'selected' : '' ?>>Iesniegts</option>
                                <option value="Apstiprināts" <?= ($view_order['statuss'] ?? '') == 'Apstiprināts' ? 'selected' : '' ?>>Apstiprināts</option>
                                <option value="Nosūtīts" <?= ($view_order['statuss'] ?? '') == 'Nosūtīts' ? 'selected' : '' ?>>Nosūtīts</option>
                                <option value="Saņemts" <?= ($view_order['statuss'] ?? '') == 'Saņemts' ? 'selected' : '' ?>>Saņemts</option>
                            </select>
                            <button type="submit" name="update_status" class="btn">Atjaunināt statusu</button>
                        </form>
                    </div>
                </div>
                
                <div class="order-info-grid" id="order-info-grid">
                    <!-- Order information will be loaded here -->
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
                            <tbody id="order-items-tbody">
                                <?php if (isset($view_order['items']) && is_array($view_order['items'])): ?>
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
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="no-records">Nav atrasti pasūtījuma vienumi</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="order-actions">
                    <a href="#" onclick="printOrderDetails()" class="btn"><i class="fas fa-print"></i> Drukāt pasūtījumu</a>
                </div>
            </div>
            
            <script>
                // Load order details on page load
                document.addEventListener('DOMContentLoaded', function() {
                    loadOrderDetails();
                    loadOrderItems();
                });

                function loadOrderDetails() {
                    const orderId = document.getElementById('order_id').value;
                    
                    fetch(`db/pasutijumi_admin.php?fetch_order_details=1&id=${orderId}`)
                        .then(response => response.json())
                        .then(order => {
                            if (order) {
                                // Update current status
                                const statusElement = document.getElementById('current-status');
                                statusElement.textContent = order.statuss;
                                statusElement.className = 'status ' + order.statuss.toLowerCase();
                                
                                // Set status select
                                document.getElementById('new_status').value = order.statuss;
                                
                                // Update order info grid
                                const orderInfoGrid = document.getElementById('order-info-grid');
                                orderInfoGrid.innerHTML = `
                                    <div class="order-info-card">
                                        <h3>Pasūtījuma informācija</h3>
                                        <div class="info-row">
                                            <span>Pasūtījuma datums:</span>
                                            <span>${new Date(order.pas_datums).toLocaleString('lv-LV')}</span>
                                        </div>
                                        <div class="info-row">
                                            <span>Pasūtījuma statuss:</span>
                                            <span class="status ${order.statuss.toLowerCase()}">${order.statuss}</span>
                                        </div>
                                        <div class="info-row">
                                            <span>Kopējā summa:</span>
                                            <span class="total-price">${parseFloat(order.kopeja_cena).toFixed(2)}€</span>
                                        </div>
                                        <div class="info-row">
                                            <span>Produktu skaits:</span>
                                            <span>${order.produktu_skaits}</span>
                                        </div>
                                        ${order.red_liet_name ? `
                                        <div class="info-row">
                                            <span>Rediģēja:</span>
                                            <span>${order.red_liet_name}</span>
                                        </div>
                                        ${order.red_dat ? `
                                        <div class="info-row">
                                            <span>Rediģēšanas datums:</span>
                                            <span>${new Date(order.red_dat).toLocaleString('lv-LV')}</span>
                                        </div>
                                        ` : ''}
                                        ` : ''}
                                    </div>
                                    
                                    <div class="order-info-card">
                                        <h3>Klienta informācija</h3>
                                        <div class="info-row">
                                            <span>Lietotājvārds:</span>
                                            <span>${order.lietotajvards}</span>
                                        </div>
                                        <div class="info-row">
                                            <span>Vārds, uzvārds:</span>
                                            <span>${order.vards} ${order.uzvards}</span>
                                        </div>
                                        <div class="info-row">
                                            <span>E-pasts:</span>
                                            <span>${order.epasts}</span>
                                        </div>
                                        <div class="info-row">
                                            <span>Telefons:</span>
                                            <span>${order.talrunis}</span>
                                        </div>
                                    </div>
                                
                                `;
                            }
                        })
                        .catch(error => {
                            console.error('Error loading order details:', error);
                        });
                }

                function loadOrderItems() {
                    const orderId = document.getElementById('order_id').value;
                    
                    fetch(`db/pasutijumi_admin.php?fetch_order_items=1&id=${orderId}`)
                        .then(response => response.json())
                        .then(items => {
                            const tbody = document.getElementById('order-items-tbody');
                            let html = '';
                            let total = 0;
                            
                            items.forEach(item => {
                                const itemTotal = parseFloat(item.cena) * parseInt(item.daudzums_no_groza);
                                total += itemTotal;
                                
                                html += `
                                    <tr>
                                        <td>
                                            <img src="data:image/jpeg;base64,${item.attels1}" alt="${item.nosaukums}" width="50">
                                        </td>
                                        <td>${item.nosaukums}</td>
                                        <td>${parseFloat(item.cena).toFixed(2)}€</td>
                                        <td>${item.daudzums_no_groza}</td>
                                        <td>${itemTotal.toFixed(2)}€</td>
                                    </tr>
                                `;
                            });
                            
                            html += `
                                <tr class="total-row">
                                    <td colspan="4" class="text-right"><strong>Kopā:</strong></td>
                                    <td><strong>${total.toFixed(2)}€</strong></td>
                                </tr>
                            `;
                            
                            tbody.innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error loading order items:', error);
                        });
                }

                // Handle status update
                document.getElementById('status-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    formData.append('update_status', '1');
                    
                    fetch('db/pasutijumi_admin.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Show success notification
                            showNotification('success', data.message);
                            // Reload order details to show updated info
                            loadOrderDetails();
                        } else {
                            showNotification('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating status:', error);
                        showNotification('error', 'Notika kļūda statusa atjaunināšanā');
                    });
                });

                function showNotification(type, message) {
                    const notification = document.querySelector('.notification');
                    const icon = notification.querySelector('i');
                    const messageEl = notification.querySelector('p');
                    
                    // Update notification content
                    messageEl.textContent = message;
                    
                    if (type === 'success') {
                        icon.className = 'fas fa-check-circle success';
                    } else {
                        icon.className = 'fas fa-exclamation-circle error';
                    }
                    
                    // Show notification
                    document.querySelector('.notification-container').style.display = 'block';
                    
                    // Hide after 3 seconds
                    setTimeout(() => {
                        document.querySelector('.notification-container').style.display = 'none';
                    }, 3000);
                }

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
                            <option value="Iesniegts" <?= ($status_filter ?? '') == 'Iesniegts' ? 'selected' : '' ?>>Iesniegts</option>
                            <option value="Apstiprināts" <?= ($status_filter ?? '') == 'Apstiprināts' ? 'selected' : '' ?>>Apstiprināts</option>
                            <option value="Nosūtīts" <?= ($status_filter ?? '') == 'Nosūtīts' ? 'selected' : '' ?>>Nosūtīts</option>
                            <option value="Saņemts" <?= ($status_filter ?? '') == 'Saņemts' ? 'selected' : '' ?>>Saņemts</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Meklēt:</label>
                        <input type="text" id="search" name="search" placeholder="Pasūtījuma #, klienta vārds vai e-pasts" value="<?= htmlspecialchars($search_filter ?? '') ?>">
                    </div>
                    
                    <div class="filter-group date-range">
                        <label>Datuma diapazons:</label>
                        <div class="date-inputs">
                            <input type="date" id="date_from" name="date_from" value="<?= $date_from ?? '' ?>">
                            <span>līdz</span>
                            <input type="date" id="date_to" name="date_to" value="<?= $date_to ?? '' ?>">
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
                                <th>Rediģēts</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody id="orders-tbody">
                            <?php if (isset($orders_result) && $orders_result->num_rows > 0): ?>
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
                                        <td>
                                            <?php if (!empty($order['red_liet_name'])): ?>
                                                <small><?= htmlspecialchars($order['red_liet_name']) ?></small>
                                                <?php if (!empty($order['red_dat'])): ?>
                                                    <br><small><?= date('d.m.Y H:i', strtotime($order['red_dat'])) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="pasutijumi.php?view=<?= $order['id_pasutijums'] ?>" class="btn"><i class="fas fa-eye"></i> Skatīt</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-records">Nav atrasts neviens pasūtījums ar norādītajiem parametriem</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Container -->
                <div class="pagination-container" id="pagination-container">
                    <div class="pagination-info">
                        <span id="pagination-text">Rāda 1-6 no 0 ierakstiem</span>
                    </div>
                    <div class="pagination-controls" id="pagination-controls">
                        <!-- Pagination buttons will be inserted here -->
                    </div>
                </div>
            </div>

            <script>

        document.addEventListener('DOMContentLoaded', function() {
            initializeOrdersPagination();
        });
            </script>
        <?php endif; ?>
    </section>
</main>