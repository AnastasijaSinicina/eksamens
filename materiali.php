<?php
session_start();
// Set redirect BEFORE checking login status
$_SESSION['redirect_after_login'] = "materiali.php";

// Check if user is logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai izveidotu pielāgotu produktu";
    header("Location: login.php");
    exit();
}

// Include database connection
require "admin/db/con_db.php";

// Get current user info
$username = $_SESSION['lietotajvardsSIN'];
$user_query = "SELECT * FROM lietotaji_sparkly WHERE lietotajvards = ?";
$user_stmt = $savienojums->prepare($user_query);
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Rest of your code remains the same...
if (isset($_POST['submit_custom_order'])) {
    // Define required fields
    $required_fields = [
        'forma' => 'Forma',
        'audums' => 'Audums',
        'malu_figura' => 'Mālu figūra',
        'dekorejums1' => 'Dekorējums 1',
        'vards' => 'Vārds',
        'uzvards' => 'Uzvārds',
        'epasts' => 'E-pasts',
        'talrunis' => 'Tālrunis',
        'adrese' => 'Adrese',
        'pilseta' => 'Pilsēta',
        'pasta_indekss' => 'Pasta indekss',
        'daudzums' => 'Daudzums'
    ];
    
    $errors = [];
    
    // Validate required fields
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field]) || $_POST[$field] === '') {
            $errors[] = "Lauks '$label' ir obligāts";
        }
    }
    
    // Validate email format
    if (!empty($_POST['epasts']) && !filter_var($_POST['epasts'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Nepareizs e-pasta formāts";
    }
    
    // Validate quantity is positive number
    if (!empty($_POST['daudzums']) && (int)$_POST['daudzums'] < 1) {
        $errors[] = "Daudzums jābūt vismaz 1";
    }
    
    // If there are validation errors, display them
    if (!empty($errors)) {
        $error_message = "Lūdzu izlabojiet šādas kļūdas:<br>• " . implode("<br>• ", $errors);
    } else {
        // Include the custom order insertion file
        require "admin/db/spec_pas.php";
        
        // Prepare data for insertion
        $order_data = [
            'vards' => htmlspecialchars($_POST['vards']),
            'uzvards' => htmlspecialchars($_POST['uzvards']),
            'epasts' => htmlspecialchars($_POST['epasts']),
            'talrunis' => htmlspecialchars($_POST['talrunis']),
            'adrese' => htmlspecialchars($_POST['adrese']),
            'pilseta' => htmlspecialchars($_POST['pilseta']),
            'pasta_indekss' => htmlspecialchars($_POST['pasta_indekss']),
            'forma' => htmlspecialchars($_POST['forma']),
            'audums' => htmlspecialchars($_POST['audums']),
            'malu_figura' => htmlspecialchars($_POST['malu_figura'] ?? ''),
            'dekorejums1' => htmlspecialchars($_POST['dekorejums1'] ?? ''),
            'daudzums' => intval($_POST['daudzums']),
            'piezimes' => htmlspecialchars($_POST['piezimes'])
        ];
        
        // Insert the order
        $result = insertCustomOrder($user['id_lietotajs'], $order_data);
        
        // Replace this section in your materiali.php file:

        if ($result['success']) {
            $_SESSION['pazinojums'] = "Jūsu pielāgotā produkta pieprasījums ir veiksmīgi nosūtīts! Mēs sazināsimies ar jums drīzumā.";
            
            // Redirect to profile page with orders tab active
            header("Location: profils.php?tab=orders&success=1");
            exit();
        } else {
            $error_message = "Kļūda nosūtot pieprasījumu: " . $result['error'];
        }
    }
}

require_once 'admin/db/materiali_spec.php';
include 'header.php';
?>
<section id="materiali">
    <h1>Izveidojiet savu pielāgoto produktu</h1>
    
    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>
    
    <div class="custom-product-container">
        <div class="form-section">
            <h2>Produkta specifikācijas</h2>
            <p class="form-description">
                Izvēlieties materiālus un dizainu savam pielāgotajam produktam. 
                Jūs varat izvēlēties no mūsu pieejamajiem materiāliem vai aprakstīt savas vēlmes.
            </p>
            
            <form id="custom-product-form" method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" enctype="multipart/form-data">
                
                <!-- Product Specifications -->
                <div class="form-group">
                    <label for="forma">Forma*:</label>
                    <select id="forma" name="forma">
                        <option value="">Izvēlieties formu</option>
                        <?php foreach ($formas as $forma): ?>
                            <option value="<?= $forma['id_forma'] ?>"><?= htmlspecialchars($forma['forma']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="audums">Audums*:</label>
                    <select id="audums" name="audums">
                        <option value="">Izvēlieties audumu</option>
                        <?php foreach ($audumi as $audums): ?>
                            <option value="<?= $audums['id_audums'] ?>"><?= htmlspecialchars($audums['nosaukums']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Malu figura with images -->
                <div class="form-group">
                    <label for="malu_figura">Malu figūra*:</label>
                    <select id="malu_figura" name="malu_figura" onchange="updateImageDisplay('malu_figura')">
                        <option value="">Izvēlieties malu figūru</option>
                        <?php foreach ($malu_figuras as $figura): ?>
                            <option value="<?= $figura['id_malu_figura'] ?>" 
                                    data-image="<?= !empty($figura['attels']) ? base64_encode($figura['attels']) : '' ?>">
                                <?= htmlspecialchars($figura['nosaukums']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="malu_figura_image" class="material-image-preview" style="display: none;">
                        <img src="" alt="Malu figūra" />
                    </div>
                </div>
                
                <!-- Dekorejums 1 with images -->
                <div class="form-group">
                    <label for="dekorejums1">Dekorējums 1*:</label>
                    <select id="dekorejums1" name="dekorejums1" onchange="updateImageDisplay('dekorejums1')">
                        <option value="">Izvēlieties dekorējumu</option>
                        <?php foreach ($dekorejumi1 as $dekorejums): ?>
                            <option value="<?= $dekorejums['id_dekorejums1'] ?>" 
                                    data-image="<?= !empty($dekorejums['attels']) ? base64_encode($dekorejums['attels']) : '' ?>">
                                <?= htmlspecialchars($dekorejums['nosaukums']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="dekorejums1_image" class="material-image-preview" style="display: none;">
                        <img src="" alt="Dekorējums 1" />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="daudzums">Daudzums*:</label>
                    <input type="number" id="daudzums" name="daudzums" min="1" value="1" required>
                </div>
                
                <!-- Contact Information -->
                <h2>Kontaktinformācija</h2>
                
                <div class="form-group">
                    <label for="vards">Vārds*:</label>
                    <input type="text" id="vards" name="vards" value="<?= htmlspecialchars($user['vards'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="uzvards">Uzvārds*:</label>
                    <input type="text" id="uzvards" name="uzvards" value="<?= htmlspecialchars($user['uzvards'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="epasts">E-pasts*:</label>
                    <input type="email" id="epasts" name="epasts" value="<?= htmlspecialchars($user['epasts'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="talrunis">Tālrunis*:</label>
                    <input type="text" id="talrunis" name="talrunis" value="<?= htmlspecialchars($user['talrunis'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="adrese">Adrese*:</label>
                    <input type="text" id="adrese" name="adrese" value="<?= htmlspecialchars($user['adrese'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="pilseta">Pilsēta*:</label>
                    <input type="text" id="pilseta" name="pilseta" value="<?= htmlspecialchars($user['pilseta'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="pasta_indekss">Pasta indekss*:</label>
                    <input type="text" id="pasta_indekss" name="pasta_indekss" value="<?= htmlspecialchars($user['pasta_indekss'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="piezimes">Papildu piezīmes:</label>
                    <textarea id="piezimes" name="piezimes" rows="4" placeholder="Jebkādas papildu piezīmes par jūsu pieprasījumu"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="submit_custom_order" class="btn btn-primary">Nosūtīt pieprasījumu</button>
                    <a href="produkcija.php" class="btn btn-secondary">Atgriezties pie produktiem</a>
                </div>
            </form>
        </div>
        
        <div class="info-section">
            <h2>Kā tas darbojas?</h2>
            <div class="info-card">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Aizpildiet formu</h3>
                        <p>Izvēlieties materiālus un aprakstiet savu vēlamo produktu</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Mēs sazināsimies</h3>
                        <p>Mūsu komanda sazināsies ar Jums, kad noteiksim cenu un termiņu</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Jūsu apstiprinājums</h3>
                        <p>Pēc individuālā cenas aprēķina mēs ar Jums sazināsimies apstiprināšanai</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Izgatavošana</h3>
                        <p>Pēc apstiprināšanas sāksim jūsu produkta izgatavošanu</p>
                    </div>
                </div>
            </div>
            
            <div class="info-note">
                <h3>Svarīga informācija:</h3>
                <ul>
                    <li>Pielāgotos produktus nav iespējams atgriezt</li>
                    <li>Izgatavošanas laiks: 7-14 darba dienas</li>
                    <li>Cena tiks aprēķināta individuāli</li>
                    <li>Apmaksa pēc cenas apstiprināšanas</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<style>

</style>

<script>

// Function to update image display when material is selected
function updateImageDisplay(selectId) {
    const select = document.getElementById(selectId);
    const imageContainer = document.getElementById(selectId + '_image');
    const img = imageContainer.querySelector('img');
    
    const selectedOption = select.options[select.selectedIndex];
    const imageData = selectedOption.getAttribute('data-image');
    
    if (imageData && imageData !== '') {
        img.src = 'data:image/jpeg;base64,' + imageData;
        imageContainer.style.display = 'block';
    } else {
        imageContainer.style.display = 'none';
    }
}
</script>

<?php
// Close database connection
$savienojums->close();
// Include footer
include 'footer.php';
?>