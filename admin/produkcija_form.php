<?php
// Uzsāk sesiju paša sākumā
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pārbauda, vai lietotājs ir pieteicies un ir admin vai moderators
if (!isset($_SESSION['lietotajvardsSIN']) || ($_SESSION['loma'] !== 'admin' && $_SESSION['loma'] !== 'moder')) {
    header("Location: ../login.php");
    exit();
}

// admin/produkcija_form.php
// Produktu formas lapa produktu pievienošanai un rediģēšanai

require 'header.php';

// Inicializē mainīgos
$editData = null;
$success_message = '';
$error_message = '';
$redirect_url = '';

// Nosaka, vai mēs rediģējam vai pievienojam
$is_editing = isset($_GET['edit']) && !empty($_GET['edit']);
$page_title = $is_editing ? 'Rediģēt produktu' : 'Pievienot jaunu produktu';

// Apstrādā formas iesniegšanu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_editing || isset($_POST['id'])) {
        require 'db/produkcija_edit.php';
    } else {
        require 'db/produkcija_add.php';
    }
}

// Iegūst produkta datus rediģēšanai
if ($is_editing) {
    $_GET['id'] = $_GET['edit']; // Pārliecinās, ka rediģēšanas skripts iegūst pareizo ID
    require 'db/produkcija_edit.php';
}

// Ielādē nolaižamo izvēlni opcijas
require 'db/produkcija_admin.php';
?>

<main>
    <!-- Paziņojumu konteiners -->
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
        
        <!-- Produkta forma -->
        <div class="custom-form-container">
            <form class="custom-form" method="POST" enctype="multipart/form-data">
                <!-- Pievieno slēpto ID lauku rediģēšanai -->
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id_bumba']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <!-- Formas izvēle -->
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
                    
                    <!-- Produkta nosaukums -->
                    <div class="form-group">
                        <label for="nosaukums">Nosaukums: <span class="required">*</span></label>
                        <input type="text" id="nosaukums" name="nosaukums" 
                               value="<?php echo $editData ? htmlspecialchars($editData['nosaukums']) : ''; ?>" 
                               required maxlength="255">
                    </div>
                    
                    <!-- Auduma izvēle -->
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
                    
                    <!-- Malu figūras izvēle -->
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
                    
                    <!-- Dekorējuma 1 izvēle -->
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
                    
                    <!-- Cena -->
                    <div class="form-group">
                        <label for="cena">Cena (€): <span class="required">*</span></label>
                        <input type="number" id="cena" name="cena" step="0.01" min="0.01" 
                               value="<?php echo $editData ? $editData['cena'] : ''; ?>" required>
                    </div>
                </div>
                
                <!-- Attēlu augšupielāde - tikai jauniem produktiem vai ja rediģējot vēlas mainīt attēlus -->
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
                
                <!-- Metadatu informācija rediģēšanai -->
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
                            // Parāda izveidotāja vārdu
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
                            // Parāda redaktora vārdu
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
                
                <!-- Formas pogas -->
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

<!-- JavaScript paziņojumiem un formas validācijai -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Parāda paziņojumus, ja tie eksistē
    <?php if (isset($success_message) && !empty($success_message)): ?>
        showNotification('success', 'Veiksmīgi!', '<?php echo addslashes($success_message); ?>');
        <?php if (isset($redirect_url) && !empty($redirect_url)): ?>
            setTimeout(() => {
                window.location.href = '<?php echo $redirect_url; ?>';
            }, 2000);
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($error_message) && !empty($error_message)): ?>
        showNotification('error', 'Kļūda!', '<?php echo addslashes($error_message); ?>');
    <?php endif; ?>
});

function showNotification(type, title, message) {
    const container = document.querySelector('.notification-container');
    const notification = document.querySelector('.notification');
    const icon = notification.querySelector('i');
    const titleElement = notification.querySelector('h3');
    const messageElement = notification.querySelector('p');
    
    // Iestata paziņojuma saturu
    icon.className = type === 'success' ? 'fas fa-check-circle success' : 'fas fa-exclamation-circle error';
    titleElement.textContent = title;
    messageElement.textContent = message;
    
    // Pievieno klasi atkarībā no tipa
    notification.className = 'notification ' + type;
    
    // Parāda paziņojumu
    container.style.display = 'block';
    
    // Paslēpj pēc 5 sekundēm
    setTimeout(() => {
        container.style.display = 'none';
    }, 5000);
}
</script>