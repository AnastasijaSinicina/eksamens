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
$feedback_sql = "SELECT a.id_atsauksme, a.vards_uzvards, a.zvaigznes, a.atsauksme, a.datums,
                        l.lietotajvards, l.foto
                 FROM sparkly_atsauksmes a
                 LEFT JOIN lietotaji_sparkly l ON a.lietotajs_id = l.id_lietotajs
                 ORDER BY a.datums DESC
                 LIMIT 3";

$result = $savienojums->query($feedback_sql);
$latest_feedback = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $latest_feedback[] = $row;
    }
}

// Calculate average rating for display
$average_rating = 0;
if (!empty($latest_feedback)) {
    $sum_ratings = array_sum(array_column($latest_feedback, 'zvaigznes'));
    $average_rating = $sum_ratings / count($latest_feedback);
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
                        $profile_image = !empty($feedback['foto']) 
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

<style>
/* Additional styles for homepage feedback section */
.feedback-summary {
    text-align: center;
    margin-bottom: 2rem;
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
}

.average-rating-home {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.average-rating-home .rating-value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--maincolor);
}

.average-rating-home .stars {
    color: #ffd700;
    font-size: 1.2rem;
}

.average-rating-home .rating-count {
    color: var(--text);
    font-size: 0.9rem;
}

.user-avatar-home {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto 1rem auto;
    border: 3px solid #e3f2fd;
    position: relative;
}

.profile-image-home,
.default-user-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.stars-home {
    color: #ffd700;
    margin: 0.5rem 0;
}

.feedback-date-home {
    color: var(--text);
    font-size: 0.8rem;
    margin-top: 0.5rem;
    display: block;
}
#atsauksmes .box{
    box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
}

#atsauksmes .box .text p {
    font-style: italic;
    line-height: 1.6;
}

/* No feedback state for homepage */
.no-feedback-home {
    background-color: #f8f9fa;
    border-radius: 1rem;
    padding: 3rem;
    text-align: center;
    margin: 2rem 0;
}

.no-feedback-content {
    max-width: 400px;
    margin: 0 auto;
}

.no-feedback-content i {
    font-size: 3rem;
    color: #ccc;
    margin-bottom: 1rem;
}

.no-feedback-content h3 {
    color: var(--tumsa);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.no-feedback-content p {
    color: var(--text);
    margin-bottom: 2rem;
    line-height: 1.6;
}

.no-feedback-content .btn {
    background-color: transparent;
    color: var(--maincolor);
    border: 2px solid var(--maincolor);
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.no-feedback-content .btn:hover {
    background-color: var(--maincolor);
    color: white;
}

@media (max-width: 768px) {
    .average-rating-home {
        flex-direction: column;
        gap: 1rem;
    }
    
    .average-rating-home .rating-value {
        font-size: 1.5rem;
    }
    
    .user-avatar-home {
        width: 60px;
        height: 60px;
    }
    
    .no-feedback-home {
        padding: 2rem 1rem;
    }
}
</style>

<?php
    require 'footer.php'
?>