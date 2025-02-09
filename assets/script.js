document.addEventListener('DOMContentLoaded', function () {
    var mainSlider = new Splide('#main-slider', {
        type: 'fade',
        rewind: true,
        pagination: false,
        arrows: false,
    });

    var thumbnailSlider = new Splide('#thumbnail-slider', {
        fixedWidth: 100,
        fixedHeight: 60,
        gap: 10,
        rewind: true,
        pagination: false,
        cover: true,
        isNavigation: true,
        focus: 'center',
        breakpoints: {
            600: {
                fixedWidth: 60,
                fixedHeight: 44,
            },
        },
    });

    mainSlider.sync(thumbnailSlider);
    mainSlider.mount();
    thumbnailSlider.mount();
});





$(document).ready(function() {

    fetchProdukcija();

    // Function to fetch product details from the server
    function fetchProdukcija() {
        $.ajax({
            url: 'db/produkcija_list.php', // PHP script that fetches product data
            type: 'GET',
            success: function(response) {
                const bumbas = JSON.parse(response);
                let template = "";
    
                bumbas.forEach(bumba => {
                    template += `
                         <a href="produkts.php?id=${bumba.id_bumba}" class="box">
                            <img src="data:image/jpeg;base64,${bumba.attels1}" alt="${bumba.nosaukums}">
                            <h3>${bumba.nosaukums}</h3>
                            <h3>${bumba.cena}€</h3>
                        </a>
                    `;
                });
    
                $('#bumbas-container').html(template);
            },
            error: function() {
                alert("Neizdevās ielādēt datus!");
            }
        });
    }
    
});