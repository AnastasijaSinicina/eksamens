function showNotification(message, type = 'success') {
    // Remove existing notification if any
    const existingNotification = document.getElementById('pazinojums');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.id = 'pazinojums';
    notification.innerHTML = `
        <p>${message}</p>
        <a onclick="this.parentElement.remove()">&times;</a>
    `;

    // Add type-specific classes
    notification.classList.add('notification', type);

    // Append to body
    document.body.appendChild(notification);

    // Force center positioning with JavaScript
    function centerNotification() {
        const rect = notification.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        notification.style.position = 'fixed';
        notification.style.transform = 'none'; // Remove any transform conflicts
    }

    // Center immediately and on window resize
    centerNotification();
    window.addEventListener('resize', centerNotification);

    // Auto-remove after 4 seconds
    setTimeout(() => {
        if (notification && notification.parentElement) {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
                window.removeEventListener('resize', centerNotification);
            }, 300);
        }
    }, 4000);
}

// Usage example:
// showNotification('Veiksmīgi! Daudzums samazināts', 'success');
// showNotification('Kļūda! Neizdevās veikt darbību', 'error');

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


//-----------------------------------------------------------------------------------------PASŪTĪJUMA VEIDOŠANA-------------------------------------------------------


/*------------------------------------------------------------------PROFILS----------------------------------------------------------------------------------*/
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
    
    // Order filtering
    const filterButtons = document.querySelectorAll('.filter-button');
    const orderItems = document.querySelectorAll('.order-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterStatus = this.getAttribute('data-status');
            const filterType = this.getAttribute('data-type');
            
            // Remove active class from all filter buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter orders
            orderItems.forEach(item => {
                const itemStatus = item.getAttribute('data-status');
                const itemType = item.getAttribute('data-type');
                
                let shouldShow = false;
                
                if (filterStatus === 'all') {
                    shouldShow = true;
                } else if (filterStatus) {
                    shouldShow = itemStatus === filterStatus;
                } else if (filterType) {
                    shouldShow = itemType === filterType;
                }
                
                item.style.display = shouldShow ? 'block' : 'none';
            });
        });
    });
    
    // Order details expansion
    const expandButtons = document.querySelectorAll('.expand-button');
    expandButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderDetails = document.getElementById(this.getAttribute('data-order'));
            const icon = this.querySelector('i');
            
            if (orderDetails.style.display === 'none' || orderDetails.style.display === '') {
                orderDetails.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                this.innerHTML = '<i class="fas fa-chevron-up"></i> Paslēpt detaļas';
            } else {
                orderDetails.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
                this.innerHTML = '<i class="fas fa-chevron-down"></i> Skatīt detaļas';
            }
        });
    });
    
    // Account deletion modal
    const deleteButton = document.getElementById('delete-account-btn');
    const modal = document.getElementById('delete-confirmation');
    const cancelButton = document.getElementById('cancel-delete');
    
    if (deleteButton) {
        deleteButton.addEventListener('click', function() {
            modal.style.display = 'flex';
        });
    }
    
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    


});

// Image preview function
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const currentImage = document.querySelector('.image-preview');
            const placeholder = document.getElementById('image-placeholder');
            
            if (currentImage) {
                currentImage.src = e.target.result;
                currentImage.style.display = 'block';
                placeholder.style.display = 'none';
            } else {
                // Create new image element if it doesn't exist
                const newImg = document.createElement('img');
                newImg.src = e.target.result;
                newImg.className = 'image-preview';
                newImg.alt = 'Profila attēls';
                
                const currentImageDiv = document.querySelector('.current-image');
                currentImageDiv.insertBefore(newImg, placeholder);
                placeholder.style.display = 'none';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/