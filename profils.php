<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Novirza uz login, ja nav pieslēdzies
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu, ielogojieties, lai piekļūtu profilam!";
    header("Location: login.php");
    exit();
}

// Iekļauj datubāzes savienojumu
require_once "admin/db/con_db.php";

// Iegūst lietotāja informāciju
$lietotajvards = $_SESSION['lietotajvardsSIN'];
$vaicajums = $savienojums->prepare("SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?");
$vaicajums->bind_param("s", $lietotajvards);
$vaicajums->execute();
$rezultats = $vaicajums->get_result();
$lietotajs = $rezultats->fetch_assoc();

// Iegūst lietotāja pasūtījumus
$order_query = "SELECT p.*, COUNT(pv.vienums_id) as total_items
                FROM sparkly_pasutijumi p
                LEFT JOIN sparkly_pasutijuma_vienumi pv ON p.id_pasutijums = pv.pasutijuma_id
                WHERE p.lietotajs_id = ?
                GROUP BY p.id_pasutijums
                ORDER BY p.pas_datums DESC";
$order_stmt = $savienojums->prepare($order_query);
$order_stmt->bind_param("i", $lietotajs['id_lietotajs']);
$order_stmt->execute();
$orders_result = $order_stmt->get_result();

// Iekļauj header
include 'header.php';
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
                    <!-- Order Filter Buttons -->
                    <div class="order-filters">
                        <button class="filter-button active" data-status="all">Visi pasūtījumi</button>
                        <button class="filter-button order-status iesniegts" data-status="Iesniegts">Iesniegts</button>
                        <button class="filter-button order-status apstiprināts" data-status="Apstiprināts">Apstiprināts</button>
                        <button class="filter-button order-status aizsūtīts" data-status="Aizsūtīts">Aizsūtīts</button>
                        <button class="filter-button order-status saņemts" data-status="Saņemts">Saņemts</button>
                    </div>
                    
                    <div class="orders-list">
                        <?php if ($orders_result->num_rows > 0): ?>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <div class="order-item" data-status="<?php echo $order['statuss']; ?>">
                                    <div class="order-header">
                                        <div class="order-id">
                                            <h3>Pasūtījums #<?php echo $order['id_pasutijums']; ?></h3>
                                            <span class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['pas_datums'])); ?></span>
                                        </div>
                                        <div class="order-status <?php echo strtolower($order['statuss']); ?>">
                                            <?php echo $order['statuss']; ?>
                                            <?php if ($order['statuss'] == 'Aizsūtīts'): ?>
                                            <div class="order-actions">
                                                <a href="admin/db/sanemts_sutijums.php?id=<?php echo $order['id_pasutijums']; ?>" 
                                                class="btn btn-success">
                                                    <i class="fas fa-check"></i>
                                                    Atzīmēt kā saņemtu
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="order-details">
                                        <div class="order-info">
                                            <div class="order-summary">
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
                                            </div>
                                            
                                            <div class="order-address">
                                                <h4>Piegādes adrese:</h4>
                                                <p><?php echo htmlspecialchars($order['vards'] . ' ' . $order['uzvards']); ?></p>
                                                <?php if ($order['piegades_veids'] == 'Kurjers'): ?>
                                                    <p><?php echo htmlspecialchars($order['adrese']); ?></p>
                                                    <p><?php echo htmlspecialchars($order['pilseta'] . ', ' . $order['pasta_indeks']); ?></p>
                                                <?php else: ?>
                                                    <p><em>Izņemšana no veikala</em></p>
                                                <?php endif; ?>
                                                <p>Tel: <?php echo htmlspecialchars($order['talrunis']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="order-items">
                                            <h4>Pasūtītās preces:</h4>
                                            <?php
                                            // Iegūst pasūtījuma vienumus
                                            $items_query = "SELECT pv.*, p.nosaukums, p.attels1
                                                           FROM sparkly_pasutijuma_vienumi pv
                                                           JOIN produkcija_sprarkly p ON pv.produkta_id = p.id_bumba
                                                           WHERE pv.pasutijuma_id = ?";
                                            $items_stmt = $savienojums->prepare($items_query);
                                            $items_stmt->bind_param("i", $order['id_pasutijums']);
                                            $items_stmt->execute();
                                            $items_result = $items_stmt->get_result();
                                            ?>
                                            
                                            <div class="items-grid">
                                                <?php while ($item = $items_result->fetch_assoc()): ?>
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
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-orders">
                                <div class="empty-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <h3>Jums vēl nav pasūtījumu</h3>
                                <p>Kad būsiet veicis pasūtījumu, tie parādīsies šeit.</p>
                                <a href="produkcija.php" class="btn">Sākt iepirkšanos</a>
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
// Cilņu pārslēgšanas funkcionalitāte
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Noņem aktīvo klasi no visiem pogas un paneliem
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Pievieno aktīvo klasi noklikšķinātai pogai un atbilstošajam panelim
            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Order filter functionality
    const filterButtons = document.querySelectorAll('.filter-button');
    const orderItems = document.querySelectorAll('.order-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all filter buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Get selected status
            const selectedStatus = button.getAttribute('data-status');
            
            // Filter orders
            orderItems.forEach(item => {
                const itemStatus = item.getAttribute('data-status');
                if (selectedStatus === 'all' || itemStatus === selectedStatus) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // Konta dzēšanas modāļa funkcionalitāte
    const deleteBtn = document.getElementById('delete-account-btn');
    const deleteModal = document.getElementById('delete-confirmation');
    const cancelBtn = document.getElementById('cancel-delete');
    
    if (deleteBtn && deleteModal && cancelBtn) {
        deleteBtn.addEventListener('click', function() {
            deleteModal.style.display = 'flex';
        });
        
        cancelBtn.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });
        
        // Aizvērt modālu, kad noklikšķina ārpus tā
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }


// Attēla priekšskatījuma funkcija
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const imagePreview = document.querySelector('.image-preview');
        const imagePlaceholder = document.getElementById('image-placeholder');
        
        reader.onload = function(e) {
            if (imagePreview) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            } else {
                // Izveido jaunu attēla priekšskatījumu, ja tas neeksistē
                const newImg = document.createElement('img');
                newImg.src = e.target.result;
                newImg.alt = 'Profila attēls';
                newImg.className = 'image-preview';
                document.querySelector('.current-image').appendChild(newImg);
            }
            
            if (imagePlaceholder) {
                imagePlaceholder.style.display = 'none';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

    
    // Pievieno paroļu sakritības pārbaudes funkcionalitāti
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (newPassword && confirmPassword) {
        // Funkcija, lai pārbaudītu, vai paroles sakrīt
        function checkPasswordMatch() {
            // Noņem visus esošos ikonu konteinerus
            const existingIcons = confirmPassword.parentElement.querySelectorAll('.icon-container');
            existingIcons.forEach(icon => icon.remove());
            
            if (confirmPassword.value === '') {
                // Ja apstiprinājuma parole ir tukša, nerāda nevienu ikonu
                return;
            }
            
            // Izveido ikonu konteineru
            const iconContainer = document.createElement('span');
            iconContainer.className = 'icon-container';
            
            if (newPassword.value === confirmPassword.value) {
                // Paroles sakrīt
                iconContainer.classList.add('match-container', 'visible');
                iconContainer.innerHTML = '<i class="fas fa-check password-match-icon"></i>';
            } else {
                // Paroles nesakrīt
                iconContainer.classList.add('mismatch-container', 'visible');
                iconContainer.innerHTML = '<i class="fas fa-times password-mismatch-icon"></i>';
            }
            
            // Pievieno ikonu konteineru apstiprinājuma paroles lauka vecākam
            confirmPassword.parentElement.style.position = 'relative';
            confirmPassword.parentElement.appendChild(iconContainer);
        }
        
        // Pārbauda paroles pie ievades
        newPassword.addEventListener('input', checkPasswordMatch);
        confirmPassword.addEventListener('input', checkPasswordMatch);
        
        // Formas validācija pirms nosūtīšanas
        const passwordForm = document.querySelector('.password-form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(event) {
                if (newPassword.value !== confirmPassword.value) {
                    event.preventDefault();
                    alert('Paroles nesakrīt!');
                    return false;
                }
                

            });
        }
    }
    });
</script>

<?php
// Iekļauj footer
include 'footer.php';
?>