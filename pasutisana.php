<?php
// Iekļauj datubāzes apstrādes failu
include 'admin/db/pasutisana.php';

// Iekļauj galveni
include 'header.php';
?>

<section id="pasutisana">
    <h1>Pasūtījuma noformēšana</h1>
    
    <?php 
    // Parāda kļūdas ziņojumu, ja tāds eksistē
    if (isset($error_message)): 
    ?>
        <div class="error-message">
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>
    
    <div class="checkout-container">
        <div class="order-summary">
            <h2>Svarīgi!</h2>
            <p class="svarigi">Mūsu izstrādājumus ir iespēja savākt tikai veikalā! <br>Ja ir nepieciešama piegāde, sazinies ar mums e-pastā, norādot adresi un pasūtījuma numuru!</p>
            <h2>Pasūtījuma kopsavilkums</h2>
            
            <div class="summary-items">
                <?php 
                // Parāda groza preces, ja tās eksistē
                if (isset($cart_items_display)):
                    foreach ($cart_items_display as $item): 
                ?>
                    <div class="summary-item">
                        <div class="item-image">
                            <img src="data:image/jpeg;base64,<?= base64_encode($item['attels1']) ?>" alt="<?= htmlspecialchars($item['nosaukums']) ?>">
                        </div>
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['nosaukums']) ?></h3>
                            <p>Daudzums: <?= $item['daudzums'] ?></p>
                            <p class="item-price"><?= number_format($item['cena'], 2) ?>€</p>
                        </div>
                    </div>
                <?php 
                    endforeach;
                endif; 
                ?>
            </div>
            
            <div class="summary-totals">
                <div class="total-row">
                    <span>Kopā:</span>
                    <span>
                        <?php 
                        // Parāda kopējo summu, ja tā eksistē
                        if (isset($subtotal)): 
                            echo number_format($subtotal, 2) . '€';
                        endif; 
                        ?>
                    </span>
                </div>
            </div>
            
            <a href="grozs.php" class="btn">Atgriezties grozā</a>
        </div>

        <div class="checkout-form">
            <h2>Piegādes informācija</h2>
            
            <form id="checkout-form" method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                <div class="form-group">
                    <label for="vards">Vārds*</label>
                    <input type="text" id="vards" name="vards" 
                           value="<?php 
                           // Ievieto lietotāja vārdu, ja tas eksistē
                           if (isset($user['vards'])): 
                               echo htmlspecialchars($user['vards']); 
                           endif; 
                           ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="uzvards">Uzvārds*</label>
                    <input type="text" id="uzvards" name="uzvards" 
                           value="<?php 
                           // Ievieto lietotāja uzvārdu, ja tas eksistē
                           if (isset($user['uzvards'])): 
                               echo htmlspecialchars($user['uzvards']); 
                           endif; 
                           ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="epasts">E-pasts*</label>
                    <input type="email" id="epasts" name="epasts" 
                           value="<?php 
                           // Ievieto lietotāja e-pastu, ja tas eksistē
                           if (isset($user['epasts'])): 
                               echo htmlspecialchars($user['epasts']); 
                           endif; 
                           ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telefons">Telefons*</label>
                    <input type="text" id="telefons" name="telefons" 
                           value="<?php 
                           // Ievieto lietotāja telefona numuru, ja tas eksistē
                           if (isset($user['telefons'])): 
                               echo htmlspecialchars($user['telefons']); 
                           endif; 
                           ?>" required>
                </div>
                
                
                
                <div class="form-group">
                    <label for="piezimes">Piezīmes par pasūtījumu</label>
                    <textarea id="piezimes" name="piezimes" rows="4"></textarea>
                </div>
                
                <!-- Pogas pasūtījuma noformēšanai -->    
                <button type="submit" name="submit_order" id="confirm-order-btn" class="btn full">Apstiprināt pasūtījumu</button>
            </form>
        </div>
    </div>

</section>

<script>
// JavaScript kods maksājuma veida izvēlei un formas validācijai
// Šeit var pievienot nepieciešamo JavaScript funkcionalitāti
</script>

<?php
// Iekļauj kājeni
include 'footer.php';
?>