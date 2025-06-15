const sidebar = document.querySelector(".sidebar");
const sidebarToggler = document.querySelector(".sidebar-toggler");
const menuToggler = document.querySelector(".menu-toggler");
const mainContent = document.querySelector("main");

const collapsedSidebarHeight = "56px";

// Sānjoslas pārslēgšanas funkcionalitāte
sidebarToggler.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    // Piespiedīt DOM pārzīmēšanu, lai stili tiktu pareizi piemēroti
    void mainContent.offsetWidth;
});

// Izvēlnes pārslēgšanas funkcija
const toggleMenu = (isMenuActive) => {
    sidebar.style.height = isMenuActive ? `${sidebar.scrollHeight}px` : collapsedSidebarHeight;
}

// Mobilās izvēlnes pārslēgšana
menuToggler.addEventListener("click", () => {
    const isActive = sidebar.classList.toggle("menu-active");
    toggleMenu(isActive);
    // Piespiedīt DOM pārzīmēšanu arī mobilajā skatā
    void mainContent.offsetWidth;
});

// Apstiprināšanas modāla rādīšana
function showConfirmModal(message, onConfirm, onCancel = null) {
    const modal = document.getElementById('confirmModal');
    const messageElement = document.getElementById('confirmMessage');
    const yesButton = document.getElementById('confirmYes');
    const noButton = document.getElementById('confirmNo');
    
    messageElement.textContent = message;
    modal.style.display = 'block';
    
    // Noņemt esošos notikumu klausītājus
    const newYesButton = yesButton.cloneNode(true);
    const newNoButton = noButton.cloneNode(true);
    yesButton.parentNode.replaceChild(newYesButton, yesButton);
    noButton.parentNode.replaceChild(newNoButton, noButton);
    
    // Pievienot jaunus notikumu klausītājus
    document.getElementById('confirmYes').addEventListener('click', function() {
        hideConfirmModal();
        if (onConfirm) onConfirm();
    });
    
    document.getElementById('confirmNo').addEventListener('click', function() {
        hideConfirmModal();
        if (onCancel) onCancel();
    });
}

// Apstiprināšanas modāla paslēpšana
function hideConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

// Aizvērt apstiprināšanas modāli, ja noklikšķina ārpus tā
window.addEventListener('click', function(event) {
    const confirmModal = document.getElementById('confirmModal');
    if (event.target === confirmModal) {
        hideConfirmModal();
    }
});

// Globālais lapošanas stāvoklis
let currentPage = 1;
let totalPages = 1;
let totalRecords = 0;
const itemsPerPage = 6; // Fiksēts visām lapām

// Pašreizējās lapas konfigurācija
let currentLoadFunction = '';
let currentTableBodyId = '';
let currentFilters = [];

/**
 * Inicializēt lapošanas sistēmu produktu lapai
 */
function initializeProductsPagination() {
    currentLoadFunction = 'db/produkcija_admin.php';
    currentTableBodyId = 'products-tbody';
    currentFilters = ['searchInput', 'filterSelect'];
    
    loadData();
    setupFilterListeners();
}

/**
 * Inicializēt lapošanas sistēmu pasūtījumu lapai
 */
function initializeOrdersPagination() {
    currentLoadFunction = 'db/pasutijumi_admin.php';
    currentTableBodyId = 'orders-tbody';
    currentFilters = ['status', 'search', 'date_from', 'date_to'];
    
    loadData();
    setupFilterListeners();
}

/**
 * Inicializēt lapošanas sistēmu individuālo pasūtījumu lapai
 */
function initializeCustomOrdersPagination() {
    currentLoadFunction = 'db/spec_pas_admin.php';
    currentTableBodyId = 'orders-tbody';
    currentFilters = ['status', 'search', 'date_from', 'date_to'];
    
    loadData();
    setupFilterListeners();
}

/**
 * Universāla datu ielādēšanas funkcija
 */
function loadData(page = 1) {
    if (!currentLoadFunction) {
        console.error('Lapošana nav inicializēta');
        return;
    }
    
    currentPage = page;
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('page', page);
    
    // Pievienot filtru vērtības
    currentFilters.forEach(filterId => {
        const element = document.getElementById(filterId);
        if (element) {
            const paramName = filterId === 'searchInput' ? 'search' : filterId;
            formData.append(paramName, element.value);
        }
    });
    
    // Rādīt ielādēšanas indikatoru
    showLoadingIndicator(true);
    
    fetch(currentLoadFunction, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        updateTableContent(html);
        showLoadingIndicator(false);
        extractPaginationInfo();
        updatePagination();
    })
    .catch(error => {
        console.error('Kļūda ielādējot datus:', error);
        showLoadingIndicator(false);
        showNotification('error', 'Kļūda!', 'Neizdevās ielādēt datus.');
    });
}

/**
 * Atjaunināt tabulas saturu ar jauno HTML
 */
function updateTableContent(html) {
    const tbody = document.getElementById(currentTableBodyId);
    if (tbody) {
        tbody.innerHTML = html;
        tbody.style.opacity = '1';
    }
}

/**
 * Rādīt/paslēpt ielādēšanas indikatoru
 */
function showLoadingIndicator(show) {
    const indicator = document.getElementById('loading-indicator');
    const tbody = document.getElementById(currentTableBodyId);
    
    if (indicator) {
        indicator.style.display = show ? 'block' : 'none';
    }
    
    if (tbody) {
        tbody.style.opacity = show ? '0.5' : '1';
    }
}

/**
 * Izgūt lapošanas informāciju no slēptajiem datu atribūtiem
 */
function extractPaginationInfo() {
    const hiddenRow = document.querySelector('[data-current-page]');
    if (hiddenRow) {
        currentPage = parseInt(hiddenRow.dataset.currentPage) || 1;
        totalPages = parseInt(hiddenRow.dataset.totalPages) || 1;
        totalRecords = parseInt(hiddenRow.dataset.totalRecords) || 0;
        hiddenRow.remove();
    }
}

/**
 * Atjaunināt lapošanas vadību un informāciju
 */
function updatePagination() {
    const paginationContainer = document.getElementById('pagination-container');
    const paginationText = document.getElementById('pagination-text');
    const paginationControls = document.getElementById('pagination-controls');
    
    if (!paginationContainer || !paginationText || !paginationControls) {
        return;
    }
    
    if (totalRecords === 0) {
        paginationContainer.style.display = 'none';
        return;
    }
    
    paginationContainer.style.display = 'flex';
    
    // Atjaunināt lapošanas tekstu
    const startRecord = ((currentPage - 1) * itemsPerPage) + 1;
    const endRecord = Math.min(currentPage * itemsPerPage, totalRecords);
    paginationText.textContent = `Rāda ${startRecord}-${endRecord} no ${totalRecords} ierakstiem`;
    
    // Ģenerēt lapošanas pogas
    paginationControls.innerHTML = generatePaginationButtons();
}

/**
 * Ģenerēt lapošanas pogu HTML
 */
function generatePaginationButtons() {
    let html = '';
    
    // Iepriekšējā poga
    if (currentPage > 1) {
        html += `<button class="pagination-btn" onclick="loadData(${currentPage - 1})">
            <i class="fas fa-chevron-left"></i> Iepriekšējā
        </button>`;
    }
    
    // Lapu numuri
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    // Pielāgot sākumu, ja esam tuvu beigām
    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // Pirmā lapa
    if (startPage > 1) {
        html += `<button class="pagination-btn page-number" onclick="loadData(1)">1</button>`;
        if (startPage > 2) {
            html += `<span class="pagination-dots">...</span>`;
        }
    }
    
    // Lapu numuri
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === currentPage ? 'active' : '';
        html += `<button class="pagination-btn page-number ${activeClass}" onclick="loadData(${i})">${i}</button>`;
    }
    
    // Pēdējā lapa
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span class="pagination-dots">...</span>`;
        }
        html += `<button class="pagination-btn page-number" onclick="loadData(${totalPages})">${totalPages}</button>`;
    }
    
    // Nākamā poga
    if (currentPage < totalPages) {
        html += `<button class="pagination-btn" onclick="loadData(${currentPage + 1})">
            Nākamā <i class="fas fa-chevron-right"></i>
        </button>`;
    }
    
    return html;
}

/**
 * Iestatīt filtru notikumu klausītājus
 */
function setupFilterListeners() {
    // Meklēšanas ievade ar aizkavi
    let searchTimeout;
    const searchElements = ['searchInput', 'search'];
    
    searchElements.forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadData(1); // Atiestatīt uz pirmo lapu, meklējot
                }, 500);
            });
        }
    });
    
    // Automātiskā filtrēšana, mainoties izvēles laukos
    const selectElements = ['status', 'filterSelect'];
    selectElements.forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener('change', function() {
                loadData(1); // Atiestatīt uz pirmo lapu, filtrējot
            });
        }
    });
    
    // Automātiskā filtrēšana, mainoties datumiem
    const dateElements = ['date_from', 'date_to'];
    dateElements.forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener('change', function() {
                loadData(1); // Atiestatīt uz pirmo lapu, filtrējot
            });
        }
    });
}

/**
 * Rādīt paziņojuma ziņojumu
 */
function showNotification(type, title, message) {
    const container = document.querySelector('.notification-container');
    if (!container) return;
    
    // Vispirms notīrīt esošos paziņojumus, lai novērstu uzkrāšanos
    container.innerHTML = '';
    
    // FIX: Pārbaudīt un nodrošināt, ka ziņojums nav undefined
    const displayMessage = message || 'Darbība pabeigta.';
    const displayTitle = title || (type === 'success' ? 'Veiksmīgi!' : 'Kļūda!');
    
    // Izveidot paziņojuma elementu
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.innerHTML = `
        <i class="${type === 'success' ? 'fas fa-check-circle success' : 'fas fa-exclamation-circle error'}"></i>
        <div>
            <h3>${displayTitle}</h3>
            <p>${displayMessage}</p>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove(); checkNotificationContainer();">×</button>
    `;
    
    // Pievienot konteineram un rādīt
    container.appendChild(notification);
    container.style.display = 'block';
    
    // Automātiski paslēpt pēc 5 sekundēm
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
            checkNotificationContainer();
        }
    }, 5000);
}

/**
 * Pārbaudīt, vai paziņojumu konteiners jāpaslēpj
 */
function checkNotificationContainer() {
    const container = document.querySelector('.notification-container');
    if (container && container.children.length === 0) {
        container.style.display = 'none';
    }
}

/**
 * Produkta dzēšanas funkcija (produktu lapai)
 */
function deleteProduct(productId, productName) {
    showConfirmModal(
        `Vai tiešām vēlaties dzēst produktu "${productName}"? Šī darbība ir neatgriezeniska.`,
        function() {
            // Apstiprināts - turpināt ar dzēšanu caur AJAX
            const formData = new FormData();
            formData.append('delete_product', '1');
            formData.append('id', productId);
            
            fetch('db/produkcija_delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('success', 'Veiksmīgi!', data.message);
                    // Pārlādēt pašreizējo lapu, lai saglabātu lapošanu
                    loadData(currentPage);
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Kļūda dzēšot produktu:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās dzēst produktu.');
            });
        }
    );
}

/**
 * Iestatīt attēlu klikšķu apstrādātājus (produktu lapai)
 */
function setupImageClickHandlers() {
    document.querySelectorAll('.product-thumbnail').forEach(img => {
        img.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 90%; max-height: 90%;">
                    <div class="modal-header">
                        <h2>Produkta attēls</h2>
                        <span class="close">&times;</span>
                    </div>
                    <div class="modal-body" style="text-align: center;">
                        <img src="${this.src}" alt="${this.alt}" style="max-width: 100%; max-height: 70vh;">
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            modal.style.display = 'flex';
            
            // Modāla aizvēršanas notikumi
            modal.querySelector('.close').onclick = () => {
                document.body.removeChild(modal);
            };
            
            modal.onclick = (e) => {
                if (e.target === modal) {
                    document.body.removeChild(modal);
                }
            };
        });
    });
}
/**
 * Atjaunināt pasūtījuma statusu
 */
function updateOrderStatus(orderId, newStatus, orderElement = null) {
    // Pārbaudīt, vai ir nepieciešamie dati
    if (!orderId || !newStatus) {
        console.error('Trūkst dati:', { orderId, newStatus });
        showNotification('error', 'Kļūda!', 'Trūkst nepieciešamie dati');
        return;
    }
    
    // Pārveidot orderId uz skaitli, ja nepieciešams
    const orderIdNum = parseInt(orderId);
    if (isNaN(orderIdNum)) {
        console.error('Nederīgs pasūtījuma ID:', orderId);
        showNotification('error', 'Kļūda!', 'Nederīgs pasūtījuma ID');
        return;
    }
    
    const formData = new FormData();
    formData.append('update_status', '1');
    formData.append('order_id', orderIdNum.toString());
    formData.append('new_status', newStatus.toString());
    
    // Izmantot pareizo PHP failu atkarībā no pašreizējās lapas
    let phpFile = 'db/pasutijumi_admin.php'; // noklusējuma fails
    if (currentLoadFunction && currentLoadFunction.includes('spec_pas_admin.php')) {
        phpFile = 'db/spec_pas_admin.php';
    }
    
    console.log('Sūtu datus:', {
        order_id: orderIdNum,
        new_status: newStatus,
        file: phpFile
    });
    
    // Rādīt ielādēšanas stāvokli, ja elements norādīts
    if (orderElement) {
        orderElement.style.opacity = '0.5';
        orderElement.style.pointerEvents = 'none';
    }
    
    fetch(phpFile, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Atbilde saņemta:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Atbildes teksts:', text);
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                // FIX: Pievienot noklusējuma ziņojumu, ja nav definēts
                const successMessage = data.message || 'Pasūtījuma statuss ir veiksmīgi atjaunināts.';
                showNotification('success', 'Veiksmīgi!', successMessage);
                // Pārlādēt pašreizējo lapu, lai rādītu atjaunināto statusu
                if (typeof loadData === 'function') {
                    loadData(currentPage);
                } else {
                    location.reload();
                }
            } else {
                // FIX: Pievienot noklusējuma kļūdas ziņojumu
                const errorMessage = data.message || 'Neizdevās atjaunināt pasūtījuma statusu.';
                showNotification('error', 'Kļūda!', errorMessage);
            }
        } catch (e) {
            console.error('JSON parsēšanas kļūda:', e);
            console.error('Saņemtais teksts:', text);
            showNotification('error', 'Kļūda!', 'Serveris atgrieza nederīgu atbildi');
        }
        
        // Atiestatīt elementa stāvokli
        if (orderElement) {
            orderElement.style.opacity = '1';
            orderElement.style.pointerEvents = 'auto';
        }
    })
    .catch(error => {
        console.error('Fetch kļūda:', error);
        showNotification('error', 'Kļūda!', 'Neizdevās sazināties ar serveri.');
        // Atiestatīt elementa stāvokli kļūdas gadījumā
        if (orderElement) {
            orderElement.style.opacity = '1';
            orderElement.style.pointerEvents = 'auto';
        }
    });
}
/**
 * Atjaunināt pasūtījuma statusu ar apstiprināšanu
 */
function updateOrderStatusWithConfirm(orderId, newStatus, orderElement = null) {
    const statusLabels = {
        'Jauns': 'Jauns',
        'Apstrādē': 'Apstrādē', 
        'Nosūtīts': 'Nosūtīts',
        'Pabeigts': 'Pabeigts',
        'Atcelts': 'Atcelts'
    };
    
    const statusLabel = statusLabels[newStatus] || newStatus;
    
    showConfirmModal(
        `Vai tiešām vēlaties mainīt pasūtījuma statusu uz "${statusLabel}"?`,
        function() {
            updateOrderStatus(orderId, newStatus, orderElement);
        }
    );
}

/**
 * Globālie notikumu klausītāji
 */
window.addEventListener('click', function(event) {
    const confirmModal = document.getElementById('confirmModal');
    if (event.target === confirmModal) {
        hideConfirmModal();
    }
});

// Pievienot paziņojuma automātiskās paslēpšanas kodu
const adminNotification = document.getElementById('admin-notification');
if (adminNotification) {
    setTimeout(function() {
        adminNotification.classList.add('fade-out');
        setTimeout(function() {
            adminNotification.style.display = 'none';
        }, 300);
    }, 3000);
}

// Tastatūras saīsnes
document.addEventListener('keydown', function(e) {
    // Escape, lai aizvērtu modāļus
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal[style*="display: flex"], .modal[style*="display: block"]');
        modals.forEach(modal => {
            modal.style.display = 'none';
            if (modal.parentNode && !modal.id) {
                modal.parentNode.removeChild(modal);
            }
        });
        hideConfirmModal();
    }
});

// Padarīt funkcijas globāli pieejamas
window.loadData = loadData;
window.showNotification = showNotification;
window.showConfirmModal = showConfirmModal;
window.hideConfirmModal = hideConfirmModal;
window.updateOrderStatus = updateOrderStatus;
window.updateOrderStatusWithConfirm = updateOrderStatusWithConfirm;
window.deleteProduct = deleteProduct;
window.setupImageClickHandlers = setupImageClickHandlers;
window.initializeProductsPagination = initializeProductsPagination;
window.initializeOrdersPagination = initializeOrdersPagination;
window.initializeCustomOrdersPagination = initializeCustomOrdersPagination;