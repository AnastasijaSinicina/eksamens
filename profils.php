<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu, ielogojieties, lai piekļūtu profilam!";
    header("Location: login.php");
    exit();
}

// Iekļauj datubāzes savienojumu
require_once "admin/db/con_db.php";

// Iekļauj pasūtījumu vestures funkcionalitāti
require_once "admin/db/pasutijuma_vesture.php";

// Iegūst lietotāja informāciju
$lietotajvards = $_SESSION['lietotajvardsSIN'];
$vaicajums = $savienojums->prepare("SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?");
$vaicajums->bind_param("s", $lietotajvards);
$vaicajums->execute();
$rezultats = $vaicajums->get_result();
$lietotajs = $rezultats->fetch_assoc();

// Check if user was found
if (!$lietotajs) {
    error_log("User not found: " . $lietotajvards);
    $_SESSION['pazinojums'] = "Lietotājs nav atrasts!";
    header("Location: login.php");
    exit();
}

// Iegūst visus pasūtījumus
$all_orders = getUserOrders($savienojums, $lietotajs['id_lietotajs']);

// Close prepared statements
$vaicajums->close();

// Include header
require 'header.php';
?>

<section id="profils">
    <div class="profile-container">
        <h1>Mans profils</h1>
        
        <?php if(isset($_SESSION['pazinojums'])): ?>
            <div class="profile-notification">
                <p><?php echo $_SESSION['pazinojums']; ?></p>
            </div>
            <?php unset($_SESSION['pazinojums']); ?>
        <?php endif; ?>
        
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php
                    // Profila attēla apstrāde MEDIUMBLOB datiem
                    $profile_image = !empty($lietotajs['foto']) 
                        ? 'data:image/jpeg;base64,'.base64_encode($lietotajs['foto']) 
                        : null;
                    ?>
                    <?php if ($profile_image): ?>
                        <img src="<?php echo $profile_image; ?>" 
                             alt="Profila attēls" 
                             class="profile-image"
                             onerror="this.style.display='none'; document.getElementById('default-avatar').style.display='block';">
                    <?php endif; ?>
                    
                    <div id="default-avatar" class="default-avatar" 
                         style="display: <?php echo $profile_image ? 'none' : 'flex'; ?>">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="profile-title">
                    <h2><?php echo htmlspecialchars($lietotajs['lietotajvards']); ?></h2>
                </div>
            </div>
            
            <div class="profile-tabs">
                <button class="tab-button active" data-tab="personal-info">Personas dati</button>
                <button class="tab-button" data-tab="orders">Mani pasūtījumi</button>
                <button class="tab-button" data-tab="settings">Iestatījumi</button>
            </div>
            
            <div class="tab-content">
                <!-- Personas datu cilne -->
                <div class="tab-pane active" id="personal-info">
                    <form action="admin/db/profila_redigesana.php" method="post" enctype="multipart/form-data" class="profile-form">
                        <div class="form-group">
                            <label>Profila attēls:</label>
                            <div class="image-upload-section">
                                <div class="current-image">
                                    <?php if ($profile_image): ?>
                                        <img src="<?php echo $profile_image; ?>" 
                                             alt="Profila attēls" 
                                             class="image-preview"
                                             onerror="this.style.display='none'; document.getElementById('image-placeholder').style.display='flex';">
                                    <?php endif; ?>
                                    
                                    <div id="image-placeholder" class="image-placeholder" 
                                         style="display: <?php echo $profile_image ? 'none' : 'flex'; ?>">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                
                                <div class="image-actions">
                                    <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                    <label for="profile_image" class="btn">
                                        <i class="fas fa-upload"></i>
                                        Augšupielādēt attēlu
                                    </label>
                                    
                                    <?php if ($profile_image): ?>
                                        <button type="submit" name="delete_image" class="btn btn-outline" 
                                                onclick="return confirm('Vai tiešām vēlaties dzēst profila attēlu?')">
                                            <i class="fas fa-trash"></i>
                                            Dzēst attēlu
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="lietotajvards">Lietotājvārds:</label>
                            <input type="text" id="lietotajvards" name="lietotajvards" value="<?php echo htmlspecialchars($lietotajs['lietotajvards']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="vards">Vārds:</label>
                            <input type="text" id="vards" name="vards" value="<?php echo htmlspecialchars($lietotajs['vards'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="uzvards">Uzvārds:</label>
                            <input type="text" id="uzvards" name="uzvards" value="<?php echo htmlspecialchars($lietotajs['uzvards'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="epasts">E-pasts:</label>
                            <input type="email" id="epasts" name="epasts" value="<?php echo htmlspecialchars($lietotajs['epasts']); ?>">
                        </div>
                        
                        <button type="submit" name="saglabat" class="btn">Saglabāt izmaiņas</button>
                    </form>
                </div>
                
                <!-- Pasūtījumu cilne -->
                <div class="tab-pane" id="orders">
                    <!-- Debug info -->
                    <?php if (isset($_GET['debug'])): ?>
                    <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; font-size: 12px; border: 1px solid #ccc;">
                        <strong>Debug Info:</strong><br>
                        User ID: <?php echo $lietotajs['id_lietotajs']; ?><br>
                        Total orders: <?php echo count($all_orders); ?><br>
                        Regular orders: <?php echo count(array_filter($all_orders, function($o) { return $o['order_type'] == 'regular'; })); ?><br>
                        Custom orders: <?php echo count(array_filter($all_orders, function($o) { return $o['order_type'] == 'custom'; })); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Order Filter Buttons -->
                    <div class="order-filters">
                        <button class="filter-button active" data-status="all">Visi pasūtījumi (<?php echo count($all_orders); ?>)</button>
                        <button class="filter-button" data-type="regular">Regulārie (<?php echo count(array_filter($all_orders, function($o) { return $o['order_type'] == 'regular'; })); ?>)</button>
                        <button class="filter-button" data-type="custom">Pielāgotie (<?php echo count(array_filter($all_orders, function($o) { return $o['order_type'] == 'custom'; })); ?>)</button>
                        <button class="filter-button order-status iesniegts" data-status="Iesniegts">Iesniegts</button>
                        <button class="filter-button order-status apstiprināts" data-status="Apstiprināts">Apstiprināts</button>
                        <button class="filter-button order-status nosūtīts" data-status="Nosūtīts">Nosūtīts</button>
                        <button class="filter-button order-status saņemts" data-status="Saņemts">Saņemts</button>
                        <button class="filter-button order-status atcelts" data-status="Atcelts">Atcelts</button>
                    </div>
                    
                    <div class="orders-list">
                        <?php if (count($all_orders) > 0): ?>
                            <?php $order_counter = 0; foreach ($all_orders as $order): $order_counter++; ?>
                                <div class="order-item" data-status="<?php echo $order['statuss']; ?>" data-type="<?php echo $order['order_type']; ?>">
                                    <div class="order-header">
                                        <div class="order-id">
                                            <h3>
                                                <?php if ($order['order_type'] == 'custom'): ?>
                                                    <span class="custom-badge">PIELĀGOTS</span>
                                                    Pasūtījums #C<?php echo $order['id_pasutijums']; ?>
                                                <?php else: ?>
                                                    Pasūtījums #<?php echo $order['pasutijuma_numurs'] ?? $order['id_pasutijums']; ?>
                                                <?php endif; ?>
                                            </h3>
                                            <span class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['pas_datums'])); ?></span>
                                        </div>
                                        <div class="order-status-section">
                                            <div class="order-status <?php echo strtolower($order['statuss']); ?>">
                                                <?php echo $order['statuss']; ?>
                                            </div>
                                            <button class="expand-button btn" data-order="order-<?php echo $order_counter; ?>">
                                                <i class="fas fa-chevron-down"></i>
                                                Skatīt detaļas
                                            </button>
                                        </div>
                                    </div>
                                    <div class="order-details" id="order-<?php echo $order_counter; ?>" style="display: none;">
                                        <div class="order-info">
                                            <div class="order-summary">
                                                <?php if ($order['order_type'] == 'regular'): ?>
                                                    <div class="summary-item">
                                                        <span class="label">Kopējā summa:</span>
                                                        <span class="value">€<?php echo number_format($order['kopeja_cena'], 2); ?></span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span class="label">Produktu skaits:</span>
                                                        <span class="value"><?php echo $order['produktu_skaits']; ?></span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span class="label">Apmaksas veids:</span>
                                                        <span class="value"><?php echo $order['apmaksas_veids']; ?></span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span class="label">Piegādes veids:</span>
                                                        <span class="value"><?php echo $order['piegades_veids']; ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="summary-item">
                                                        <span class="label">Veids:</span>
                                                        <span class="value">Pielāgots produkts</span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span class="label">Daudzums:</span>
                                                        <span class="value"><?php echo $order['daudzums']; ?></span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span class="label">Cena:</span>
                                                        <span class="value">€<?php echo number_format($order['cena'], 2);?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="order-address">
                                                <h4>Piegādes informācija:</h4>
                                                    <p><em>Izņemšana no veikala</em></p>
                                                <p>Tel: <?php echo htmlspecialchars($order['talrunis']); ?></p>
                                                <p>E-pasts: <?php echo htmlspecialchars($order['epasts']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <?php if ($order['order_type'] == 'regular'): ?>
                                        <div class="order-items">
                                            <h4>Pasūtītās preces:</h4>
                                            <?php
                                            // Iegūst pasūtījuma vienumus
                                            $order_items = getOrderItems($savienojums, $order['id_pasutijums']);
                                            ?>
                                            
                                            <div class="items-grid">
                                                <?php foreach ($order_items as $item): ?>
                                                    <div class="item-card">
                                                        <div class="item-image">
                                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($item['attels1']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($item['nosaukums']); ?>">
                                                        </div>
                                                        <div class="item-info">
                                                            <h5><?php echo htmlspecialchars($item['nosaukums']); ?></h5>
                                                            <p class="item-price">€<?php echo number_format($item['cena'], 2); ?></p>
                                                            <p class="item-quantity">Daudzums: <?php echo $item['daudzums_no_groza']; ?></p>
                                                            <p class="item-total">Kopā: €<?php echo number_format($item['cena'] * $item['daudzums_no_groza'], 2); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="order-items">
                                            <h4>Pielāgotā produkta specifikācijas:</h4>
                                            <div class="custom-specs">
                                                <?php if (!empty($order['forma_name'])): ?>
                                                    <div class="spec-item">
                                                        <span class="spec-label">Forma:</span>
                                                        <span class="spec-value"><?php echo htmlspecialchars($order['forma_name']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($order['audums_name'])): ?>
                                                    <div class="spec-item">
                                                        <span class="spec-label">Audums:</span>
                                                        <span class="spec-value"><?php echo htmlspecialchars($order['audums_name']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($order['malu_figura_name'])): ?>
                                                    <div class="spec-item">
                                                        <span class="spec-label">Malu figūra:</span>
                                                        <span class="spec-value"><?php echo htmlspecialchars($order['malu_figura_name']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($order['dekorejums1_name'])): ?>
                                                    <div class="spec-item">
                                                        <span class="spec-label">Dekorējums:</span>
                                                        <span class="spec-value"><?php echo htmlspecialchars($order['dekorejums1_name']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                
                                                <?php if (!empty($order['piezimes'])): ?>
                                                    <div class="spec-item full-width">
                                                        <span class="spec-label">Piezīmes:</span>
                                                        <span class="spec-value"><?php echo nl2br(htmlspecialchars($order['piezimes'])); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-orders">
                                <div class="empty-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <h3>Jums vēl nav pasūtījumu</h3>
                                <p>Kad būsiet veicis pasūtījumu, tie parādīsies šeit.</p>
                                <div class="empty-actions">
                                    <a href="produkcija.php" class="btn">Sākt iepirkšanos</a>
                                    <a href="materiali.php" class="btn">Izveidot pielāgotu</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Iestatījumu cilne -->
                <div class="tab-pane" id="settings">
                    <div class="settings-form" id="parolesmaina">
                        <h3>Paroles maiņa</h3>
                        <form action="admin/db/paroles_maina.php" method="post" class="password-form">
                            <div class="form-group">
                                <label for="current_password">Pašreizējā parole:</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Jauna parole:</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Apstiprināt paroli:</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn">Mainīt paroli</button>
                        </form>
                        
                        <div class="delete-account">
                            <h3>Dzēst kontu</h3>
                            <p>Šī darbība ir neatgriezeniska. Visi jūsu dati tiks dzēsti neatgriezeniski.</p>
                            <button id="delete-account-btn" class="btn danger">Dzēst kontu</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="delete-confirmation" class="confirmation-modal">
    <div class="modal-content">
        <h3>Apstiprināt konta dzēšanu</h3>
        <p>Vai tiešām vēlaties dzēst savu kontu? Šī darbība ir neatgriezeniska.</p>
        <div class="modal-buttons">
            <button id="cancel-delete" class="btn">Atcelt</button>
            <a href="admin/db/dzest_profilu.php" class="btn danger">Dzēst kontu</a>
        </div>
    </div>
</div>
<script>

</script>

<?php
// Include footer
include 'footer.php';
?>