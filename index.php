<?php
    require 'header.php'
?>
    <section id="sakums">
  
            
            <div class="box text">
                <h2>Laiks svinēt!</h2>
                <p>Esi sveicināts mūsu internet veikalā -<strong>"Sparkly Dream"!</strong><br>
            Mēs piedāvājam Jums unikālus eglītes rotājumus. Kā arī, Jūs varas izvēlēties savu dizainu, ko mēs izstrādāsim!</p>
            <a href="produkcija.php" class="btn">Iepirkties</a>
            </div>
            <!-- <div class="box">
                <img src="images/bezfona.png" alt="">
            </div> -->
          

        
    </section>


    <section id="parmums">
        <h1>Par mums</h1>
        <div class="box-container">
            <div class="box">
                <i class="fa-solid fa-euro-sign"></i>
                <h2>Izdevīgas cenas</h2>
                <p>Mūsu eglīšu rotājumi piedāvā lieliskas cenas, kas pieejamas ikvienam.</p>
            </div>
            <div class="box">
                <i class="fa-solid fa-medal"></i>
                <h2>Kvalitāte</h2>
                <p>Augsta kvalitāte nodrošina, ka rotājumi kalpos gadiem ilgi.</p>
            </div>
            <div class="box">
                <i class="fa-solid fa-paintbrush"></i>
                <h2>Radošums</h2>
                <p>Mūsu rotājumi ir īpaši skaisti un piešķirs svētku noskaņu jebkurai eglītei.</p>
            </div>
            <div class="box">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <h2>Personalizācija</h2>
                <p>Piedāvājam personalizācijas iespējas, lai jūsu rotājumi būtu unikāli.</p>
            </div>
        </div>
    </section>

    <section id="custom-design">
    <div class="container">
        <h1>Izveidojiet savu unikālo rotaļlietu!</h1>
        <p>Mēs dodam jums iespēju īstenot savu ideju! Jūs varat izvēlēties krāsu, formu un papildu detaļas savai rotaļlietai, lai padarītu to patiesi unikālu.</p>
        <div class="custom-design-box">
            <img src="images/izdeide.jpg" alt="Rotaļlietas izveide">
            <div class="custom-text">
                <h2>Kā tas darbojas?</h2>
                <ul>
                    <li>Izvēlieties rotaļlietas pamatformu</li>
                    <li>Nosakiet krāsu un materiālus</li>
                    <li>Pievienojiet unikālas detaļas</li>
                    <li>Mēs izveidosim rotaļlietu pēc jūsu dizaina!</li>
                </ul>
                <a href="materiali.php" class="btn full">Izveidot</a>
            </div>
        </div>
    </div>
</section>
    
    <section id="produkcija">
    <h1>Produkcija</h1>
    <div class="box-container">
    <?php
require "admin/db/con_db.php";

$produkcijaSQL = "SELECT * FROM produkcija_sprarkly LIMIT 4";
$atlasaProdukcija = mysqli_query($savienojums, $produkcijaSQL);

if (mysqli_num_rows($atlasaProdukcija) > 0) {
    while ($bumba = mysqli_fetch_assoc($atlasaProdukcija)) {
        // Get the image data from the 'attel1' column
        $attels1Data = $bumba['attels1'];

        // Convert the image data to a base64 encoded string
        $attels1Base64 = base64_encode($attels1Data);
        $attels1Src = 'data:image/jpeg;base64,' . $attels1Base64;  // Adjust 'image/jpeg' if your image is a different format

        echo "
        <a href='produkts.php?id={$bumba['id_bumba']}' class='box'>

         <img src='{$attels1Src}' alt='Image'>
            <h3>{$bumba['nosaukums']}</h3>
            <h4>{$bumba['cena']}€</h4>
           
        </a>
        ";
    }
} else {
    echo "Nav nevienu piedāvājumu";
}
?>  

    </div>
    <a href="produkcija.php" class="btn">Skatīt vairāk</a>
</section>
   <?php
    // Get latest feedback from database for homepage display
    // First, let's check if the table exists and get its structure
    $table_check = "SHOW TABLES LIKE 'sparkly_atsauksmes'";
    $table_result = $savienojums->query($table_check);
    
    $latest_feedback = [];
    $average_rating = 0;
    $total_ratings = 0;
    
    if ($table_result && $table_result->num_rows > 0) {
        // Table exists, now check if it has data and what columns it has
        $column_check = "SHOW COLUMNS FROM sparkly_atsauksmes";
        $columns_result = $savienojums->query($column_check);
        
        if ($columns_result) {
            // Build the query based on available columns
            $feedback_sql = "SELECT a.id_atsauksme, a.vards_uzvards, a.zvaigznes, a.atsauksme, a.datums";
            
            // Check if we have lietotajs_id column to join with users table
            $has_lietotajs_id = false;
            while ($column = $columns_result->fetch_assoc()) {
                if ($column['Field'] == 'lietotajs_id') {
                    $has_lietotajs_id = true;
                    break;
                }
            }
            
            if ($has_lietotajs_id) {
                $feedback_sql .= ", l.lietotajvards, l.foto
                                 FROM sparkly_atsauksmes a
                                 LEFT JOIN lietotaji_sparkly l ON a.lietotajs_id = l.id_lietotajs";
            } else {
                $feedback_sql .= " FROM sparkly_atsauksmes a";
            }
            
            // Add condition to show only approved feedback if the column exists
            $feedback_sql .= " WHERE 1=1";
            
            // Check if apstiprinats column exists
            $columns_result = $savienojums->query("SHOW COLUMNS FROM sparkly_atsauksmes LIKE 'apstiprinats'");
            if ($columns_result && $columns_result->num_rows > 0) {
                $feedback_sql .= " AND a.apstiprinats = 1";
            }
            
            $feedback_sql .= " ORDER BY a.datums DESC LIMIT 3";
            
            $result = $savienojums->query($feedback_sql);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $latest_feedback[] = $row;
                }
                
                // Calculate average rating for display
                if (!empty($latest_feedback)) {
                    $sum_ratings = array_sum(array_column($latest_feedback, 'zvaigznes'));
                    $total_ratings = count($latest_feedback);
                    $average_rating = $sum_ratings / $total_ratings;
                }
            }
        }
    }
    ?>

    <section id="atsauksmes">
        <h1>Klientu atsauksmes</h1>
        
        <?php if (!empty($latest_feedback)): ?>
            <div class="feedback-summary">
                <div class="average-rating-home">
                    <span class="rating-value"><?php echo number_format($average_rating, 1); ?></span>
                    <div class="stars">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= floor($average_rating)) {
                                echo '<i class="fa-solid fa-star"></i>';
                            } elseif ($i == ceil($average_rating) && $average_rating > floor($average_rating)) {
                                echo '<i class="fa-solid fa-star-half-stroke"></i>';
                            } else {
                                echo '<i class="fa-regular fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <span class="rating-count">(<?php echo count($latest_feedback); ?> jaunākās atsauksmes)</span>
                </div>
            </div>
            
            <div class="box-container">
                <?php foreach ($latest_feedback as $feedback): ?>
                    <div class="box">
                        <div class="user-avatar-home">
                            <?php
                            // Profile image processing
                            $profile_image = (!empty($feedback['foto'])) 
                                ? 'data:image/jpeg;base64,'.base64_encode($feedback['foto']) 
                                : null;
                            ?>
                            <?php if ($profile_image): ?>
                                <img src="<?php echo $profile_image; ?>" 
                                     alt="Profila attēls" 
                                     class="profile-image-home"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <?php endif; ?>
                            
                            <img src="images/user.png" 
                                 alt="Default user" 
                                 class="default-user-image"
                                 style="display: <?php echo $profile_image ? 'none' : 'block'; ?>">
                        </div>
                        <div class="text">
                            <h3><?php echo htmlspecialchars($feedback['vards_uzvards']); ?></h3>
                            <div class="stars-home">
                                <?php 
                                $rating = (int)$feedback['zvaigznes'];
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<i class="fa-solid fa-star"></i>';
                                    } else {
                                        echo '<i class="fa-regular fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <p>"<?php echo htmlspecialchars($feedback['atsauksme']); ?>"</p>
                            <small class="feedback-date-home">
                                <?php echo date('d.m.Y', strtotime($feedback['datums'])); ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- No feedback message when database is empty -->
            <div class="no-feedback-home">
                <div class="no-feedback-content">
                    <i class="fas fa-comments"></i>
                    <h3>Pagaidām nav atsauksmju</h3>
                    <p>Esiet pirmais, kas dalās ar savu pieredzi pēc pasūtījuma!</p>
                    <a href="produkcija.php" class="btn btn-outline">Iepirkties tagad</a>
                </div>
            </div>
        <?php endif; ?>
        
        <a href="atsauksmes.php" class="btn">Skatīt visas atsauksmes</a>
    </section>




<?php
    require 'footer.php'
?>