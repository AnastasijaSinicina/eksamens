<?php
session_start();

// Check if user is logged in and has admin/moder role
if (!isset($_SESSION['lietotajvardsSIN']) || ($_SESSION['loma'] !== 'admin' && $_SESSION['loma'] !== 'moder')) {
    header("Location: ../login.php");
    exit();
}

require 'header.php';
require 'db/con_db.php';

// Get current user's data
$lietotajvards = $_SESSION['lietotajvardsSIN'];

// Fetch user information with creator and editor details
$user_query = "SELECT u.*, 
               creator.lietotajvards as created_by_username,
               creator.vards as created_by_first_name,
               creator.uzvards as created_by_last_name,
               editor.lietotajvards as edited_by_username,
               editor.vards as edited_by_first_name,
               editor.uzvards as edited_by_last_name
               FROM lietotaji_sparkly u
               LEFT JOIN lietotaji_sparkly creator ON u.izveidots_liet = creator.id_lietotajs
               LEFT JOIN lietotaji_sparkly editor ON u.red_liet = editor.id_lietotajs
               WHERE u.lietotajvards = ?";

$stmt = $savienojums->prepare($user_query);
$stmt->bind_param("s", $lietotajvards);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

if (!$user_data) {
    $_SESSION['pazinojums'] = "Lietotāja dati nav atrasti!";
    header("Location: ../logout.php");
    exit();
}

// Handle profile image display
$profile_image = '';
if (!empty($user_data['foto'])) {
    $profile_image = 'data:image/jpeg;base64,' . base64_encode($user_data['foto']);
}

?>

<main>
    <!-- Notification container -->
    <div class="notification-container" style="display: none;">
        <div class="notification">
            <i class="fas fa-check-circle success"></i>
            <h3>Veiksmīgi!</h3>
            <p>Darbība veiksmīgi izpildīta.</p>
        </div>
    </div>

    <section class="admin-content">
        <div class="page-header">
            <h1><i class="fas fa-user-circle"></i> Mans profils</h1>
        </div>

        <?php if (isset($_SESSION['pazinojums'])): ?>
            <div class="notification-message">
                <?php 
                echo $_SESSION['pazinojums']; 
                unset($_SESSION['pazinojums']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Profile Content Grid -->
        <div class="dashboard-content-grid">
            <!-- Profile Information Card -->
            <div class="dashboard-card profile-card">
                <h2><i class="fas fa-user"></i> Profila informācija</h2>
                
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if ($profile_image): ?>
                            <img src="<?= $profile_image ?>" alt="Profila attēls" id="profile-image-display">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="avatar-upload" id="avatar-upload">
                            <label for="profile-image-input" class="upload-label">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="profile-image-input" name="profile_image" accept="image/*" style="display: none;">
                        </div>
                    </div>
                    
                    <div class="profile-info">
                        <h3><?= htmlspecialchars($user_data['vards'] . ' ' . $user_data['uzvards']) ?></h3>
                        <p class="role-badge"><?= ucfirst($user_data['loma']) ?></p>
                        <p class="username">@<?= htmlspecialchars($user_data['lietotajvards']) ?></p>
                    </div>
                </div>

                <!-- Profile Form -->
                <form id="profile-form" method="POST" action="db/profila_redigesana.php" enctype="multipart/form-data">
                    <div class="profile-info-grid">
                        <div class="form-group">
                            <label for="vards">Vārds:</label>
                            <input type="text" id="vards" name="vards" value="<?= htmlspecialchars($user_data['vards']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="uzvards">Uzvārds:</label>
                            <input type="text" id="uzvards" name="uzvards" value="<?= htmlspecialchars($user_data['uzvards']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="lietotajvards">Lietotājvārds:</label>
                            <input type="text" id="lietotajvards" name="lietotajvards" value="<?= htmlspecialchars($user_data['lietotajvards']) ?>" readonly>
                            <small>Lietotājvārds nav maināms drošības nolūkos</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="epasts">E-pasts:</label>
                            <input type="email" id="epasts" name="epasts" value="<?= htmlspecialchars($user_data['epasts']) ?>" required>
                        </div>
                    </div>

                    <div class="form-buttons" id="profile-buttons">
                        <button type="submit" name="saglabat" class="btn">
                            <i class="fas fa-save"></i> Saglabāt izmaiņas
                        </button>
                        <button type="button" onclick="resetForm()" class="btn clear-btn">
                            <i class="fas fa-undo"></i> Atjaunot
                        </button>
                    </div>
                </form>
            </div>

     

            


        <!-- Password Change Section -->
        <div class="dashboard-card">
            <h2><i class="fas fa-lock"></i> Paroles maiņa</h2>
            
            <form id="password-form" method="POST" action="db/paroles_maina.php">
                <div class="password-grid">
                    <div class="form-group">
                        <label for="current_password">Pašreizējā parole:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Jaunā parole:</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8">
                        <small>Minimums 8 simboli</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Apstiprināt jauno paroli:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="change_password" class="btn">
                        <i class="fas fa-key"></i> Mainīt paroli
                    </button>
                </div>
            </form>


        </div>
    </section>
</main>

<style>
/* Profile-specific styles */
.profile-card {
    grid-column: 1 / -1;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 2rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid var(--light3);
}

.profile-avatar {
    position: relative;
    width: 120px;
    height: 120px;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--light3);
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: var(--light3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: var(--maincolor);
    border: 4px solid var(--light3);
}

.avatar-upload {
    position: absolute;
    bottom: 0;
    right: 0;
    background: var(--maincolor);
    border-radius: 50%;
    padding: 0.5rem;
}

.upload-label {
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.profile-info h3 {
    margin: 0 0 0.5rem 0;
    color: var(--tumsa);
    font-size: 1.8rem;
}

.username {
    color: var(--text);
    font-size: 1.1rem;
    margin: 0.5rem 0;
}

.profile-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.password-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-group input[readonly] {
    background-color: var(--light3);
    cursor: not-allowed;
    border: 2px solid var(--light1);
}

.form-group input:not([readonly]) {
    background-color: white;
    border: 2px solid #e0e0e0;
    transition: border-color 0.3s ease;
}

.form-group input:not([readonly]):focus {
    border-color: var(--maincolor);
    box-shadow: 0 0 0.5rem rgba(3, 135, 206, 0.5);
    outline: none;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-info {
    padding: 1rem;
    background: var(--light3);
    border-radius: 0.5rem;
}

.stat-info h3 {
    margin: 0 0 0.5rem 0;
    color: var(--tumsa);
    font-size: 1rem;
    font-weight: 600;
}

.stat-info p {
    margin: 0;
    color: var(--text);
    font-size: 1.1rem;
}

.stat-info small {
    color: #666;
    font-size: 0.9rem;
    display: block;
    margin-top: 0.3rem;
}

.status-active {
    color: #28a745 !important;
    font-weight: 600;
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

.material-stats {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--light3);
}

.material-breakdown {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.material-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--light3);
    border-radius: 0.5rem;
}

.material-label {
    font-weight: 500;
    color: var(--tumsa);
}

.material-count {
    font-weight: bold;
    color: var(--maincolor);
    font-size: 1.2rem;
}

.material-link {
    color: var(--maincolor);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s;
}

.material-link:hover {
    color: var(--tumsa);
}

.logout-btn {
    background: #dc3545 !important;
}

.logout-btn:hover {
    background: #c82333 !important;
}

.notification-message {
    background: var(--light2);
    border: 1px solid var(--light1);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 2rem;
    color: var(--tumsa);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .profile-info-grid,
    .password-grid {
        grid-template-columns: 1fr;
    }
    
    .material-breakdown {
        grid-template-columns: 1fr;
    }
    
    .material-item {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
}
</style>

<?php $savienojums->close(); ?>