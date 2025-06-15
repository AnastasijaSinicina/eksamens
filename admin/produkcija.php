<?php
// admin/produkcija.php
// Main product management page - displays product list only

require 'header.php';

// Load product data
require 'db/produkcija_admin.php';
?>

<main>
    <!-- Notification container -->
    <div class="notification-container" style="display: none;">
        <div class="notification">
            <i class="fas fa-check-circle success"></i>
            <h3>Veiksmīgi!</h3>
            <p>Darbība veiksmīgi izpildīta.</p>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="confirm-modal">
        <div class="confirm-modal-content">
            <div class="confirm-modal-header">
                <div class="confirm-modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="confirm-modal-title">Apstiprināt dzēšanu</h3>
            </div>
            <div class="confirm-modal-body">
                <p class="confirm-modal-message" id="confirmMessage">
                    Vai tiešām vēlaties dzēst šo produktu?
                </p>
                <div class="confirm-modal-buttons">
                    <button class="confirm-btn confirm-btn-danger" id="confirmYes">
                        <i class="fas fa-trash-alt"></i> Dzēst
                    </button>
                    <button class="confirm-btn confirm-btn-cancel" id="confirmNo">
                        <i class="fas fa-times"></i> Atcelt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section class="admin-content">
        <div class="page-header">
            <h1>Produktu pārvaldība</h1>
            <a href="produkcija_form.php" class="btn">
                <i class="fas fa-plus"></i> Pievienot jaunu produktu
            </a>
        </div>

        <!-- Filters Section -->
        <div class="filters-container">
            <form id="filters-form" class="filters-form">
                <div class="filter-group">
                    <label for="searchInput">Meklēt:</label>
                    <input type="text" id="searchInput" name="search" placeholder="Produkta nosaukums vai ID" class="search-input">
                </div>
                
            </form>
        </div>
        
        <!-- Product Table -->
        <div class="product-table-container">
            <div id="loading-indicator" style="display: none; text-align: center; padding: 20px;">
                <i class="fas fa-spinner fa-spin"></i> Ielādē...
            </div>
            
            <div class="table-responsive">
                <table class="product-table" id="productTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Attēls</th>
                            <th>Nosaukums</th>
                            <th>Forma</th>
                            <th>Audums</th>
                            <th>Malu figūra</th>
                            <th>Dekorējums</th>
                            <th>Cena</th>
                            <th>Izveidots</th>
                            <th>Atjaunināts</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody">     
                        <?php
                        if ($products_result && $products_result->num_rows > 0) {
                            while ($row = $products_result->fetch_assoc()) {
                                echo "<tr data-product-id='{$row['id_bumba']}'>";
                                echo "<td>{$row['id_bumba']}</td>";
                                
                                // Display first image as preview
                                if (!empty($row['attels1'])) {
                                    echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['attels1']) . "' alt='" . htmlspecialchars($row['nosaukums']) . "' class='product-thumbnail'></td>";
                                } else {
                                    echo "<td><div class='no-image'><i class='fas fa-image'></i></div></td>";
                                }
                                
                                echo "<td class='product-name'>" . htmlspecialchars($row['nosaukums']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['forma_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['audums_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['malu_figura_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['dekorejums1_name']) . "</td>";
                                echo "<td class='price'>€" . number_format($row['cena'], 2) . "</td>";
                                
                                // Created info
                                $created_info = '';
                                if (!empty($row['created_first_name']) && !empty($row['created_last_name'])) {
                                    $created_info .= htmlspecialchars($row['created_first_name'] . ' ' . $row['created_last_name']);
                                } elseif (!empty($row['izveidots_liet'])) {
                                    $created_info .= 'Lietotājs ID: ' . $row['izveidots_liet'];
                                }
                                
                                if (!empty($row['created_at'])) {
                                    $created_info .= '<br><small>' . date('d.m.Y H:i', strtotime($row['created_at'])) . '</small>';
                                }
                                echo "<td class='metadata'>" . ($created_info) . "</td>";
                                
                                // Updated info
                                $updated_info = '';
                                if (!empty($row['updated_first_name']) && !empty($row['updated_last_name'])) {
                                    $updated_info .= htmlspecialchars($row['updated_first_name'] . ' ' . $row['updated_last_name']);
                                } elseif (!empty($row['red_liet'])) {
                                    $updated_info .= 'Lietotājs ID: ' . $row['red_liet'];
                                } else {
                                    $updated_info = '';
                                }
                                
                                if (!empty($row['updated_at'])) {
                                    $updated_info .= ($updated_info ? '<br>' : '') . '<small>' . date('d.m.Y H:i', strtotime($row['updated_at'])) . '</small>';
                                }
                                echo "<td class='metadata'>" . ($updated_info ?: 'Nav atjaunināts') . "</td>";
                                
                                // Action buttons
                                echo "<td class='action-buttons'>";
                                echo "<a href='produkcija_form.php?edit={$row['id_bumba']}' class='btn edit-btn' title='Rediģēt produktu'>";
                                echo "<i class='fas fa-edit'></i>";
                                echo "</a>";
                                echo "<button onclick='deleteProduct({$row['id_bumba']}, \"" . htmlspecialchars(addslashes($row['nosaukums'])) . "\")' class='btn delete-btn' title='Dzēst produktu'>";
                                echo "<i class='fas fa-trash-alt'></i>";
                                echo "</button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='11' class='no-records'>";
                            echo "<div class='empty-state'>";
                            echo "<i class='fas fa-box-open'></i>";
                            echo "<h3>Nav atrasts neviens produkts</h3>";
                            echo "<p>Sāciet, pievienojot savu pirmo produktu.</p>";
                            echo "<a href='produkcija_form.php' class='btn add-btn'>";
                            echo "<i class='fas fa-plus'></i> Pievienot produktu";
                            echo "</a>";
                            echo "</div>";
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Container -->
            <div class="pagination-container" id="pagination-container">
                <div class="pagination-info">
                    <span id="pagination-text">Rāda 1-10 no 0 ierakstiem</span>
                </div>
                <div class="pagination-controls" id="pagination-controls">
                    <!-- Pagination buttons will be inserted here -->
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Product View Modal -->
<div id="productModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Produkta informācija</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Product details will be loaded here -->
        </div>
        <div class="modal-footer">
            <button class="btn cancel-btn" onclick="closeModal()">Aizvērt</button>
        </div>
    </div>
</div>

<!-- JavaScript for enhanced functionality -->
<script>

    document.addEventListener('DOMContentLoaded', function() {
    initializeProductsPagination();
    setupImageClickHandlers();
});

// Setup image click handlers
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

// Search input with debounce
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadProducts(1); // Reset to first page when searching
    }, 500);
});



// ===== REUSABLE CONFIRMATION MODAL FUNCTIONS =====
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

// ===== PRODUCT DELETION FUNCTION =====
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
                    loadProducts(currentPage);
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

function viewProduct(productId) {
    // This would fetch product details via AJAX
    // For now, redirect to edit page
    window.location.href = `produkcija_form.php?edit=${productId}`;
}

function closeModal() {
    const modal = document.getElementById('productModal');
    modal.style.display = 'none';
}

function showNotification(type, title, message) {
    const container = document.querySelector('.notification-container');
    
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
    const timeoutId = setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
            checkNotificationContainer();
        }
    }, 5000);
}

// Helper function to check if container should be hidden
function checkNotificationContainer() {
    const container = document.querySelector('.notification-container');
    if (container.children.length === 0) {
        container.style.display = 'none';
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + N for new product
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        window.location.href = 'produkcija_form.php';
    }
    
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
        document.getElementById('searchInput').focus();
    }
});

// Export table data (bonus feature)
function exportTableData() {
    const table = document.getElementById('productTable');
    const rows = Array.from(table.querySelectorAll('tr'));
    
    let csvContent = '';
    
    rows.forEach(row => {
        if (row.querySelector('.no-records')) return;
        
        const cells = Array.from(row.querySelectorAll('th, td'));
        const csvRow = cells.map(cell => {
            // Clean up cell content
            let content = cell.textContent.trim();
            // Remove action buttons content
            if (cell.classList.contains('action-buttons')) {
                content = '';
            }
            // Escape quotes
            content = content.replace(/"/g, '""');
            return `"${content}"`;
        }).join(',');
        
        csvContent += csvRow + '\n';
    });
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `produkti_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    window.URL.revokeObjectURL(url);
}
</script>