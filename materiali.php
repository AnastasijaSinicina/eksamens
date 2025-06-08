<?php
session_start();
// Iestatīta pāradresācija PIRMS pieteikšanās statusa pārbaudes
$_SESSION['redirect_after_login'] = "materiali.php";

// Pārbauda vai lietotājs ir ielogojies
if (!isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['pazinojums'] = "Lūdzu ielogojieties, lai izveidotu pielāgotu produktu";
    header("Location: login.php");
    exit();
}

// Iekļauj datubāzes savienojumu
require "admin/db/con_db.php";

// Iekļauj pielāgotā pasūtījuma apstrādes failu (satur visus SQL vaicājumus)
require "admin/db/spec_pas.php";

// Iekļauj materiālu datus
require 'admin/db/materiali_spec.php';
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
                
                <!-- Produkta specifikācijas -->
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
                
                <!-- Malu figūra ar attēliem -->
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
                
                <!-- Dekorējums 1 ar attēliem -->
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
                
                <!-- Kontaktinformācija -->
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

// Funkcija lai atjauninātu attēla parādīšanu kad materiāls ir izvēlēts
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
// Aizvēr datubāzes savienojumu
$savienojums->close();
// Iekļauj footer
include 'footer.php';
?>