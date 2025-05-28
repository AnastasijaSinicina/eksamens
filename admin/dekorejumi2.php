<?php
    // Include admin header
    require 'header.php';
?>

<main>
    <!-- Notification container -->
    <div class="notification-container">
        <div class="notification">
            <i class="fas fa-check-circle success"></i>
            <h3>Veiksmīgi!</h3>
            <p>Darbība veiksmīgi izpildīta.</p>
        </div>
    </div>
    <div id="confirmModal" class="confirm-modal">
        <div class="confirm-modal-content">
            <div class="confirm-modal-header">
                <div class="confirm-modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="confirm-modal-title">Apstiprināt darbību</h3>
            </div>
            <div class="confirm-modal-body">
                <p class="confirm-modal-message" id="confirmMessage">
                    Vai tiešām vēlaties dzēst šo vienumu?
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
        <h1>Dekorējumu pārvaldība (2)</h1>
        
        <!-- Decorations 2 Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Dekorējumi (2)</h2>
                <button onclick="openAddModal()" class="btn "><i class="fas fa-plus"></i></button>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nosaukums</th>
                            <th>Attēls</th>
                            <th>Pievienošanas datums</th>
                            <th>Pēdējās izmaiņas</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody id="dekorejums2-table-body">     
                        <!-- Table content will be loaded via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modal for Add/Edit Decorations -->
        <div id="dekorejums-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-title">Pievienot jaunu dekorējumu</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="dekorejums-form-element" enctype="multipart/form-data">
                        <input type="hidden" id="dekorejums-id" name="id">
                        <input type="hidden" id="dekorejums-type" name="type" value="2">
                        
                        <div class="form-group">
                            <label for="nosaukums">Nosaukums:</label>
                            <input type="text" id="nosaukums" name="nosaukums" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="attels">Attēls:</label>
                            <div id="current-image" style="display: none;">
                                <p>Pašreizējais attēls:</p>
                                <img id="current-image-preview" alt="Dekorējuma attēls" width="100">
                            </div>
                            <input type="file" id="attels" name="attels" accept="image/*">
                            <small>Atbalstītie formāti: JPG, JPEG, PNG, GIF</small>
                        </div>
                        
                        <div class="modal-buttons">
                            <button type="submit" class="btn btn-primary" id="submit-btn">Pievienot dekorējumu</button>
                            <button type="button" onclick="closeModal()" class="btn btn-secondary">Atcelt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </section>
</main>

<!-- JavaScript for managing dekorejums2 -->
<script>
   // Fixed JavaScript for managing dekorejums2
let editMode = false;
const currentType = 2; // Fixed to type 2

// Function definitions
function openAddModal() {
    editMode = false;
    document.getElementById('modal-title').textContent = 'Pievienot jaunu dekorējumu (2)';
    document.getElementById('submit-btn').textContent = 'Pievienot dekorējumu';
    document.getElementById('dekorejums-form-element').reset();
    document.getElementById('dekorejums-id').value = '';
    document.getElementById('current-image').style.display = 'none';
    document.getElementById('attels').required = true;
    document.getElementById('dekorejums-modal').style.display = 'block';
}

function openEditModal(id) {
    editMode = true;
    document.getElementById('modal-title').textContent = 'Rediģēt dekorējumu (2)';
    document.getElementById('submit-btn').textContent = 'Atjaunināt dekorējumu';
    
    // Fetch dekorejums data
    fetch(`db/materiali.php?fetch_dekorejums2_single=1&id=${id}`)
    .then(response => {
        console.log('Edit response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text(); // First get as text to debug
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            if (data) {
                document.getElementById('dekorejums-id').value = data.id_dekorejums2;
                document.getElementById('nosaukums').value = data.nosaukums;
                
                if (data.attels) {
                    document.getElementById('current-image').style.display = 'block';
                    document.getElementById('current-image-preview').src = `data:image/jpeg;base64,${data.attels}`;
                }
                
                document.getElementById('attels').required = false;
                document.getElementById('dekorejums-modal').style.display = 'block';
            }
        } catch (e) {
            console.error('JSON Parse Error:', e);
            showNotification('error', 'Kļūda!', 'Neizdevās parsēt servera atbildi.');
        }
    })
    .catch(error => {
        console.error('Error fetching dekorejums:', error);
        showNotification('error', 'Kļūda!', 'Neizdevās ielādēt dekorējuma datus.');
    });
}

function closeModal() {
    document.getElementById('dekorejums-modal').style.display = 'none';
}

function deleteDekorejums(id) {
    showConfirmModal(
        'Vai tiešām vēlaties dzēst šo dekorējumu? Šī darbība ir neatgriezeniska.',
        function() {
            // Confirmed - proceed with deletion
            const formData = new FormData();
            formData.append('delete_dekorejums2', '1');
            formData.append('id', id);
            
            fetch('db/materiali_delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('success', 'Veiksmīgi!', data.message);
                    loadDekorejums();
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting dekorējums (2):', error);
                showNotification('error', 'Kļūda!', 'Neizdevās dzēst dekorējumu (2).');
            });
        }
    );
}

function loadDekorejums() {
    console.log('Loading dekorejums type 2');
    
    fetch('db/materiali.php?fetch_dekorejums2=1')
    .then(response => {
        console.log('Load response status:', response.status);
        console.log('Load response headers:', response.headers);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text(); // Get as text first to debug
    })
    .then(text => {
        console.log('Raw response text:', text);
        console.log('Response length:', text.length);
        
        // Check if response is empty
        if (!text.trim()) {
            console.warn('Empty response received');
            const tbody = document.getElementById('dekorejums2-table-body');
            if (tbody) {
                tbody.innerHTML = "<tr><td colspan='6' class='no-records'>Nav atrasts neviens dekorējums</td></tr>";
            }
            return;
        }
        
        // Try to parse JSON
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Failed to parse:', text.substring(0, 200));
            showNotification('error', 'Kļūda!', 'Neizdevās parsēt servera atbildi.');
            return;
        }
        
        console.log('Parsed data:', data);
        const tbody = document.getElementById('dekorejums2-table-body');
        
        if (!tbody) {
            console.error('Table body element not found: dekorejums2-table-body');
            showNotification('error', 'Kļūda!', 'Tabulas elements nav atrasts.');
            return;
        }
        
        tbody.innerHTML = '';
        
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(dekorejums => {
                const datums = dekorejums.datums || new Date().toISOString().slice(0, 19).replace('T', ' ');
                
                // Handle image display
                let imageHtml = 'Nav attēla';
                if (dekorejums.attels) {
                    imageHtml = `<img src="data:image/jpeg;base64,${dekorejums.attels}" alt="${dekorejums.nosaukums}" width="50">`;
                }
                
                // Format last modified info - same as figuras.php
                let lastModified = '';
                if (dekorejums.red_dat && dekorejums.red_liet_name) {
                    const modifiedDate = new Date(dekorejums.red_dat).toLocaleString('lv-LV');
                    lastModified = `${dekorejums.red_liet_name}<br><small>${modifiedDate}</small>`;
                } else if (dekorejums.datums && dekorejums.izveidots_liet_name) {
                    const createdDate = new Date(dekorejums.datums).toLocaleString('lv-LV');
                    lastModified = `${dekorejums.izveidots_liet_name}<br><small>${createdDate}</small>`;
                }
                
                const row = `
                    <tr>
                        <td>${dekorejums.id_dekorejums2}</td>
                        <td>${dekorejums.nosaukums}</td>
                        <td>${imageHtml}</td>
                        <td>${datums}</td>
                        <td>${lastModified}</td>
                        <td class='action-buttons'>
                            <button onclick="openEditModal(${dekorejums.id_dekorejums2})" class='btn edit-btn'><i class='fas fa-edit'></i></button>
                            <button onclick="deleteDekorejums(${dekorejums.id_dekorejums2})" class='btn delete-btn'><i class='fas fa-trash-alt'></i></button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='6' class='no-records'>Nav atrasts neviens dekorējums</td></tr>";
        }
    })
    .catch(error => {
        console.error('Error loading dekorejums2:', error);
        showNotification('error', 'Kļūda!', 'Neizdevās ielādēt dekorējumus.');
    });
}

function showNotification(type, title, message) {
    const container = document.querySelector('.notification-container');
    const notification = document.querySelector('.notification');
    
    if (!container || !notification) {
        console.error('Notification elements not found');
        return;
    }
    
    const icon = notification.querySelector('i');
    const titleElement = notification.querySelector('h3');
    const messageElement = notification.querySelector('p');
    
    // Set notification content
    if (icon) icon.className = type === 'success' ? 'fas fa-check-circle success' : 'fas fa-exclamation-circle error';
    if (titleElement) titleElement.textContent = title;
    if (messageElement) messageElement.textContent = message;
    
    // Add class based on type
    notification.className = 'notification ' + type;
    
    // Show the notification
    container.style.display = 'block';
    
    // Hide after 5 seconds
    setTimeout(() => {
        container.style.display = 'none';
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - initializing dekorejumi2');
    loadDekorejums();
    
    // Handle form submission
    const form = document.getElementById('dekorejums-form-element');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const endpoint = editMode ? 'db/materiali_edit.php' : 'db/materiali_add.php';
            const action = editMode ? 'edit_dekorejums2' : 'add_dekorejums2';
            
            formData.append(action, '1');
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Form submit response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Form submit raw response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        showNotification('success', 'Veiksmīgi!', data.message);
                        closeModal();
                        loadDekorejums();
                    } else {
                        showNotification('error', 'Kļūda!', data.message);
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    showNotification('error', 'Kļūda!', 'Neizdevās parsēt servera atbildi.');
                }
            })
            .catch(error => {
                console.error('Error saving dekorejums:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās saglabāt dekorējumu.');
            });
        });
    } else {
        console.error('Form element not found: dekorejums-form-element');
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('dekorejums-modal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>