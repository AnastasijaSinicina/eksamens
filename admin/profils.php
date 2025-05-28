<?php
    // Include admin header
    require 'header.php';
    
    // Database connection
    require 'db/con_db.php';

    // Initialize variables
    $notification = null;
    $currentUser = null;

    // Get current user data
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $sql = "SELECT * FROM lietotaji_sparkly WHERE id_lietotajs = ? AND (loma = 'admin' OR loma = 'moder')";
        $stmt = $savienojums->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $currentUser = $result->fetch_assoc();
        } else {
            // Redirect if user not found or unauthorized
            header("Location: login.php");
            exit();
        }
        $stmt->close();
    } else {
        // Redirect if not logged in
        header("Location: login.php");
        exit();
    }

    // Handle profile update
    if (isset($_POST['update_profile'])) {
        $vards = trim($_POST['vards']);
        $uzvards = trim($_POST['uzvards']);
        $lietotajvards = trim($_POST['lietotajvards']);
        $epasts = trim($_POST['epasts']);
        
        // Validate input
        if (empty($vards) || empty($uzvards) || empty($lietotajvards) || empty($epasts)) {
            $notification = ['type' => 'error', 'title' => 'Kļūda!', 'message' => 'Visi lauki ir obligāti!'];
        } else {
            // Check if username or email already exists (excluding current user)
            $checkSql = "SELECT id_lietotajs FROM lietotaji_sparkly WHERE (lietotajvards = ? OR epasts = ?) AND id_lietotajs != ?";
            $checkStmt = $savienojums->prepare($checkSql);
            $checkStmt->bind_param("ssi", $lietotajvards, $epasts, $userId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $notification = ['type' => 'error', 'title' => 'Kļūda!', 'message' => 'Lietotājvārds vai e-pasts jau eksistē!'];
            } else {
                // Update user profile
                $updateSql = "UPDATE lietotaji_sparkly SET vards = ?, uzvards = ?, lietotajvards = ?, epasts = ? WHERE id_lietotajs = ?";
                $updateStmt = $savienojums->prepare($updateSql);
                $updateStmt->bind_param("ssssi", $vards, $uzvards, $lietotajvards, $epasts, $userId);
                
                if ($updateStmt->execute()) {
                    $notification = ['type' => 'success', 'title' => 'Veiksmīgi!', 'message' => 'Profils ir veiksmīgi atjaunināts!'];
                    // Update current user data
                    $currentUser['vards'] = $vards;
                    $currentUser['uzvards'] = $uzvards;
                    $currentUser['lietotajvards'] = $lietotajvards;
                    $currentUser['epasts'] = $epasts;
                    // Update session username if it exists
                    if (isset($_SESSION['lietotajvards'])) {
                        $_SESSION['lietotajvards'] = $lietotajvards;
                    }
                } else {
                    $notification = ['type' => 'error', 'title' => 'Kļūda!', 'message' => 'Neizdevās atjaunināt profilu: ' . $updateStmt->error];
                }
                $updateStmt->close();
            }
            $checkStmt->close();
        }
    }

    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate input
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $notification = ['type' => 'error', 'title' => 'Kļūda!', 'message' => 'Visi paroles lauki ir obligāti!'];
        } elseif ($new_password !== $confirm_password) {
            $notification = ['type' => 'error', 'title' => 'Kļūda!', 'message' => 'Jaunā parole un apstiprinājums nesakrīt!'];
        } elseif (strlen($new_password) < 6) {
            $notification = ['type' => 'error', 'title' => 'Kļūda!', 'message' => 'Jaunajai parolei jābūt vismaz 6 simbolu garai!'];
        } else {
            // Verify current password
            if (password_verify($current_password, $currentUser['parole'])) {
                // Hash new password and update
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $updatePassSql = "UPDATE lietotaji_sparkly SET parole = ? WHERE id_lietotajs = ?";
                $updatePassStmt = $savienojums->prepare($updatePassSql);
                $updatePassStmt->bind_param("si", $hashedPassword, $userId);
                
                if ($updatePassStmt->execute()) {
                    $notification = ['type' => 'success', 'title' => 'Veiksmīgi!', 'message' => 'Parole ir veiksmīgi nomainīta!'];
                    $currentUser['parole'] = $hashedPassword;
                } else {
                    $notification = ['type' => 'error', 'title' => 'Kļūda!', 'message' => 'Neizdevās nomainīt paroli: ' . $updatePassStmt->error];
                }
                $updatePassStmt->close();
            } else {
                $notification = ['type' => 'error', 'title' => 'Kļūda!', 'message' => 'Nepareiza esošā parole!'];
            }
        }
    }
?>

<main>
    <!-- Notification container -->
    <?php if ($notification): ?>
    <div class="notification-container" style="display: block;">
        <div class="notification <?php echo $notification['type']; ?>">
            <i class="fas <?php echo $notification['type'] === 'success' ? 'fa-check-circle success' : 'fa-exclamation-circle error'; ?>"></i>
            <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
            <p><?php echo htmlspecialchars($notification['message']); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <section class="admin-content">
        <h1>Profila pārvaldība</h1>
        
        <!-- Profile Information Section -->
        <div class="custom-form-container">
            <h2>Profila informācija</h2>
            <form class="custom-form" method="POST">
                <div class="profile-info-grid">
                    <div class="form-group">
                        <label for="vards">Vārds:</label>
                        <input type="text" id="vards" name="vards" value="<?php echo htmlspecialchars($currentUser['vards']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="uzvards">Uzvārds:</label>
                        <input type="text" id="uzvards" name="uzvards" value="<?php echo htmlspecialchars($currentUser['uzvards']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lietotajvards">Lietotājvārds:</label>
                        <input type="text" id="lietotajvards" name="lietotajvards" value="<?php echo htmlspecialchars($currentUser['lietotajvards']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="epasts">E-pasts:</label>
                        <input type="email" id="epasts" name="epasts" value="<?php echo htmlspecialchars($currentUser['epasts']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="loma">Loma:</label>
                        <input type="text" id="loma" name="loma" value="<?php echo ucfirst($currentUser['loma']); ?>" readonly disabled>
                        <small>Lomu var mainīt tikai cits administrators</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="registracijas_datums">Reģistrācijas datums:</label>
                        <input type="text" id="registracijas_datums" name="registracijas_datums" 
                               value="<?php echo isset($currentUser['registracijas_datums']) ? date('d.m.Y H:i', strtotime($currentUser['registracijas_datums'])) : 'Nav pieejams'; ?>" 
                               readonly disabled>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="update_profile" class="btn">Atjaunināt profilu</button>
                </div>
            </form>
        </div>
        
        <!-- Password Change Section -->
        <div class="custom-form-container">
            <h2>Paroles maiņa</h2>
            <form class="custom-form" method="POST">
                <div class="password-grid">
                    <div class="form-group">
                        <label for="current_password">Esošā parole:</label>
                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Jaunā parole:</label>
                        <input type="password" id="new_password" name="new_password" required autocomplete="new-password" minlength="6">
                        <small>Parolei jābūt vismaz 6 simbolu garai</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Apstiprināt jauno paroli:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" minlength="6">
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="change_password" class="btn">Nomainīt paroli</button>
                </div>
            </form>
        </div>
        
        <!-- Profile Statistics Section -->
        <div class="custom-form-container">
            <h2>Profila statistika</h2>
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Lietotāja tips</h3>
                        <p><?php echo ucfirst($currentUser['loma']); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pievienošanās</h3>
                        <p><?php echo isset($currentUser['registracijas_datums']) ? date('d.m.Y', strtotime($currentUser['registracijas_datums'])) : 'Nav pieejams'; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pēdējā aktivitāte</h3>
                        <p>Aktīvs tagad</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Konts</h3>
                        <p class="status-active">Aktīvs</p>
                    </div>
                </div>
            </div>
        </div>
        
    </section>
</main>

<!-- JavaScript for notifications and form validation -->
<script>
    // Auto-hide notifications after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const notificationContainer = document.querySelector('.notification-container');
        if (notificationContainer && notificationContainer.style.display === 'block') {
            setTimeout(() => {
                notificationContainer.style.display = 'none';
            }, 5000);
        }
        
        // Password confirmation validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        function validatePasswords() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                confirmPasswordInput.setCustomValidity('Paroles nesakrīt');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }
        
        if (newPasswordInput && confirmPasswordInput) {
            newPasswordInput.addEventListener('input', validatePasswords);
            confirmPasswordInput.addEventListener('input', validatePasswords);
        }
    });
    
    // Show/hide password toggle functionality
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling;
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<style>
    /* Profile-specific styles */
    .profile-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .password-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .profile-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    
    .stat-card {
        background-color: var(--light2);
        border-radius: 0.5rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--box-shadow2);
    }
    
    .stat-icon {
        background-color: var(--maincolor);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
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
    
    .status-active {
        color: #28a745 !important;
        font-weight: 600;
    }
    
    /* Form styling for profile */
    .custom-form input[readonly], 
    .custom-form input[disabled] {
        background-color: var(--light3);
        cursor: not-allowed;
    }
    
    .password-input-container {
        position: relative;
    }
    
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--text);
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .profile-info-grid,
        .password-grid {
            grid-template-columns: 1fr;
        }
        
        .profile-stats {
            grid-template-columns: 1fr;
        }
        
        .stat-card {
            padding: 1rem;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }
    }
</style>