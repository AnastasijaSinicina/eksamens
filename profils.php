<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu, ielogojieties, lai piekļūtu profilam!";
    header("Location: login.php");
    exit();
}

// Include database connection
require_once "admin/db/con_db.php";

// Fetch user information
$lietotajvards = $_SESSION['lietotajvardsSIN'];
$vaicajums = $savienojums->prepare("SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?");
$vaicajums->bind_param("s", $lietotajvards);
$vaicajums->execute();
$rezultats = $vaicajums->get_result();
$lietotajs = $rezultats->fetch_assoc();

// Include header
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
                    // Profile image handling for MEDIUMBLOB
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
                <!-- Personal Info Tab -->
                <div class="tab-pane active" id="personal-info">
                    <form action="admin/db/update_profile.php" method="post" enctype="multipart/form-data" class="profile-form">
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
                        
                        <div class="form-group">
                            <label for="telefons">Telefons:</label>
                            <input type="text" id="telefons" name="telefons" value="<?php echo htmlspecialchars($lietotajs['telefons'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="adrese">Adrese:</label>
                            <textarea id="adrese" name="adrese"><?php echo htmlspecialchars($lietotajs['adrese'] ?? ''); ?></textarea>
                        </div>
                        <!--                         
                        <div class="form-group">
                            <label for="profile_image">Profila attēls:</label>
                            <input type="file" id="profile_image" name="profile_image" accept="image/*">
                            <small>Atļautie formāti: JPG, PNG, GIF (maks. 5MB)</small>
                        </div> -->
                        
                        <button type="submit" name="saglabat" class="btn">Saglabāt izmaiņas</button>
                    </form>
                </div>
                
                <!-- Rest of the code remains the same as previous version -->
                <!-- Orders Tab -->
                <div class="tab-pane" id="orders">
                    <div class="orders-list">
                        <?php
                        // Fetch user orders
                        $vaicajums_pasutijumi = $savienojums->prepare("SELECT * FROM pasutijumi WHERE lietotajs_id = ? ORDER BY datums DESC");
                        $vaicajums_pasutijumi->bind_param("i", $lietotajs['id']);
                        $vaicajums_pasutijumi->execute();
                        $pasutijumi = $vaicajums_pasutijumi->get_result();
                        
                        if ($pasutijumi->num_rows > 0) {
                            while ($pasutijums = $pasutijumi->fetch_assoc()) {
                                ?>
                                <div class="order-item">
                                    <div class="order-header">
                                        <div class="order-id">
                                            <h3>Pasūtījums #<?php echo $pasutijums['id']; ?></h3>
                                            <span class="order-date"><?php echo date('d.m.Y', strtotime($pasutijums['datums'])); ?></span>
                                        </div>
                                        <div class="order-status <?php echo strtolower($pasutijums['statuss']); ?>">
                                            <?php echo $pasutijums['statuss']; ?>
                                        </div>
                                    </div>
                                    <div class="order-details">
                                        <p><strong>Summa:</strong> €<?php echo number_format($pasutijums['summa'], 2); ?></p>
                                        <a href="pasutijums.php?id=<?php echo $pasutijums['id']; ?>" class="btn small">Skatīt detaļas</a>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="empty-orders"><p>Jums vēl nav pasūtījumu.</p></div>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Settings Tab -->
                <div class="tab-pane" id="settings">
                    <!-- Settings content remains the same -->
                </div>
            </div>
        </div>
    </div>
</section>
<style>
    
</style>

<script>
    // Script remains the same as in previous version
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and panes
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Add active class to clicked button and corresponding pane
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Delete account modal
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
        }
    });
</script>

<?php
// Include footer
include 'footer.php';
?>