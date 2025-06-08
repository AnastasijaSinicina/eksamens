<?php
/**
 * Pasūtījuma apstiprināšanas lapa
 * Šī lapa parāda lietotājam pasūtījuma detaļas pēc veiksmīgas apstiprināšanas
 */

// Sākam sesiju
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pārbaudām vai lietotājs ir pieteicies
if (!isset($_SESSION['lietotajvardsSIN'])) {
    header("Location: login.php");
    exit();
}

// Pārbaudām vai ir sniegts pasūtījuma ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// Iegūstam pasūtījuma ID un konvertējam uz veselu skaitli
$order_id = intval($_GET['id']);

// Iekļaujam datu bāzes savienojumu
require "admin/db/con_db.php";

// Iegūstam visus nepieciešamos datus no datu bāzes
include "admin/db/pasutijuma_dati.php";

// Iekļaujam galveni
include 'header.php';
?>

<section id="pasutijums-apstiprinats">
    <div class="success-container">
        <!-- Veiksmīgas darbības ikona -->
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <!-- Galvenais virsraksts -->
        <h1>Pasūtījums pieņemts!</h1>
        
        <!-- Parādām pasūtījuma numuru -->
        <?php if (isset($pasutijums['pasutijuma_numurs'])): ?>
            <p>Jūsu pasūtījums Nr. <?= htmlspecialchars($pasutijums['pasutijuma_numurs']) ?> ir veiksmīgi pieņemts.</p>
        <?php endif; ?>
        
        <!-- Parādām paziņojumu, ja tāds ir -->
        <?php if (isset($_SESSION['pazinojums'])): ?>
            <div class="success-message">
                <p><?= htmlspecialchars($_SESSION['pazinojums']) ?></p>
            </div>
            <?php 
            // Dzēšam paziņojumu no sesijas
            unset($_SESSION['pazinojums']); 
            ?>
        <?php endif; ?>
        
        <!-- Pasūtījuma detaļas -->
        <div class="order-details">
            <h2>Pasūtījuma detaļas</h2>
            
            <?php if (isset($pasutijums)): ?>
                <div class="order-info">
                    <!-- Pasūtījuma numurs -->
                    <?php if (isset($pasutijums['pasutijuma_numurs'])): ?>
                        <div class="info-row">
                            <span>Pasūtījuma numurs:</span>
                            <span>#<?= htmlspecialchars($pasutijums['pasutijuma_numurs']) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Pasūtījuma datums -->
                    <?php if (isset($pasutijums['pas_datums'])): ?>
                        <div class="info-row">
                            <span>Datums:</span>
                            <span><?= date('d.m.Y H:i', strtotime($pasutijums['pas_datums'])) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Pasūtījuma statuss -->
                    <?php if (isset($pasutijums['statuss'])): ?>
                        <div class="info-row">
                            <span>Statuss:</span>
                            <span class="status <?= strtolower($pasutijums['statuss']) ?>">
                                <?= htmlspecialchars($pasutijums['statuss']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Kopējā summa -->
                    <?php if (isset($pasutijums['kopeja_cena'])): ?>
                        <div class="info-row">
                            <span>Kopējā summa:</span>
                            <span class="total-price"><?= number_format($pasutijums['kopeja_cena'], 2) ?>€</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Pasūtītās preces -->
            <h3>Pasūtītās preces</h3>
            
            <div class="order-items">
                <?php if (isset($ir_vienumi) && $ir_vienumi && isset($vienumi_rezultats)): ?>
                    <?php while ($vienums = $vienumi_rezultats->fetch_assoc()): ?>
                        <div class="order-item">
                            <!-- Produkta attēls -->
                            <div class="item-image">
                                <?php if (isset($vienums['attels1']) && !empty($vienums['attels1'])): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($vienums['attels1']) ?>" 
                                         alt="<?= htmlspecialchars($vienums['nosaukums'] ?? 'Produkts') ?>">
                                <?php else: ?>
                                    <div class="no-image">Nav attēla</div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Produkta detaļas -->
                            <div class="item-details">
                                <?php if (isset($vienums['nosaukums']) && !empty($vienums['nosaukums'])): ?>
                                    <h4><?= htmlspecialchars($vienums['nosaukums']) ?></h4>
                                <?php else: ?>
                                    <h4>Produkts ID: <?= htmlspecialchars($vienums['produkta_id'] ?? 'nav zināms') ?></h4>
                                <?php endif; ?>
                                
                                <?php if (isset($vienums['cena'])): ?>
                                    <p>Cena: <?= number_format($vienums['cena'], 2) ?>€</p>
                                <?php endif; ?>
                                
                                <?php if (isset($vienums['daudzums_no_groza'])): ?>
                                    <p>Daudzums: <?= intval($vienums['daudzums_no_groza']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Kopējā summa par vienumu -->
                            <div class="item-total">
                                <?php if (isset($vienums['cena']) && isset($vienums['daudzums_no_groza'])): ?>
                                    <p><?= number_format($vienums['cena'] * $vienums['daudzums_no_groza'], 2) ?>€</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Nav atrasti pasūtījuma vienumi.</p>
                <?php endif; ?>
            </div>
            
            <!-- Piegādes informācija -->
            <div class="shipping-info">
                <h3>Piegādes informācija</h3>
                
                <?php if (isset($pasutijums)): ?>
                    <!-- Klienta vārds un uzvārds -->
                    <?php if (isset($pasutijums['vards']) && isset($pasutijums['uzvards'])): ?>
                        <p><?= htmlspecialchars($pasutijums['vards'] . ' ' . $pasutijums['uzvards']) ?></p>
                    <?php endif; ?>
                    
                    <!-- Telefona numurs -->
                    <?php if (isset($pasutijums['talrunis'])): ?>
                        <p>Tel: <?= htmlspecialchars($pasutijums['talrunis']) ?></p>
                    <?php endif; ?>
                    
                    <!-- E-pasta adrese -->
                    <?php if (isset($pasutijums['epasts'])): ?>
                        <p>E-pasts: <?= htmlspecialchars($pasutijums['epasts']) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Izņemšanas informācija -->
                <div class="pickup-info">
                    <h4>Izņemšanas vieta:</h4>
                    <p><b>Mēs paziņosim, kad jūsu pasūtījums būs gatavs saņemšanai!</b></p>
                    <p>Mūsu veikals: Lielā iela 14-11, Liepāja</p>
                    <p>Darba laiks: P-Pk 10:00-16:00, S 10:00-14:00</p>
                </div>
            </div>
        </div>
        
        <!-- Darbības pogas -->
        <div class="actions">
            <a href="index.php" class="btn">Atgriezties sākumlapā</a>
            <a href="produkcija.php" class="btn">Turpināt iepirkties</a>
        </div>
    </div>
</section>

<?php
// Iekļaujam kājeni
include 'footer.php';

// Aizveram datu bāzes savienojumu
if (isset($savienojums)) {
    $savienojums->close();
}
?>