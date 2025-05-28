<?php
// Include admin header
require 'header.php';
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
                        <span class="status" id="current-status"></span>
                        
                        <form id="status-form" class="status-form">
                            <input type="hidden" name="order_id" id="order_id" value="<?= $_GET['view'] ?>">
                            <select name="new_status" id="new_status" class="status-select">
                                <option value="Iesniegts">Iesniegts</option>
                                <option value="Apstiprināts">Apstiprināts</option>
                                <option value="Nosūtīts">Nosūtīts</option>
                                <option value="Saņemts">Saņemts</option>
                            </select>
                            <button type="submit" class="btn">Atjaunināt statusu</button>
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
                                <!-- Order items will be loaded here -->
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
                                            <span>Apmaksas veids:</span>
                                            <span>${order.apmaksas_veids}</span>
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
                                    
                                    <div class="order-info-card">
                                        <h3>Piegādes informācija</h3>
                                        <div class="info-row">
                                            <span>Adrese:</span>
                                            <span>${order.adrese}</span>
                                        </div>
                                        <div class="info-row">
                                            <span>Pilsēta:</span>
                                            <span>${order.pilseta}</span>
                                        </div>
                                        <div class="info-row">
                                            <span>Pasta indekss:</span>
                                            <span>${order.pasta_indeks}</span>
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
                            <option value="Iesniegts">Iesniegts</option>
                            <option value="Apstiprināts">Apstiprināts</option>
                            <option value="Nosūtīts">Nosūtīts</option>
                            <option value="Saņemts">Saņemts</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Meklēt:</label>
                        <input type="text" id="search" name="search" placeholder="Pasūtījuma #, klienta vārds vai e-pasts">
                    </div>
                    
                    <div class="filter-group date-range">
                        <label>Datuma diapazons:</label>
                        <div class="date-inputs">
                            <input type="date" id="date_from" name="date_from">
                            <span>līdz</span>
                            <input type="date" id="date_to" name="date_to">
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
                            <!-- Orders will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                // Load orders on page load
                document.addEventListener('DOMContentLoaded', function() {
                    loadOrders();
                });

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

                    fetch('db/pasutijumi_admin.php', {
                        method: 'POST',
                        body: formData
                    })
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

                // Auto-filter on input change with debounce
                document.getElementById('search').addEventListener('input', function() {
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