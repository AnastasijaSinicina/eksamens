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
            <img src="images/custom-toy.jpg" alt="Rotaļlietas izveide">
            <div class="custom-text">
                <h2>Kā tas darbojas?</h2>
                <ul>
                    <li>Izvēlieties rotaļlietas pamatformu</li>
                    <li>Nosakiet krāsu un materiālus</li>
                    <li>Pievienojiet unikālas detaļas</li>
                    <li>Mēs izveidosim rotaļlietu pēc jūsu dizaina!</li>
                </ul>
                <a href="materiali.php" class="btn">Izveidot</a>
            </div>
        </div>
    </div>
</section>
    
    <section id="produkcija">
    <h1>Produkcija</h1>
    <div class="box-container">
    <?php
require "db/con_db.php";

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

    
    <!-- <section id="atsauksmes">
        <h1>Atsauksmes</h1>     
        <div class="box-container">  
            <div class="box">
                <div class="text">
                <p>Es iegādājos Ziemassvētku bumbiņu, kas patiesi papildināja manu svētku dekorācijas. Tā ir skaisti izgatavota, ar spīdīgu un kvalitatīvu apdari, kas izskatās ļoti eleganti uz eglītes. Krāsa ir koša un neizbalē pat pēc vairākām svētku sezonām.</p>
                <div class="username">
                <p>Nora Kalniņa</p>
                <p><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></p>
            </div>
        </div>
                <div class="img">
                    <img src="images/igruwka1.jpg" alt="">
                </div>
               
            </div>
            <div class="box">
                <div class="text">
                <p>Esmu ļoti apmierināts ar šo Ziemassvētku rotājumu, lietussarga formā. Tā ir izturīga, skaisti izkrāsota un lieliski pieskaņojas manai svētku tēmai. </p>
                <div class="username">
                <p>Nora Kalniņa</p>
                <p><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></p>
            </div>
        </div>
                <div class="img">
                    <img src="images/igruwkaa7.jpg" alt="">
                </div>
               
            </div>
            <div class="box">
                <div class="text">
                <p>Šī Ziemassvētku bumbiņa bija patīkams pārsteigums – tās dizains ir izsmalcināts, un kvalitāte pārsniedza manas cerības.</p>
                <div class="username">
                <p>Nora Kalniņa</p>
                <p><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></p>
            </div>
        </div>
                <div class="img">
                    <img src="images/igruwka3.jpg" alt="">
                </div>
               
            </div>
    </div> 
    <a href="atsauksmes.php" class="btn">Lasīt vairāk</a>
    </section> -->

<?php
    require 'footer.php'
?>
