<?php
// Start session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default user if no session exists
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Admin';
}

// admin/produkcija_form.php
// Product form page for adding and editing products

require 'header.php';

// Determine if we're editing or adding
$is_editing = isset($_GET['edit']) && !empty($_GET['edit']);
$page_title = $is_editing ? 'Rediģēt produktu' : 'Pievienot jaunu produktu';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_editing || isset($_POST['id'])) {
        require 'db/produkcija_edit.php';
    } else {
        require 'db/produkcija_add.php';
    }
}

// Get product data for editing or load dropdown options
if ($is_editing) {
    $_GET['id'] = $_GET['edit'];
    require 'db/produkcija_edit.php';
}

// Load dropdown options
require 'db/produkcija_admin.php';
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
            <h1><?php echo $page_title; ?></h1>
            <a href="produkcija.php" class="btn back-btn">
                <i class="fas fa-arrow-left"></i> Atpakaļ uz sarakstu
            </a>
        </div>
        
        <!-- Product Form -->
        <div class="custom-form-container">
            <form class="custom-form" method="POST" enctype="multipart/form-data">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id_bumba']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <!-- Form selection -->
                    <div class="form-group">
                        <label for="forma">Forma: <span class="required">*</span></label>
                        <?php if (isset($formas_table_exists) && $formas_table_exists && !empty($formas_options)): ?>
                            <select id="forma" name="forma" required>
                                <option value="">Izvēlieties formu</option>
                                <?php foreach ($formas_options as $forma_option): ?>
                                    <option value="<?php echo $forma_option['id_forma']; ?>" 
                                            <?php echo ($editData && $editData['forma'] == $forma_option['id_forma']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($forma_option['forma']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="number" id="forma" name="forma" min="1"
                                   value="<?php echo $editData ? $editData['forma'] : ''; ?>" required>
                            <small>Ievadiet formas ID (skaitlis)</small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product name -->
                    <div class="form-group">
                        <label for="nosaukums">Nosaukums: <span class="required">*</span></label>
                        <input type="text" id="nosaukums" name="nosaukums" 
                               value="<?php echo $editData ? htmlspecialchars($editData['nosaukums']) : ''; ?>" 
                               required maxlength="255">
                    </div>
                    
                    <!-- Fabric selection -->
                    <div class="form-group">
                        <label for="audums_id">Audums: <span class="required">*</span></label>
                        <?php if (isset($audumi_table_exists) && $audumi_table_exists && !empty($audums_options)): ?>
                            <select id="audums_id" name="audums_id" required>
                                <option value="">Izvēlieties audumu</option>
                                <?php foreach ($audums_options as $audums_option): ?>
                                    <option value="<?php echo $audums_option['id_audums']; ?>" 
                                            <?php echo ($editData && $editData['audums_id'] == $audums_option['id_audums']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($audums_option['nosaukums']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="number" id="audums_id" name="audums_id" min="1"
                                   value="<?php echo $editData ? $editData['audums_id'] : ''; ?>" required>
                            <small>Ievadiet auduma ID (skaitlis)</small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Edge figure selection -->
                    <div class="form-group">
                        <label for="figura_id">Malu figūra: <span class="required">*</span></label>
                        <?php if (isset($malu_figura_table_exists) && $malu_figura_table_exists && !empty($malu_figura_options)): ?>
                            <select id="figura_id" name="figura_id" required>
                                <option value="">Izvēlieties malu figūru</option>
                                <?php foreach ($malu_figura_options as $figura_option): ?>
                                    <option value="<?php echo $figura_option['id_malu_figura']; ?>" 
                                            <?php echo ($editData && $editData['figura_id'] == $figura_option['id_malu_figura']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($figura_option['nosaukums']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="number" id="figura_id" name="figura_id" min="1"
                                   value="<?php echo $editData ? $editData['figura_id'] : ''; ?>" required>
                            <small>Ievadiet malu figūras ID (skaitlis)</small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Decoration 1 selection -->
                    <div class="form-group">
                        <label for="dekorejums1_id">Dekorējums 1: <span class="required">*</span></label>
                        <?php if (isset($dekorejums1_table_exists) && $dekorejums1_table_exists && !empty($dekorejums1_options)): ?>
                            <select id="dekorejums1_id" name="dekorejums1_id" required>
                                <option value="">Izvēlieties dekorējumu</option>
                                <?php foreach ($dekorejums1_options as $dek1_option): ?>
                                    <option value="<?php echo $dek1_option['id_dekorejums1']; ?>" 
                                            <?php echo ($editData && $editData['dekorejums1_id'] == $dek1_option['id_dekorejums1']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dek1_option['nosaukums']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="number" id="dekorejums1_id" name="dekorejums1_id" min="1"
                                   value="<?php echo $editData ? $editData['dekorejums1_id'] : ''; ?>" required>
                            <small>Ievadiet dekorējuma 1 ID (skaitlis)</small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Decoration 2 selection -->
                    <div class="form-group">
                        <label for="dekorejums2_id">Dekorējums 2: <span class="required">*</span></label>
                        <?php if (isset($dekorejums2_table_exists) && $dekorejums2_table_exists && !empty($dekorejums2_options)): ?>
                            <select id="dekorejums2_id" name="dekorejums2_id" required>
                                <option value="">Izvēlieties dekorējumu</option>
                                <?php foreach ($dekorejums2_options as $dek2_option): ?>
                                    <option value="<?php echo $dek2_option['id_dekorejums2']; ?>" 
                                            <?php echo ($editData && $editData['dekorejums2_id'] == $dek2_option['id_dekorejums2']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dek2_option['nosaukums']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="number" id="dekorejums2_id" name="dekorejums2_id" min="1"
                                   value="<?php echo $editData ? $editData['dekorejums2_id'] : ''; ?>" required>
                            <small>Ievadiet dekorējuma 2 ID (skaitlis)</small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Price -->
                    <div class="form-group">
                        <label for="cena">Cena (€): <span class="required">*</span></label>
                        <input type="number" id="cena" name="cena" step="0.01" min="0.01" 
                               value="<?php echo $editData ? $editData['cena'] : ''; ?>" required>
                    </div>
                </div>
                
                <!-- Image uploads - only for adding new products or if editing and want to change images -->
                <?php if (!$editData): ?>
                <div class="image-upload-section">
                    <h3>Produkta attēli <span class="required">*</span></h3>
                    <div class="image-grid">
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="image-upload-group">
                            <label for="attels<?php echo $i; ?>">
                                Attēls <?php echo $i; ?>: <span class="required">*</span>
                            </label>
                            <input type="file" id="attels<?php echo $i; ?>" name="attels<?php echo $i; ?>" 
                                   accept="image/jpeg,image/jpg,image/png" required>
                            <small class="file-info">
                                Atbalstītie formāti: JPG, JPEG, PNG (maks. 5MB)
                            </small>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Metadata info for editing -->


<!-- Metadata info for editing -->
            <?php if ($editData): ?>
            <div class="metadata-section">
                <h3>Produkta informācija</h3>
                <div class="metadata-grid">
                    <div class="metadata-item">
                        <strong>Produkta ID:</strong> <?php echo $editData['id_bumba']; ?>
                    </div>
                    
                    <?php if (!empty($editData['izveidots_liet']) || !empty($editData['timestamp'])): ?>
                    <div class="metadata-item">
                        <strong>Izveidoja:</strong> 
                        <?php 
                        // Display creator name
                        if (!empty($editData['created_first_name']) && !empty($editData['created_last_name'])) {
                            echo htmlspecialchars($editData['created_first_name'] . ' ' . $editData['created_last_name']);
                            if (!empty($editData['created_username'])) {
                                echo ' (' . htmlspecialchars($editData['created_username']) . ')';
                            }
                        } elseif (!empty($editData['created_username'])) {
                            echo htmlspecialchars($editData['created_username']);
                        } else {
                            echo 'Lietotājs ID: ' . ($editData['izveidots_liet'] ?? 'Nav zināms');
                        }
                        ?>
                        <?php if (!empty($editData['timestamp'])): ?>
                            <br><small><?php echo date('d.m.Y H:i', strtotime($editData['timestamp'])); ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($editData['red_liet']) || !empty($editData['red_dat'])): ?>
                    <div class="metadata-item">
                        <strong>Pēdējoreiz rediģēja:</strong> 
                        <?php 
                        // Display editor name
                        if (!empty($editData['updated_first_name']) && !empty($editData['updated_last_name'])) {
                            echo htmlspecialchars($editData['updated_first_name'] . ' ' . $editData['updated_last_name']);
                            if (!empty($editData['updated_username'])) {
                                echo ' (' . htmlspecialchars($editData['updated_username']) . ')';
                            }
                        } elseif (!empty($editData['updated_username'])) {
                            echo htmlspecialchars($editData['updated_username']);
                        } else {
                            echo 'Lietotājs ID: ' . ($editData['red_liet'] ?? 'Nav zināms');
                        }
                        ?>
                        <?php if (!empty($editData['red_dat'])): ?>
                            <br><small><?php echo date('d.m.Y H:i', strtotime($editData['red_dat'])); ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
                
                <!-- Form buttons -->
                <div class="form-buttons">
                    <button type="submit" name="submit" class="btn submit-btn">
                        <i class="fas fa-save"></i>
                        <?php echo $editData ? 'Atjaunināt produktu' : 'Pievienot produktu'; ?>
                    </button>
                    <a href="produkcija.php" class="btn cancel-btn">
                        <i class="fas fa-times"></i>
                        Atcelt
                    </a>
                </div>
            </form>
        </div>
    </section>
</main>

<!-- Custom CSS for form styling -->
<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e0e0e0;
}

.back-btn {
    background-color: #6c757d;
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.back-btn:hover {
    background-color: #5a6268;
}

.custom-form-container {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #333;
}

.required {
    color: #dc3545;
}

.form-group input,
.form-group select {
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #007bff;
}

.form-group small {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.image-upload-section {
    margin: 2rem 0;
    padding: 1.5rem;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.image-upload-section h3 {
    margin-bottom: 1rem;
    color: #333;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.image-upload-group {
    display: flex;
    flex-direction: column;
}

.file-info {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.metadata-section {
    margin: 2rem 0;
    padding: 1.5rem;
    background-color: #e9ecef;
    border-radius: 8px;
}

.metadata-section h3 {
    margin-bottom: 1rem;
    color: #495057;
}

.metadata-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.metadata-item {
    background: white;
    padding: 1rem;
    border-radius: 5px;
    border-left: 4px solid #17a2b8;
}



</style>

<!-- JavaScript for notifications and form validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show notifications if they exist
    <?php if (isset($success_message)): ?>
        showNotification('success', 'Veiksmīgi!', '<?php echo addslashes($success_message); ?>');
        <?php if (isset($redirect_url)): ?>
            setTimeout(() => {
                window.location.href = '<?php echo $redirect_url; ?>';
            }, 2000);
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        showNotification('error', 'Kļūda!', '<?php echo addslashes($error_message); ?>');
    <?php endif; ?>
});

function showNotification(type, title, message) {
    const container = document.querySelector('.notification-container');
    const notification = document.querySelector('.notification');
    const icon = notification.querySelector('i');
    const titleElement = notification.querySelector('h3');
    const messageElement = notification.querySelector('p');
    
    // Set notification content
    icon.className = type === 'success' ? 'fas fa-check-circle success' : 'fas fa-exclamation-circle error';
    titleElement.textContent = title;
    messageElement.textContent = message;
    
    // Add class based on type
    notification.className = 'notification ' + type;
    
    // Show the notification
    container.style.display = 'block';
    
    // Hide after 5 seconds
    setTimeout(() => {
        container.style.display = 'none';
    }, 5000);
}
</script>