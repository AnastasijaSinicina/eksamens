<?php


// Include admin header
require 'header.php';

// Database connection
require 'db/spec_pas_admin.php';

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
        <h1>Pielāgoto pasūtījumu pārvaldība</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="notification success-message">
                <i class="fas fa-check-circle success"></i>
                <p><?= $success_message ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="notification error-message">
                <i class="fas fa-exclamation-circle error"></i>
                <p><?= $error_message ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($view_order): ?>
            <!-- Order Details View -->
            <div class="order-details-container">
                <div class="back-link">
                    <a href="spec_pas.php" class="btn"><i class="fas fa-arrow-left"></i> Atgriezties pie saraksta</a>
                </div>
                
                <div class="order-header">
                    <h2>Pielāgots pasūtījums #C<?= $view_order['id_spec_pas'] ?></h2>
                    <div class="order-status-section">
                        <span class="status-label">Pašreizējais statuss:</span>
                        <span class="status <?= strtolower(str_replace('ē', 'e', $view_order['statuss'])) ?>"><?= $view_order['statuss'] ?></span>
                        
                        <form method="post" action="spec_pas.php" class="status-form">
                            <input type="hidden" name="order_id" value="<?= $view_order['id_spec_pas'] ?>">
                            <select name="new_status" class="status-select">
                                <option value="Iesniegts" <?= $view_order['statuss'] == 'Iesniegts' ? 'selected' : '' ?>>Iesniegts</option>
                                <option value="Apstiprināts" <?= $view_order['statuss'] == 'Apstiprināts' ? 'selected' : '' ?>>Apstiprināts</option>
                                <option value="Nosūtīts" <?= $view_order['statuss'] == 'Nosūtīts' ? 'selected' : '' ?>>Nosūtīts</option>
                                <option value="Saņemts" <?= $view_order['statuss'] == 'Saņemts' ? 'selected' : '' ?>>Saņemts</option>
                                <option value="Atcelts" <?= $view_order['statuss'] == 'Atcelts' ? 'selected' : '' ?>>Atcelts</option>
                            </select>
                            <input type="number" name="new_price" step="0.01" min="0" value="<?= $view_order['cena'] ?? '' ?>" 
                                   placeholder="Cena €" class="price-input">
                            <button type="submit" name="update_order" class="btn">Atjaunināt</button>
                        </form>
                    </div>
                </div>
                
                <div class="order-info-grid">
                    <div class="order-info-card">
                        <h3>Pasūtījuma informācija</h3>
                        <div class="info-row">
                            <span>Pasūtījuma datums:</span>
                            <span><?= date('d.m.Y H:i', strtotime($view_order['datums'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span>Pasūtījuma statuss:</span>
                            <span class="status <?= strtolower(str_replace('ē', 'e', $view_order['statuss'])) ?>"><?= $view_order['statuss'] ?></span>
                        </div>
                        <div class="info-row">
                            <span>Daudzums:</span>
                            <span><?= $view_order['daudzums'] ?></span>
                        </div>
                        <div class="info-row">
                            <span>Kopējā summa:</span>
                            <span class="total-price">
                                <?php if ($view_order['cena']): ?>
                                    <?= number_format($view_order['cena'], 2) ?>€
                                <?php else: ?>
                                    <em class="price-not-set">Nav noteikta</em>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-info-card">
                        <h3>Klienta informācija</h3>
                        <div class="info-row">
                            <span>Lietotājvārds:</span>
                            <span><?= htmlspecialchars($view_order['lietotajvards'] ?? 'Nav') ?></span>
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
                            <span><?= htmlspecialchars($view_order['pasta_indekss']) ?></span>
                        </div>
                    </div>
                </div>
                
               <!-- Product Specifications -->
                <?php if ($view_order['forma'] || $view_order['audums'] || $view_order['piezimes']): ?>
                <div class="order-info-card specifications-card">
                    <h3>Produkta specifikācijas</h3>
                    <div class="specifications-grid">
                        <?php if ($view_order['forma']): ?>
                            <div class="info-row">
                                <span>Forma:</span>
                                <span>
                                    <?php if (isset($view_order['forma_name']) && $view_order['forma_name']): ?>
                                        <?= htmlspecialchars($view_order['forma_name']) ?>
                                    <?php else: ?>
                                        ID: <?= $view_order['forma'] ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ($view_order['audums']): ?>
                            <div class="info-row">
                                <span>Audums:</span>
                                <span>
                                    <?php if (isset($view_order['audums_name']) && $view_order['audums_name']): ?>
                                        <?= htmlspecialchars($view_order['audums_name']) ?>
                                    <?php else: ?>
                                        ID: <?= $view_order['audums'] ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ($view_order['malu_figura']): ?>
                            <div class="info-row">
                                <span>Malu figūra:</span>
                                <span>
                                    <?php if (isset($view_order['malu_figura_name']) && $view_order['malu_figura_name']): ?>
                                        <?= htmlspecialchars($view_order['malu_figura_name']) ?>
                                    <?php else: ?>
                                        ID: <?= $view_order['malu_figura'] ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ($view_order['dekorejums1']): ?>
                            <div class="info-row">
                                <span>Dekorējums 1:</span>
                                <span>
                                    <?php if (isset($view_order['dekorejums1_name']) && $view_order['dekorejums1_name']): ?>
                                        <?= htmlspecialchars($view_order['dekorejums1_name']) ?>
                                    <?php else: ?>
                                        ID: <?= $view_order['dekorejums1'] ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ($view_order['dekorejums2']): ?>
                            <div class="info-row">
                                <span>Dekorējums 2:</span>
                                <span>
                                    <?php if (isset($view_order['dekorejums2_name']) && $view_order['dekorejums2_name']): ?>
                                        <?= htmlspecialchars($view_order['dekorejums2_name']) ?>
                                    <?php else: ?>
                                        ID: <?= $view_order['dekorejums2'] ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($view_order['piezimes']): ?>
                        <div class="notes-section">
                            <strong>Piezīmes:</strong>
                            <div class="notes-content">
                                <?= nl2br(htmlspecialchars($view_order['piezimes'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="order-actions">
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
            <div class="orders-table-container">
                <div class="table-responsive">
                    <table class="product-table orders-table">
                        <thead>
                            <tr>
                                <th>Pasūtījuma #</th>
                                <th>Klients</th>
                                <th>Datums</th>
                                <th>Daudzums</th>
                                <th>Cena</th>
                                <th>Statuss</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                                <?php while ($order = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#C<?= $order['id_spec_pas'] ?></td>
                                        <td>
                                            <div class="client-info">
                                                <div><?= htmlspecialchars($order['vards'] . ' ' . $order['uzvards']) ?></div>
                                                <small><?= htmlspecialchars($order['lietotajvards'] ?? 'Nav') ?></small>
                                            </div>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($order['datums'])) ?></td>
                                        <td><?= $order['daudzums'] ?></td>
                                        <td>
                                            <?php if ($order['cena']): ?>
                                                <?= number_format($order['cena'], 2) ?>€
                                            <?php else: ?>
                                                <span class="price-not-set">Nav noteikta</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status <?= strtolower(str_replace('ē', 'e', $order['statuss'])) ?>">
                                                <?= $order['statuss'] ?>
                                            </span>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="spec_pas.php?view=<?= $order['id_spec_pas'] ?>" class="btn edit-btn">
                                                <i class="fas fa-eye"></i> Skatīt
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="no-records">
                                        Nav atrasts neviens pielāgots pasūtījums
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>