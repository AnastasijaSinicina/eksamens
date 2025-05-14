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

document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('menu-btn');
    const nav = document.querySelector('header .nav');
    
    menuBtn.addEventListener('click', function() {
        nav.classList.toggle('active');
        this.querySelector('i').classList.toggle('fa-bars');
        this.querySelector('i').classList.toggle('fa-times');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!nav.contains(event.target) && !menuBtn.contains(event.target) && nav.classList.contains('active')) {
            nav.classList.remove('active');
            menuBtn.querySelector('i').classList.add('fa-bars');
            menuBtn.querySelector('i').classList.remove('fa-times');
        }
    });
});


document.addEventListener('DOMContentLoaded', function() {
    const paymentBtn = document.getElementById('payment-btn');
    const confirmOrderBtn = document.getElementById('confirm-order-btn');
    const paymentModal = document.getElementById('payment-modal');
    const modalClose = document.querySelector('.modal-close');
    const cancelPaymentBtn = document.getElementById('cancel-payment');
    const confirmPaymentBtn = document.getElementById('confirm-payment');
    const checkoutForm = document.getElementById('checkout-form');
    const deliveryRadios = document.querySelectorAll('input[name="piegades_veids"]');
    const hiddenPaymentInput = document.getElementById('apmaksas-veids-input');
    
    let paymentMethodSelected = false;
    
    // Adreses lauki
    const addressGroup = document.getElementById('address-group');
    const cityGroup = document.getElementById('city-group');
    const postalGroup = document.getElementById('postal-group');
    const addressInput = document.getElementById('adrese');
    const cityInput = document.getElementById('pilseta');
    const postalInput = document.getElementById('pasta_indekss');
    
    // Funkcija piegādes veida maiņas apstrādei
    function handleDeliveryChange() {
        const selectedDelivery = document.querySelector('input[name="piegades_veids"]:checked').value;
        
        if (selectedDelivery === 'Pats') {
            // Slēpj adreses laukus pašizvešanas gadījumā
            addressGroup.classList.add('address-hidden');
            cityGroup.classList.add('address-hidden');
            postalGroup.classList.add('address-hidden');
            
            // Noņem obligāto atribūtu no adreses laukiem
            addressInput.removeAttribute('required');
            cityInput.removeAttribute('required');
            postalInput.removeAttribute('required');
        } else {
            // Rāda adreses laukus kurjera piegādei
            addressGroup.classList.remove('address-hidden');
            cityGroup.classList.remove('address-hidden');
            postalGroup.classList.remove('address-hidden');
            
            // Pievieno obligāto atribūtu adreses laukiem
            addressInput.setAttribute('required', 'required');
            cityInput.setAttribute('required', 'required');
            postalInput.setAttribute('required', 'required');
        }
        
        // Atiestatīt maksājuma veida izvēli, kad mainās piegādes veids
        paymentMethodSelected = false;
        paymentBtn.style.display = 'block';
        confirmOrderBtn.style.display = 'none';
        hiddenPaymentInput.value = '';
        
        // Slēpj maksājuma veida attēlošanas lauku
        const paymentDisplayGroup = document.getElementById('payment-method-display');
        const paymentDisplayField = document.getElementById('selected-payment-method');
        paymentDisplayGroup.style.display = 'none';
        paymentDisplayField.value = '';
    }

    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', handleDeliveryChange);
    });

    handleDeliveryChange();
    
    // Funkcija modālā satura atjaunošanai atkarībā no piegādes veida
    function updateModalContent(deliveryMethod) {
        const modalHeader = paymentModal.querySelector('.modal-header h2');
        const modalBody = paymentModal.querySelector('.modal-body');
        
        if (deliveryMethod === 'Kurjers') {
            modalHeader.textContent = 'Maksājuma informācija';
            modalBody.innerHTML = `
                <div class="payment-description">
                    <div class="courier-notice">
                        <i class="fas fa-truck"></i>
                        <h3>Kurjera piegāde</h3>
                        <p>Kurjera piegādei pieejams tikai maksājums ar bankas karti. Maksājums tiks veikts tagad, tiešsaitē.</p>
                    </div>
                    <div class="payment-options">
                        <label class="payment-option selected">
                            <input type="radio" name="modal_payment" value="Bankas karte" checked disabled>
                            <span class="payment-label">
                                <i class="fas fa-credit-card"></i>
                                Bankas karte
                            </span>
                        </label>
                    </div>
                </div>
            `;
        } else {
            modalHeader.textContent = 'Izvēlieties maksājuma veidu';
            modalBody.innerHTML = `
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="modal_payment" value="Bankas karte" checked>
                        <span class="payment-label">
                            <i class="fas fa-credit-card"></i>
                            Bankas karte
                        </span>
                    </label>
                    
                    <label class="payment-option">
                        <input type="radio" name="modal_payment" value="Skaidra nauda">
                        <span class="payment-label">
                            <i class="fas fa-money-bill"></i>
                            Skaidra nauda
                        </span>
                    </label>
                </div>
            `;
        }
    }
    
    // Apstrādā maksājuma pogas klikšķi
    paymentBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Pārbauda, vai forma ir derīga
        if (!checkoutForm.checkValidity()) {
            checkoutForm.reportValidity();
            return;
        }
        
        const selectedDelivery = document.querySelector('input[name="piegades_veids"]:checked').value;
        
        // Atjaunina modālā saturu atkarībā no piegādes veida
        updateModalContent(selectedDelivery);
        
        paymentModal.style.display = 'block';
    });
    
    // Apstrādā maksājuma apstiprināšanu modālajā logā
    confirmPaymentBtn.addEventListener('click', function() {
        let selectedPayment;
        const selectedDelivery = document.querySelector('input[name="piegades_veids"]:checked').value;
        
        if (selectedDelivery === 'Kurjers') {
            selectedPayment = 'Bankas karte';
        } else {
            const paymentInput = document.querySelector('input[name="modal_payment"]:checked');
            selectedPayment = paymentInput ? paymentInput.value : 'Bankas karte';
        }
        
        hiddenPaymentInput.value = selectedPayment;
        paymentModal.style.display = 'none';
        
        paymentBtn.style.display = 'none';
        confirmOrderBtn.style.display = 'block';
        
        // Rāda un aizpilda maksājuma veida attēlošanas lauku
        const paymentDisplayGroup = document.getElementById('payment-method-display');
        const paymentDisplayField = document.getElementById('selected-payment-method');
        paymentDisplayGroup.style.display = 'block';
        paymentDisplayField.value = selectedPayment;
        
        paymentMethodSelected = true;
    });

    function closeModal() {
        paymentModal.style.display = 'none';
    }
    
    modalClose.addEventListener('click', closeModal);
    cancelPaymentBtn.addEventListener('click', closeModal);
    

    window.addEventListener('click', function(e) {
        if (e.target === paymentModal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && paymentModal.style.display === 'block') {
            closeModal();
        }
    });
});