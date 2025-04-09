// Notification function
function showNotification(message, type = 'success', title = 'Paziņojums', duration = 5000) {
    const container = document.getElementById('notification-container');
    const notification = document.getElementById('notification');
    const notificationIcon = document.getElementById('notification-icon');
    const notificationTitle = document.getElementById('notification-title');
    const notificationMessage = document.getElementById('notification-message');

    // Set the message and type
    notificationMessage.textContent = message;
    notificationTitle.textContent = title;

    // Reset classes
    notification.className = 'notification';
    notificationIcon.className = '';

    // Set type-specific styles
    if (type === 'success') {
        notification.classList.add('success');
        notificationIcon.className = 'fa-solid fa-circle-check success';
        notificationTitle.textContent = 'Veiksmīgi!';
    } else if (type === 'error') {
        notification.classList.add('error');
        notificationIcon.className = 'fa-solid fa-circle-xmark error';
        notificationTitle.textContent = 'Kļūda!';
    }

    // Show the notification
    container.style.display = 'block';

    // Hide after duration
    setTimeout(() => {
        container.style.display = 'none';
    }, duration);
}

// Check if there's a notification in URL parameters when page loads
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const notification = urlParams.get('notification');
    const notificationType = urlParams.get('type') || 'success';
    
    if (notification) {
        showNotification(decodeURIComponent(notification), notificationType);
        
        // Remove notification params from URL without reloading page
        const url = new URL(window.location);
        url.searchParams.delete('notification');
        url.searchParams.delete('type');
        window.history.replaceState({}, '', url);
    }
});


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
            url: 'admin/db/produkcija_list.php', // PHP script that fetches product data
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