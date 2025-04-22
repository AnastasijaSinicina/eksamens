<?php
    require 'header.php'
?>

<section id="jaunumi-main">
    <div class="container">
        <h1>Jaunumi</h1>
        
        <div class="news-container">
            <!-- Latest News Item -->
            <div class="news-item featured">
                <div class="news-image">
                    <img src="images/igruwka7.jpg" alt="Jauna kolekcija">
                </div>
                <div class="news-content">
                    <div class="news-date">17. Aprīlis, 2025</div>
                    <h2>Jauna Ziemas kolekcija 2025</h2>
                    <p>Mēs ar prieku paziņojam par mūsu jaunās Ziemas kolekcijas izlaišanu! Šogad mēs piedāvājam vairāk nekā 20 jaunus dizainus, kas iedvesmoti no ziemas pasakām un skandināvu motīviem. Kolekcijā ir iekļauti gan tradicionālie rotājumi, gan moderni un unikāli dizaini.</p>
                    <a href="produkcija.php" class="btn">Apskatīt kolekciju</a>
                </div>
            </div>
            
            <!-- Regular News Items -->
            <div class="news-grid">
                <div class="news-item">
                    <div class="news-image">
                        <img src="images/igruwka14.jpg" alt="Personalizēti rotājumi">
                    </div>
                    <div class="news-content">
                        <div class="news-date">10. Aprīlis, 2025</div>
                        <h3>Personalizēti rotājumi ģimenēm</h3>
                        <p>Tagad piedāvājam personalizētus rotājumus ar ģimenes vārdiem vai iniciāļiem. Ideāla dāvana svētkos vai īpašos gadījumos!</p>
                        <a href="materiali.php" class="read-more">Lasīt vairāk →</a>
                    </div>
                </div>
                
                <div class="news-item">
                    <div class="news-image">
                        <img src="images/do1.jpg" alt="Darbnīcas">
                    </div>
                    <div class="news-content">
                        <div class="news-date">5. Aprīlis, 2025</div>
                        <h3>Atvērto durvju dienas mūsu darbnīcā</h3>
                        <p>Aicinām visus interesentus apmeklēt mūsu darbnīcu 15. un 16. aprīlī. Būs iespēja redzēt, kā top mūsu rotājumi, un piedalīties meistarklasē.</p>
                        <a href="#" class="read-more">Lasīt vairāk →</a>
                    </div>
                </div>
                
                <div class="news-item">
                    <div class="news-image">
                        <img src="images/igruwka5.jpg" alt="Pavasara kolekcija">
                    </div>
                    <div class="news-content">
                        <div class="news-date">28. Marts, 2025</div>
                        <h3>Pavasara kolekcijas izpārdošana</h3>
                        <p>Sagatavošanās vasarai! Visiem pavasara kolekcijas rotājumiem 20% atlaide. Piedāvājums spēkā līdz 20. aprīlim.</p>
                        <a href="produkcija.php" class="read-more">Lasīt vairāk →</a>
                    </div>
                </div>
                
                <div class="news-item">
                    <div class="news-image">
                        <img src="images/igruwka3.jpg" alt="Jaunais katalogs">
                    </div>
                    <div class="news-content">
                        <div class="news-date">15. Marts, 2025</div>
                        <h3>Publicēts jaunais katalogs</h3>
                        <p>Mūsu jaunais 2025. gada katalogs ir pieejams! Apskatiet visas kolekcijas un jaunos piedāvājumus vienuviet.</p>
                        <a href="#" class="read-more">Lasīt vairāk →</a>
                    </div>
                </div>
            </div>
            
            <!-- Company Update Section -->
            <div class="company-updates">
                <h2>Uzņēmuma jaunumi</h2>
                
                <div class="update-item">
                    <div class="update-date">1. Aprīlis, 2025</div>
                    <h3>Mūsu tiešsaistes veikals tagad ar jaunu dizainu</h3>
                    <p>Esam atjaunojuši mūsu tiešsaistes veikalu, lai padarītu iepirkšanos vēl ērtāku un patīkamāku. Jaunais dizains ļauj vieglāk atrast vēlamos rotājumus, kā arī piedāvā uzlabotu filtru sistēmu.</p>
                </div>
                
                <div class="update-item">
                    <div class="update-date">20. Marts, 2025</div>
                    <h3>Paplašinām savu komandu</h3>
                    <p>Sparkly Dream aug! Mēs paplašinām savu komandu un meklējam jaunus talantīgus meistarus, kas palīdzēs mums radīt vēl vairāk brīnišķīgu rotājumu. Ja jūs interesē pievienoties mūsu komandai, lūdzu, sazinieties ar mums.</p>
                </div>
                
                <div class="update-item">
                    <div class="update-date">10. Marts, 2025</div>
                    <h3>Sadarbība ar vietējiem māksliniekiem</h3>
                    <p>Mēs esam uzsākuši jaunu projektu sadarbībā ar vietējiem māksliniekiem, lai radītu unikālu rotājumu kolekciju, kas apvieno tradicionālo amatu prasmes ar mūsdienīgu mākslas pieeju.</p>
                </div>
            </div>
        </div>
        
        <!-- Subscribe to Newsletter -->
        <div class="newsletter">
            <h2>Pierakstieties jaunumiem</h2>
            <p>Saņemiet jaunāko informāciju par mūsu kolekcijām, piedāvājumiem un pasākumiem tieši savā e-pastā!</p>
            <form action="#" method="post" class="newsletter-form">
                <input type="email" name="email" placeholder="Jūsu e-pasta adrese" required>
                <button type="submit" class="btn">Pierakstīties</button>
            </form>
        </div>
    </div>
</section>

<style>
    #jaunumi-main {
        padding: 4rem 6%;
        background: var(--gradient);
    }
    
    #jaunumi-main .container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    #jaunumi-main h1 {
        text-align: center;
        margin-bottom: 3rem;
        color: var(--tumsa);
    }
    
    .news-container {
        display: flex;
        flex-direction: column;
        gap: 3rem;
    }
    
    /* Featured News Item */
    .news-item.featured {
        display: flex;
        background-color: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--box-shadow2);
    }
    
    .news-item.featured .news-image {
        flex: 0 0 50%;
    }
    
    .news-item.featured .news-content {
        flex: 1;
        padding: 2.5rem;
        display: flex;
        flex-direction: column;
    }
    
    .news-item.featured img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .news-item.featured h2 {
        font-size: 2rem;
        color: var(--tumsa);
        margin: 0.5rem 0 1.5rem 0;
    }
    
    .news-item.featured p {
        font-size: 1.2rem;
        line-height: 1.6;
        margin-bottom: 2rem;
    }
    
    .news-item.featured .btn {
        align-self: flex-start;
        margin-top: auto;
    }
    
    /* News Grid */
    .news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
        gap: 2rem;
    }
    
    .news-item {
        background-color: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .news-item:hover {
        transform: translateY(-10px);
        box-shadow: var(--box-shadow2);
    }
    
    .news-item .news-image {
        height: 200px;
        overflow: hidden;
    }
    
    .news-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .news-item:hover img {
        transform: scale(1.05);
    }
    
    .news-item .news-content {
        padding: 1.5rem;
    }
    
    .news-date {
        font-size: 0.9rem;
        color: var(--maincolor);
        margin-bottom: 0.5rem;
    }
    
    .news-item h3 {
        font-size: 1.4rem;
        color: var(--tumsa);
        margin-bottom: 1rem;
    }
    
    .news-item p {
        font-size: 1rem;
        line-height: 1.5;
        margin-bottom: 1.5rem;
    }
    
    .read-more {
        color: var(--maincolor);
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .read-more:hover {
        color: var(--tumsa);
    }
    
    /* Company Updates */
    .company-updates {
        background-color: var(--light2);
        padding: 2.5rem;
        border-radius: 1rem;
        box-shadow: var(--box-shadow);
    }
    
    .company-updates h2 {
        color: var(--tumsa);
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .update-item {
        background-color: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--maincolor);
    }
    
    .update-date {
        font-size: 0.9rem;
        color: var(--maincolor);
        margin-bottom: 0.5rem;
    }
    
    .update-item h3 {
        font-size: 1.3rem;
        color: var(--tumsa);
        margin-bottom: 0.8rem;
    }
    
    .update-item p {
        font-size: 1rem;
        line-height: 1.5;
    }
    
    /* Newsletter */
    .newsletter {
        margin-top: 4rem;
        text-align: center;
        background-color: var(--tumsa2);
        padding: 3rem;
        border-radius: 1rem;
        color: white;
    }
    
    .newsletter h2 {
        color: white;
        margin-bottom: 1rem;
    }
    
    .newsletter p {
        font-size: 1.1rem;
        margin-bottom: 2rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .newsletter-form {
        display: flex;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .newsletter-form input {
        flex: 1;
        padding: 1rem;
        border: none;
        border-radius: 0.5rem 0 0 0.5rem;
        font-size: 1rem;
    }
    
    .newsletter-form .btn {
        border-radius: 0 0.5rem 0.5rem 0;
        background-color: var(--maincolor);
        color: white;
    }
    
    .newsletter-form .btn:hover {
        background-color: var(--light1);
        color: var(--tumsa);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 992px) {
        .news-item.featured {
            flex-direction: column;
        }
        
        .news-item.featured .news-image {
            height: 300px;
        }
    }
    
    @media (max-width: 768px) {
        .news-grid {
            grid-template-columns: 1fr;
        }
        
        .newsletter-form {
            flex-direction: column;
        }
        
        .newsletter-form input {
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .newsletter-form .btn {
            border-radius: 0.5rem;
            width: 100%;
        }
    }
</style>

<?php
    require 'footer.php'
?>