<?php
    require 'header.php'
?>
<section id="materiali">
    <h2>Apskaties materiālus, lai pāsūtītu Tavu iedomātu rotājumu!</h2>
    <div class="box-container">
        <div class="box">
            <h3>Formas:</h3>
            <div class="krasa">
                <img src="images/krasa1.png" alt="">
                <p>Apaļa 10x10</p>
            </div>
            <div class="krasa">
                <img src="images/krasa2.png" alt="">
                <p>Apaļa 15x15</p>
            </div>
            <div class="krasa">
                <img src="images/krasa3.png" alt="">
                <p>Medaljons</p>
            </div>
            <div class="krasa">
                <img src="images/krasa4.png" alt="">
                <p>Lāsteka</p>
            </div>
            <div class="krasa">
                <img src="images/krasa4.png" alt="">
                <p>Mālu rāmis</p>
            </div>
            <div class="krasa">
                <img src="images/krasa4.png" alt="">
                <p>Mālu lietussargs</p>
            </div>
            
        </div>
        <div class="box">
            <h3>Pamatkrāsas:</h3>
            <div class="krasa">
                <img src="images/krasa1.png" alt="">
                <p>Baltā</p>
            </div>
            <div class="krasa">
                <img src="images/krasa2.png" alt="">
                <p>Bēšs</p>
            </div>
            <div class="krasa">
                <img src="images/krasa3.png" alt="">
                <p>Rozā</p>
            </div>
            <div class="krasa">
                <img src="images/krasa4.png" alt="">
                <p>Pelēka</p>
            </div>
        </div>
        <div class="box">
            <h3>Mālu figūras:</h3>
            <div class="krasa">
                <img src="images/figuri1.jpg" alt="">
                <p>Rāmis</p>
            </div>
            <div class="krasa">
                <img src="images/figuri2.jpg" alt="">
                <p>Lācis</p>
            </div>
            <div class="krasa">
                <img src="images/figuri3.jpg" alt="">
                <p>Logi</p>
            </div>
            <div class="krasa">
                <img src="images/figuri4.jpg" alt="">
                <p>Putns</p>
            </div>
            <div class="krasa">
                <img src="images/figuri5.jpg" alt="">
                <p>Karuselis</p>
            </div>
            <div class="krasa">
                <img src="images/figuri6.jpg" alt="">
                <p>Enģelis</p>
            </div>
        </div>
        <div class="box">
            <h3>Dekorējums:</h3>
            <div class="krasa">
                <img src="images/cveti.jpg" alt="">
                <p>Dekoratīvie ziedi</p>
            </div>
            <div class="krasa">
                <img src="images/kamni.jpg" alt="">
                <p>Sudraba dekorējumi</p>
            </div>
            <div class="krasa">
                <img src="images/kamni2.jpg" alt="">
                <p>Zelta dekorējumi</p>
            </div>
            <div class="krasa">
                <img src="images/kamni2.jpg" alt="">
                <p>Baltie dekorējumi</p>
            </div>
            <div class="krasa">
                <img src="images/kamni2.jpg" alt="">
                <p>Roza dekorējumi</p>
            </div>
         
        </div>
    </div>
    <div class="dropdown">
        <form action="">
            <h3>Izvēlies formu:</h3>
            <div id="radio">
                
                <div class="radio-group">
                    
                </div>
                <div class="radio-group">
                    <input type="radio" id="apala1" name="color1" value="apala1">
                    <label for="apala1">Apaļa 10x10</label>
                </div>
                <div class="radio-group">
                    <input type="radio" id="apala2" name="color1" value="apala2">
                    <label for="apala2">Apaļa 15x15</label>
                </div>
                <div class="radio-group">
                    <input type="radio" id="lasteka" name="color1" value="lasteka">
                    <label for="lasteka">Lāsteka</label>
                </div>
                <div class="radio-group">
                    <input type="radio" id="medaljons" name="color1" value="medaljons">
                    <label for="medaljons">Medaljons</label>
                </div>
                <div class="radio-group">
                    <input type="radio" id="ramis" name="color1" value="ramis">
                    <label for="ramis">Mālu rāmis</label>
                </div>
                <div class="radio-group">
                    <input type="radio" id="lietussargs" name="color1" value="lietussargs">
                    <label for="lietussargs">Mālu lietussargs</label>
                </div>
            </div>
            <button type="button" class="btn full" onclick="showDropdowns()">Izvēlēties</button>
            <div id="dropdown1" class="all" style="display:none;">
                <label for="color1">Izvēlies pamatkrāsu:</label>
                <select id="color1" name="color1">
                  <option value="balta">Baltā</option>
                  <option value="bess">Bēšs</option>
                  <option value="roza">Rozā</option>
                  <option value="peleka">Pelēka</option>
                </select>
            </div>
    
            <div id="dropdown2" class="big" style="display:none;">
                <label for="figure1">Izvēlies mālu figūru:</label>
                <select id="figure1" name="figure1">
                  <option value="lacis">Rāmis</option>
                  <option value="cuska">Lācis</option>
                  <option value="engelis">Logi</option>
                  <option value="ramis">Putns</option>
                  <option value="engelis">Karuselis</option>
                  <option value="ramis">Enģelis</option>
                </select>           
            </div>
    
            <div id="dropdown3" class="all" style="display:none;">
                <label for="color2">Izvēlies dekorējumu krāsu:</label>
                <select id="color2" name="color2">
                  <option value="bez">Zelta</option>
                  <option value="bess">Balta</option>
                  <option value="roza">Sudraba</option>
                  <option value="peleka">Roza</option>
                </select>
            </div>
    
            <div id="dropdown4" class="all" style="display:none;">
                <label for="decor">Izvēlies dekorējumus:</label>
                <select id="decor" name="decor">
                    <option value="bez">Akmentiņi</option>
                    <option value="bess">Mežģīnes</option>
                    <option value="roza">Dekoratīvie ziedi</option>
                </select>
            </div>
        </form>
    </div>
</section>
<?php
    require 'footer.php'
?>