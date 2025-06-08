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
                        <?php if (!empty($user_data['foto'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($user_data['foto']) ?>" alt="Profila attēls" id="profile-image-display">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="avatar-upload" id="avatar-upload">
                            <label for="profile-image-input" class="upload-label">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>
                    </div>
                    
                    <div class="profile-info">
                        <h3><?= htmlspecialchars($user_data['vards'] . ' ' . $user_data['uzvards']) ?></h3>
                        <p class="role-badge"><?= ucfirst($user_data['loma']) ?></p>
                        <p class="username">@<?= htmlspecialchars($user_data['lietotajvards']) ?></p>
                        <?php if (!empty($user_data['foto'])): ?>
                            <button type="button" onclick="deleteProfileImage()" class="btn btn-sm delete-image-btn">
                                <i class="fas fa-trash"></i> Dzēst attēlu
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Profile Form -->
                <form id="profile-form" method="POST" action="db/profila_redigesana.php" enctype="multipart/form-data">
                    <!-- Hidden file input for image upload -->
                    <input type="file" id="profile-image-input" name="profile_image" accept="image/*" style="display: none;" onchange="handleImageSelect(this)">
                    
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
                
                <!-- Hidden form for image deletion -->
                <form id="delete-image-form" method="POST" action="db/profila_redigesana.php" style="display: none;">
                    <input type="hidden" name="delete_image" value="1">
                </form>
            </div>

            <!-- Password Change Card -->
            <div class="dashboard-card password-card">
                <h2><i class="fas fa-key"></i> Paroles maiņa</h2>
                
                <form id="password-form" method="POST" action="db/paroles_maina.php">
                    <div class="password-grid">
                        <div class="form-group">
                            <label for="current_password">Pašreizējā parole:</label>
                            <div class="password-input-container">
                                <input type="password" id="current_password" name="current_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye" id="current_password_icon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Jaunā parole:</label>
                            <div class="password-input-container">
                                <input type="password" id="new_password" name="new_password" required minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye" id="new_password_icon"></i>
                                </button>
                            </div>
                            <small class="password-help">Parolei jābūt vismaz 8 simbolus garai</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Apstiprināt jauno paroli:</label>
                            <div class="password-input-container">
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye" id="confirm_password_icon"></i>
                                </button>
                            </div>
                            <div id="password-match-message" class="password-match-message"></div>
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" name="change_password" class="btn" disabled id="change-password-btn">
                            <i class="fas fa-key"></i> Mainīt paroli
                        </button>
                        <button type="button" onclick="resetPasswordForm()" class="btn clear-btn">
                            <i class="fas fa-undo"></i> Notīrīt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
function handleImageSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Atļauti tikai JPEG, JPG, PNG un GIF formāta attēli!');
            input.value = '';
            return;
        }
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('Attēla izmērs nedrīkst pārsniegt 5MB!');
            input.value = '';
            return;
        }
        
        // Preview the image
        const reader = new FileReader();
        reader.onload = function(e) {
            const imageDisplay = document.getElementById('profile-image-display');
            const placeholder = document.querySelector('.avatar-placeholder');
            
            if (imageDisplay) {
                imageDisplay.src = e.target.result;
            } else if (placeholder) {
                // Replace placeholder with image
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Profila attēls';
                img.id = 'profile-image-display';
                placeholder.parentNode.replaceChild(img, placeholder);
            }
        };
        reader.readAsDataURL(file);
    }
}

function deleteProfileImage() {
    if (confirm('Vai tiešām vēlaties dzēst profila attēlu?')) {
        document.getElementById('delete-image-form').submit();
    }
}

function resetForm() {
    document.getElementById('profile-form').reset();
    document.getElementById('profile-image-input').value = '';
    
    // Reset image preview
    const imageDisplay = document.getElementById('profile-image-display');
    if (imageDisplay) {
        // Restore original image source from server data
        <?php if (!empty($user_data['foto'])): ?>
            imageDisplay.src = 'data:image/jpeg;base64,<?= base64_encode($user_data['foto']) ?>';
        <?php endif; ?>
    }
}

// Password functionality
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function validatePasswords() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const currentPassword = document.getElementById('current_password').value;
    const messageDiv = document.getElementById('password-match-message');
    const submitBtn = document.getElementById('change-password-btn');
    
    // Check if all fields are filled
    if (currentPassword.length === 0 || newPassword.length === 0 || confirmPassword.length === 0) {
        messageDiv.textContent = '';
        submitBtn.disabled = true;
        return;
    }
    
    // Check password length
    if (newPassword.length < 8) {
        messageDiv.textContent = 'Parolei jābūt vismaz 8 simbolus garai';
        messageDiv.className = 'password-match-message error';
        submitBtn.disabled = true;
        return;
    }
    
    // Check if passwords match
    if (newPassword === confirmPassword) {
        messageDiv.textContent = 'Paroles sakrīt ✓';
        messageDiv.className = 'password-match-message success';
        submitBtn.disabled = false;
    } else {
        messageDiv.textContent = 'Paroles nesakrīt';
        messageDiv.className = 'password-match-message error';
        submitBtn.disabled = true;
    }
}

function resetPasswordForm() {
    document.getElementById('password-form').reset();
    document.getElementById('password-match-message').textContent = '';
    document.getElementById('change-password-btn').disabled = true;
    
    // Reset all password visibility icons
    const icons = ['current_password_icon', 'new_password_icon', 'confirm_password_icon'];
    icons.forEach(iconId => {
        const icon = document.getElementById(iconId);
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    });
    
    // Reset password field types
    document.getElementById('current_password').type = 'password';
    document.getElementById('new_password').type = 'password';
    document.getElementById('confirm_password').type = 'password';
}

// Add event listeners for password validation
document.getElementById('new_password').addEventListener('input', validatePasswords);
document.getElementById('confirm_password').addEventListener('input', validatePasswords);
document.getElementById('current_password').addEventListener('input', validatePasswords);
</script>

<style>
/* Profile-specific styles */
.profile-card {
    grid-column: 1 / -1;
}

.password-card {
    grid-column: 1 / -1;
    margin-top: 1rem;
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

.password-input-container {
    position: relative;
}

.password-input-container input {
    width: 100%;
    padding-right: 3rem;
}

.password-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text);
    cursor: pointer;
    font-size: 1rem;
    padding: 0.25rem;
}

.password-toggle:hover {
    color: var(--maincolor);
}

.password-help {
    color: #666;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}

.password-match-message {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    font-weight: 500;
}

.password-match-message.success {
    color: #28a745;
}

.password-match-message.error {
    color: #dc3545;
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

.delete-image-btn {
    background: #dc3545 !important;
    color: white;
    font-size: 0.8rem;
    padding: 0.3rem 0.6rem;
    margin-top: 0.5rem;
}

.delete-image-btn:hover {
    background: #c82333 !important;
}

.avatar-upload:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

#profile-image-display {
    cursor: pointer;
    transition: opacity 0.3s ease;
}

#profile-image-display:hover {
    opacity: 0.8;
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
}
</style>

<?php $savienojums->close(); ?>