<?php
// admin/produkcija.php
// Main product management page - displays product list only

require 'header.php';

// Handle product deletion via AJAX (we'll modify this approach)
// Remove the GET delete handling since we'll use AJAX

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

        
        <!-- Product Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošie produkti</h2>
                <div class="table-actions">
                    <input type="text" id="searchInput" placeholder="Meklēt produktus..." class="search-input">
                    <select id="filterSelect" class="filter-select">
                        <option value="">Visi produkti</option>
                        <option value="recent">Nesen pievienoti</option>
                        <option value="updated">Nesen atjaunināti</option>
                    </select>
                </div>
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
                            <th>Dekorējums (1)</th>
                            <th>Dekorējums (2)</th>
                            <th>Cena</th>
                            <th>Izveidots</th>
                            <th>Atjaunināts</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody>     
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
                                echo "<td>" . htmlspecialchars($row['dekorejums2_name']) . "</td>";
                                echo "<td class='price'>€" . number_format($row['cena'], 2) . "</td>";
                                
                                // Created info - UPDATED VERSION WITH USER NAMES
                                $created_info = '';
                                if (!empty($row['created_first_name']) && !empty($row['created_last_name'])) {
                                    $created_info .= htmlspecialchars($row['created_first_name'] . ' ' . $row['created_last_name']);
                                } elseif (!empty($row['izveidots_liet'])) {
                                    $created_info .= 'Lietotājs ID: ' . $row['izveidots_liet'];
                                }
                                
                                if (!empty($row['created_at'])) {
                                    $created_info .= '<small>' . date('d.m.Y H:i', strtotime($row['created_at'])) . '</small>';
                                }
                                echo "<td class='metadata'>" . ($created_info) . "</td>";
                                
                                // Updated info - UPDATED VERSION WITH USER NAMES
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
                                
                                // Action buttons - UPDATED TO USE NEW FUNCTION
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
                            echo "<tr><td colspan='12' class='no-records'>";
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

<!-- Custom CSS for improved styling -->
<style>
    .product-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .product-thumbnail:hover {
        transform: scale(1.1);
    }
    
    .no-image {
        width: 50px;
        height: 50px;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        color: #6c757d;
    }
    
    .metadata {
        font-size: 0.9em;
        line-height: 1.4;
    }
    
    .metadata small {
        color: #6c757d;
    }
</style>

<!-- JavaScript for enhanced functionality -->
<script>
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
                    // Remove the row from table
                    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                    if (row) {
                        row.remove();
                    }
                    // Check if table is empty
                    checkEmptyTable();
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

function checkEmptyTable() {
    const tbody = document.querySelector('#productTable tbody');
    const visibleRows = tbody.querySelectorAll('tr[data-product-id]');
    
    if (visibleRows.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan='12' class='no-records'>
                <div class='empty-state'>
                    <i class='fas fa-box-open'></i>
                    <h3>Nav atrasts neviens produkts</h3>
                    <p>Sāciet, pievienojot savu pirmo produktu.</p>
                    <a href='produkcija_form.php' class='btn add-btn'>
                        <i class='fas fa-plus'></i> Pievienot produktu
                    </a>
                </div>
            </td></tr>
        `;
    }
}

document.addEventListener('DOMContentLoaded', function() {

    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('filterSelect');
    const productTable = document.getElementById('productTable');
    const tbody = productTable.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Search function
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const filterValue = filterSelect.value;
        
        rows.forEach(row => {
            if (row.querySelector('.no-records')) return;
            
            const productName = row.querySelector('.product-name')?.textContent.toLowerCase() || '';
            const productId = row.querySelector('td:first-child')?.textContent || '';
            const createdDate = row.getAttribute('data-created') || '';
            const updatedDate = row.getAttribute('data-updated') || '';
            
            // Search filter
            const matchesSearch = productName.includes(searchTerm) || 
                                productId.includes(searchTerm);
            
            // Date filter
            let matchesFilter = true;
            if (filterValue === 'recent') {
                const created = new Date(createdDate);
                const weekAgo = new Date();
                weekAgo.setDate(weekAgo.getDate() - 7);
                matchesFilter = created > weekAgo;
            } else if (filterValue === 'updated') {
                const updated = new Date(updatedDate);
                const weekAgo = new Date();
                weekAgo.setDate(weekAgo.getDate() - 7);
                matchesFilter = updated > weekAgo;
            }
            
            // Show/hide row
            if (matchesSearch && matchesFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Check if any rows are visible
        const visibleRows = rows.filter(row => 
            row.style.display !== 'none' && !row.querySelector('.no-records')
        );
        
        // Show/hide "no results" message
        let noResultsRow = tbody.querySelector('.no-results');
        if (visibleRows.length === 0 && rows.length > 0 && !rows[0].querySelector('.no-records')) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results';
                noResultsRow.innerHTML = `
                    <td colspan="12" class="no-records">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h3>Nav atrasti rezultāti</h3>
                            <p>Mēģiniet mainīt meklēšanas kritērijus.</p>
                        </div>
                    </td>
                `;
                tbody.appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }
    
    // Event listeners for search and filter
    searchInput.addEventListener('input', filterTable);
    filterSelect.addEventListener('change', filterTable);
    
    // Image click to enlarge
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
});

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

// Auto-refresh every 5 minutes to show updated data
setInterval(() => {
    // Only refresh if no modals are open and no active form interactions
    const hasOpenModals = document.querySelector('.modal[style*="display: flex"], .modal[style*="display: block"]');
    const hasActiveInputs = document.querySelector('input:focus, select:focus, textarea:focus');
    
    if (!hasOpenModals && !hasActiveInputs) {
        // You could implement a subtle refresh here
        // For now, just update the timestamp or show a subtle indicator
        console.log('Auto-refresh check at:', new Date().toLocaleTimeString());
    }
}, 300000); // 5 minutes

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