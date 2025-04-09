 <?php
    require 'header.php'
?>
 <!--<section id="materiali">
    <h2>Apskaties materiālus, lai pasūtītu Tavu iedomātu rotājumu!</h2>
    
    
    <div class="material-slider-container">
        <h3>Formas:</h3>
        <div id="formas-slider" class="splide">
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa1.png" alt="Apaļa 10x10">
                            <p>Apaļa 10x10</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa2.png" alt="Apaļa 15x15">
                            <p>Apaļa 15x15</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa3.png" alt="Medaljons">
                            <p>Medaljons</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa4.png" alt="Lāsteka">
                            <p>Lāsteka</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa4.png" alt="Mālu rāmis">
                            <p>Mālu rāmis</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa4.png" alt="Mālu lietussargs">
                            <p>Mālu lietussargs</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="material-slider-container">
        <h3>Pamatkrāsas:</h3>
        <div id="krasas-slider" class="splide">
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa1.png" alt="Baltā">
                            <p>Baltā</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa2.png" alt="Bēšs">
                            <p>Bēšs</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa3.png" alt="Rozā">
                            <p>Rozā</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/krasa4.png" alt="Pelēka">
                            <p>Pelēka</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="material-slider-container">
        <h3>Mālu figūras:</h3>
        <div id="figuras-slider" class="splide">
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/figuri1.jpg" alt="Rāmis">
                            <p>Rāmis</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/figuri2.jpg" alt="Lācis">
                            <p>Lācis</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/figuri3.jpg" alt="Logi">
                            <p>Logi</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/figuri4.jpg" alt="Putns">
                            <p>Putns</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/figuri5.jpg" alt="Karuselis">
                            <p>Karuselis</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/figuri6.jpg" alt="Enģelis">
                            <p>Enģelis</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="material-slider-container">
        <h3>Dekorējums:</h3>
        <div id="dekorejums-slider" class="splide">
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/cveti.jpg" alt="Dekoratīvie ziedi">
                            <p>Dekoratīvie ziedi</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/kamni.jpg" alt="Sudraba dekorējumi">
                            <p>Sudraba dekorējumi</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/kamni2.jpg" alt="Zelta dekorējumi">
                            <p>Zelta dekorējumi</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/kamni2.jpg" alt="Baltie dekorējumi">
                            <p>Baltie dekorējumi</p>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <div class="material-item">
                            <img src="images/kamni2.jpg" alt="Roza dekorējumi">
                            <p>Roza dekorējumi</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="custom-form-container">
        <h2>Izveidojiet savu unikālo rotājumu</h2>
        
        <form class="custom-form" action="submit_custom.php" method="post">
            <div class="dropdown">
                <div id="drop">
                    <label for="forma">Izvēlieties formu:</label>
                    <select name="forma" id="forma" required>
                        <option value="" disabled selected>-- Izvēlieties formu --</option>
                        <option value="Apaļa 10x10">Apaļa 10x10</option>
                        <option value="Apaļa 15x15">Apaļa 15x15</option>
                        <option value="Medaljons">Medaljons</option>
                        <option value="Lāsteka">Lāsteka</option>
                        <option value="Mālu rāmis">Mālu rāmis</option>
                        <option value="Mālu lietussargs">Mālu lietussargs</option>
                    </select>
                </div>
                
                <div id="drop">
                    <label for="krasa">Izvēlieties pamatkrāsu:</label>
                    <select name="krasa" id="krasa" required>
                        <option value="" disabled selected>-- Izvēlieties krāsu --</option>
                        <option value="Baltā">Baltā</option>
                        <option value="Bēšs">Bēšs</option>
                        <option value="Rozā">Rozā</option>
                        <option value="Pelēka">Pelēka</option>
                    </select>
                </div>
                
                <div id="drop">
                    <label for="figura">Izvēlieties mālu figūru:</label>
                    <select name="figura" id="figura">
                        <option value="" disabled selected>-- Izvēlieties figūru --</option>
                        <option value="Rāmis">Rāmis</option>
                        <option value="Lācis">Lācis</option>
                        <option value="Logi">Logi</option>
                        <option value="Putns">Putns</option>
                        <option value="Karuselis">Karuselis</option>
                        <option value="Enģelis">Enģelis</option>
                        <option value="Nav">Bez figūras</option>
                    </select>
                </div>
                
                <div id="drop">
                    <label for="dekorejums1">Izvēlieties pirmo dekorējumu:</label>
                    <select name="dekorejums1" id="dekorejums1">
                        <option value="" disabled selected>-- Izvēlieties dekorējumu --</option>
                        <option value="Dekoratīvie ziedi">Dekoratīvie ziedi</option>
                        <option value="Sudraba dekorējumi">Sudraba dekorējumi</option>
                        <option value="Zelta dekorējumi">Zelta dekorējumi</option>
                        <option value="Baltie dekorējumi">Baltie dekorējumi</option>
                        <option value="Roza dekorējumi">Roza dekorējumi</option>
                        <option value="Nav">Bez dekorējuma</option>
                    </select>
                </div>
                
                <div id="drop">
                    <label for="dekorejums2">Izvēlieties otro dekorējumu (neobligāti):</label>
                    <select name="dekorejums2" id="dekorejums2">
                        <option value="Nav" selected>Bez papildu dekorējuma</option>
                        <option value="Dekoratīvie ziedi">Dekoratīvie ziedi</option>
                        <option value="Sudraba dekorējumi">Sudraba dekorējumi</option>
                        <option value="Zelta dekorējumi">Zelta dekorējumi</option>
                        <option value="Baltie dekorējumi">Baltie dekorējumi</option>
                        <option value="Roza dekorējumi">Roza dekorējumi</option>
                    </select>
                </div>
                
                <div id="drop">
                    <label for="teksts">Personalizācijas teksts (neobligāti):</label>
                    <input type="text" name="teksts" id="teksts" placeholder="Piem., vārds vai datums" maxlength="20">
                </div>
                
                <button type="submit" class="btn">Pasūtīt rotājumu</button>
            </div>
        </form>
    </div>
</section> -->

<script>
    // document.addEventListener('DOMContentLoaded', function() {
    // // This function will create a main slider with thumbnail navigation
    // // similar to the one in produkts.php
    
    // // Initialize main sliders with thumbnails for each category
    // initializeSliderWithThumbnails('formas');
    // initializeSliderWithThumbnails('krasas');
    // initializeSliderWithThumbnails('figuras');
    // initializeSliderWithThumbnails('dekorejums');
    
    // function initializeSliderWithThumbnails(categoryId) {
    //     // Get the original slider element
    //     const originalSlider = document.getElementById(`${categoryId}-slider`);
    //     if (!originalSlider) return;
        
    //     // Get the parent container
    //     const container = originalSlider.closest('.material-slider-container');
    //     if (!container) return;
        
    //     // Get all slides from the original slider
    //     const slides = Array.from(originalSlider.querySelectorAll('.splide__slide'));
    //     if (!slides.length) return;
        
    //     // Remove the original slider
    //     originalSlider.remove();
        
    //     // Create new HTML structure for main slider and thumbnails
    //     const newHTML = `
    //         <div id="${categoryId}-main-slider" class="splide">
    //             <div class="splide__track">
    //                 <ul class="splide__list">
    //                     ${slides.map(slide => slide.outerHTML).join('')}
    //                 </ul>
    //             </div>
    //         </div>
            
    //         <div id="${categoryId}-thumbnail-slider" class="splide">
    //             <div class="splide__track">
    //                 <ul class="splide__list">
    //                     ${slides.map(slide => {
    //                         const img = slide.querySelector('img');
    //                         const imgSrc = img ? img.getAttribute('src') : '';
    //                         const imgAlt = img ? img.getAttribute('alt') : '';
    //                         return `<li class="splide__slide"><img src="${imgSrc}" alt="${imgAlt}"></li>`;
    //                     }).join('')}
    //                 </ul>
    //             </div>
    //         </div>
    //     `;
        
    //     // Add the new HTML to the container
    //     container.insertAdjacentHTML('beforeend', newHTML);
        
    //     // Initialize the splide sliders with syncing
    //     const mainSlider = new Splide(`#${categoryId}-main-slider`, {
    //         type: 'slide',
    //         perPage: 1,
    //         perMove: 1,
    //         gap: 10,
    //         pagination: false,
    //         arrows: true
    //     });
        
    //     const thumbnailSlider = new Splide(`#${categoryId}-thumbnail-slider`, {
    //         perPage: 4,
    //         perMove: 1,
    //         gap: 10,
    //         pagination: false,
    //         isNavigation: true,
    //         focus: 'center',
    //         arrows: false,
    //         rewind: true,
    //         breakpoints: {
    //             768: {
    //                 perPage: 3,
    //             },
    //             576: {
    //                 perPage: 2,
    //             }
    //         }
    //     });
        
    //     // Sync the sliders
    //     mainSlider.sync(thumbnailSlider);
    //     mainSlider.mount();
    //     thumbnailSlider.mount();
    // }

    // });
</script>

<?php
    require 'footer.php'
?>