<?php
    require 'header.php';
?>

<section id="kontakti">
    <div class="container">
        <h1>Sazinieties ar mums</h1>
        
        <div class="contact-container">
            <!-- Contact Information -->
            <div class="contact-info">
                <h2>Mūsu kontaktinformācija</h2>
                
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <div class="info-content">
                        <h3>Tālrunis</h3>
                        <p>(+371) 26789983 (Natālija)</p>
                        <p>(+371) 20514011 (Anastasija)</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div class="info-content">
                        <h3>E-pasts</h3>
                        <p>sparkly.dream.shop@gmail.com</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="info-content">
                        <h3>Adrese</h3>
                        <p>Lielā iela 14-11, Liepāja, Latvija</p>
                    </div>
                </div>
                
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2202.9801870543934!2d21.0181181550179!3d56.48532818110969!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46faaf54474d0b99%3A0x2c3238f1f38ef80c!2sVai%C5%86odes%20iela%2028-32%2C%20Liep%C4%81ja%2C%20LV-3407!5e0!3m2!1sru!2slv!4v1727176677157!5m2!1sru!2slv" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-form">
                <h2>Nosūtiet mums ziņu</h2>
                
                <?php
                // Check if form is submitted
                if(isset($_POST["nosutit"])){
                    // Include external mail handler
                    require 'assets/mail.php';
                }
                ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="vards">Vārds, Uzvārds*</label>
                        <input type="text" id="vards" name="vards" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="epasts">E-pasts*</label>
                        <input type="email" id="epasts" name="epasts" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="talrunis">Tālrunis</label>
                        <input type="tel" id="talrunis" name="talrunis">
                    </div>
                    
                    <div class="form-group">
                        <label for="zinojums">Ziņojums*</label>
                        <textarea id="zinojums" name="zinojums" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn" name="nosutit">Nosūtīt ziņojumu</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    function x() {
        document.getElementById('pazinojums').style.display = 'none';
    }
</script>

<?php
    require 'footer.php'
?>