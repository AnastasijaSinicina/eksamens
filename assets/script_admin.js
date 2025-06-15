const sidebar = document.querySelector(".sidebar");
const sidebarToggler = document.querySelector(".sidebar-toggler");
const menuToggler = document.querySelector(".menu-toggler");
const mainContent = document.querySelector("main");



const collapsedSidebarHeight = "56px";

sidebarToggler.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    // Force DOM reflow to ensure styles are applied correctly
    void mainContent.offsetWidth;
});

const toggleMenu = (isMenuActive) => {
    sidebar.style.height = isMenuActive ? `${sidebar.scrollHeight}px` : collapsedSidebarHeight;
}

menuToggler.addEventListener("click", () => {
    const isActive = sidebar.classList.toggle("menu-active");
    toggleMenu(isActive);
    // Force DOM reflow for mobile view as well
    void mainContent.offsetWidth;
});

 function showConfirmModal(message, onConfirm, onCancel = null) {
        const modal = document.getElementById('confirmModal');
        const messageElement = document.getElementById('confirmMessage');
        const yesButton = document.getElementById('confirmYes');
        const noButton = document.getElementById('confirmNo');
        
        messageElement.textContent = message;
        modal.style.display = 'block';
        
        // Remove any existing event listeners
        const newYesButton = yesButton.cloneNode(true);
        const newNoButton = noButton.cloneNode(true);
        yesButton.parentNode.replaceChild(newYesButton, yesButton);
        noButton.parentNode.replaceChild(newNoButton, noButton);
        
        // Add new event listeners
        document.getElementById('confirmYes').addEventListener('click', function() {
            hideConfirmModal();
            if (onConfirm) onConfirm();
        });
        
        document.getElementById('confirmNo').addEventListener('click', function() {
            hideConfirmModal();
            if (onCancel) onCancel();
        });
    }

    function hideConfirmModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    // Close confirmation modal when clicking outside
    window.addEventListener('click', function(event) {
        const confirmModal = document.getElementById('confirmModal');
        if (event.target === confirmModal) {
            hideConfirmModal();
        }
    });



// Global pagination state
let currentPage = 1;
let totalPages = 1;
let totalRecords = 0;
const itemsPerPage = 6; // Fixed for all pages

// Current page configuration
let currentLoadFunction = '';
let currentTableBodyId = '';
let currentFilters = [];

/**
 * Initialize pagination system for a specific page
 */
function initializeProductsPagination() {
    currentLoadFunction = 'db/produkcija_admin.php';
    currentTableBodyId = 'products-tbody';
    currentFilters = ['searchInput', 'filterSelect'];
    
    loadData();
    setupFilterListeners();
}

function initializeOrdersPagination() {
    currentLoadFunction = 'db/pasutijumi_admin.php';
    currentTableBodyId = 'orders-tbody';
    currentFilters = ['status', 'search', 'date_from', 'date_to'];
    
    loadData();
    setupFilterListeners();
}

function initializeCustomOrdersPagination() {
    currentLoadFunction = 'db/spec_pas_admin.php';
    currentTableBodyId = 'orders-tbody';
    currentFilters = ['status', 'search', 'date_from', 'date_to'];
    
    loadData();
    setupFilterListeners();
}

/**
 * Generic data loading function
 */
function loadData(page = 1) {
    if (!currentLoadFunction) {
        console.error('Pagination not initialized');
        return;
    }
    
    currentPage = page;
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('page', page);
    
    // Add filter values
    currentFilters.forEach(filterId => {
        const element = document.getElementById(filterId);
        if (element) {
            const paramName = filterId === 'searchInput' ? 'search' : filterId;
            formData.append(paramName, element.value);
        }
    });
    
    // Show loading indicator
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
        console.error('Error loading data:', error);
        showLoadingIndicator(false);
        showNotification('error', 'Kļūda!', 'Neizdevās ielādēt datus.');
    });
}

/**
 * Update table content with new HTML
 */
function updateTableContent(html) {
    const tbody = document.getElementById(currentTableBodyId);
    if (tbody) {
        tbody.innerHTML = html;
        tbody.style.opacity = '1';
    }
}

/**
 * Show/hide loading indicator
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
 * Extract pagination info from hidden data attributes
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
 * Update pagination controls and info
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
    
    // Update pagination text
    const startRecord = ((currentPage - 1) * itemsPerPage) + 1;
    const endRecord = Math.min(currentPage * itemsPerPage, totalRecords);
    paginationText.textContent = `Rāda ${startRecord}-${endRecord} no ${totalRecords} ierakstiem`;
    
    // Generate pagination buttons
    paginationControls.innerHTML = generatePaginationButtons();
}

/**
 * Generate pagination buttons HTML
 */
function generatePaginationButtons() {
    let html = '';
    
    // Previous button
    if (currentPage > 1) {
        html += `<button class="pagination-btn" onclick="loadData(${currentPage - 1})">
            <i class="fas fa-chevron-left"></i> Iepriekšējā
        </button>`;
    }
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    // Adjust start if we're near the end
    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // First page
    if (startPage > 1) {
        html += `<button class="pagination-btn page-number" onclick="loadData(1)">1</button>`;
        if (startPage > 2) {
            html += `<span class="pagination-dots">...</span>`;
        }
    }
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === currentPage ? 'active' : '';
        html += `<button class="pagination-btn page-number ${activeClass}" onclick="loadData(${i})">${i}</button>`;
    }
    
    // Last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span class="pagination-dots">...</span>`;
        }
        html += `<button class="pagination-btn page-number" onclick="loadData(${totalPages})">${totalPages}</button>`;
    }
    
    // Next button
    if (currentPage < totalPages) {
        html += `<button class="pagination-btn" onclick="loadData(${currentPage + 1})">
            Nākamā <i class="fas fa-chevron-right"></i>
        </button>`;
    }
    
    return html;
}

/**
 * Setup filter event listeners
 */
function setupFilterListeners() {
    // Search input with debounce
    let searchTimeout;
    const searchElements = ['searchInput', 'search'];
    
    searchElements.forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadData(1); // Reset to first page when searching
                }, 500);
            });
        }
    });
    
    // Auto-filter on select changes
    const selectElements = ['status', 'filterSelect'];
    selectElements.forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener('change', function() {
                loadData(1); // Reset to first page when filtering
            });
        }
    });
    
    // Auto-filter on date changes
    const dateElements = ['date_from', 'date_to'];
    dateElements.forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener('change', function() {
                loadData(1); // Reset to first page when filtering
            });
        }
    });
}

/**
 * Show notification message
 */
function showNotification(type, title, message) {
    const container = document.querySelector('.notification-container');
    if (!container) return;
    
    // Clear any existing notifications first to prevent stacking
    container.innerHTML = '';
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.innerHTML = `
        <i class="${type === 'success' ? 'fas fa-check-circle success' : 'fas fa-exclamation-circle error'}"></i>
        <div>
            <h3>${title}</h3>
            <p>${message}</p>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove(); checkNotificationContainer();">×</button>
    `;
    
    // Add to container and show
    container.appendChild(notification);
    container.style.display = 'block';
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
            checkNotificationContainer();
        }
    }, 5000);
}

/**
 * Check if notification container should be hidden
 */
function checkNotificationContainer() {
    const container = document.querySelector('.notification-container');
    if (container && container.children.length === 0) {
        container.style.display = 'none';
    }
}

/**
 * Reusable confirmation modal functions
 */
function showConfirmModal(message, onConfirm, onCancel = null) {
    const modal = document.getElementById('confirmModal');
    if (!modal) return;
    
    const messageElement = document.getElementById('confirmMessage');
    const yesButton = document.getElementById('confirmYes');
    const noButton = document.getElementById('confirmNo');
    
    if (messageElement) messageElement.textContent = message;
    modal.style.display = 'block';
    
    // Remove any existing event listeners
    const newYesButton = yesButton.cloneNode(true);
    const newNoButton = noButton.cloneNode(true);
    yesButton.parentNode.replaceChild(newYesButton, yesButton);
    noButton.parentNode.replaceChild(newNoButton, noButton);
    
    // Add new event listeners
    document.getElementById('confirmYes').addEventListener('click', function() {
        hideConfirmModal();
        if (onConfirm) onConfirm();
    });
    
    document.getElementById('confirmNo').addEventListener('click', function() {
        hideConfirmModal();
        if (onCancel) onCancel();
    });
}

function hideConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) modal.style.display = 'none';
}

/**
 * Product deletion function (for products page)
 */
function deleteProduct(productId, productName) {
    showConfirmModal(
        `Vai tiešām vēlaties dzēst produktu "${productName}"? Šī darbība ir neatgriezeniska.`,
        function() {
            // Confirmed - proceed with deletion via AJAX
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
                    // Reload current page to maintain pagination
                    loadData(currentPage);
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting product:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās dzēst produktu.');
            });
        }
    );
}

/**
 * Setup image click handlers (for products page)
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
            
            // Close modal events
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
 * Global event listeners
 */
 window.addEventListener('click', function(event) {
        const confirmModal = document.getElementById('confirmModal');
        if (event.target === confirmModal) {
            hideConfirmModal();
        }
    });
    
    // Add the notification auto-hide code here:
    const adminNotification = document.getElementById('admin-notification');
    if (adminNotification) {
        setTimeout(function() {
            adminNotification.classList.add('fade-out');
            setTimeout(function() {
                adminNotification.style.display = 'none';
            }, 300);
        }, 3000);
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape to close modals
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
        
        // Focus search with Ctrl/Cmd + F
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.getElementById('searchInput') || document.getElementById('search');
            if (searchInput) searchInput.focus();
        }
    
});


window.loadData = loadData;
window.showNotification = showNotification;
window.showConfirmModal = showConfirmModal;
window.hideConfirmModal = hideConfirmModal;
window.deleteProduct = deleteProduct;
window.setupImageClickHandlers = setupImageClickHandlers;
window.initializeProductsPagination = initializeProductsPagination;
window.initializeOrdersPagination = initializeOrdersPagination;
window.initializeCustomOrdersPagination = initializeCustomOrdersPagination;
