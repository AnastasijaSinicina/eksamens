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
        <h1>Malu Figūru pārvaldība</h1>
        
        <!-- Figures Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošās malu figūras</h2>
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
                    <tbody id="figuras-table-body">     
                        <!-- Table content will be loaded via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modal for Add/Edit Figures -->
        <div id="figuras-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-title">Pievienot jaunu malu figūru</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="figuras-form-element" enctype="multipart/form-data">
                        <input type="hidden" id="figura-id" name="id">
                        
                        <div class="form-group">
                            <label for="nosaukums">Nosaukums:</label>
                            <input type="text" id="nosaukums" name="nosaukums" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="attels">Attēls:</label>
                            <div id="current-image" style="display: none;">
                                <p>Pašreizējais attēls:</p>
                                <img id="current-image-preview" alt="Malu figūras attēls" width="100">
                            </div>
                            <input type="file" id="attels" name="attels" accept="image/*">
                            <small>Atbalstītie formāti: JPG, JPEG, PNG</small>
                        </div>
                        
                        <div class="modal-buttons">
                            <button type="submit" class="btn btn-primary" id="submit-btn">Pievienot malu figūru</button>
                            <button type="button" onclick="closeModal()" class="btn btn-secondary">Atcelt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </section>
</main>

<!-- JavaScript for managing figuras -->
<script>
    let editMode = false;

    // Function definitions
    function openAddModal() {
        editMode = false;
        document.getElementById('modal-title').textContent = 'Pievienot jaunu malu figūru';
        document.getElementById('submit-btn').textContent = 'Pievienot malu figūru';
        document.getElementById('figuras-form-element').reset();
        document.getElementById('figura-id').value = '';
        document.getElementById('current-image').style.display = 'none';
        document.getElementById('attels').required = true;
        document.getElementById('figuras-modal').style.display = 'block';
    }

    function openEditModal(id) {
        editMode = true;
        document.getElementById('modal-title').textContent = 'Rediģēt malu figūru';
        document.getElementById('submit-btn').textContent = 'Atjaunināt malu figūru';
        
        // Fetch figura data
        fetch(`db/materiali.php?fetch_figuras_single=1&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                document.getElementById('figura-id').value = data.id_malu_figura;
                document.getElementById('nosaukums').value = data.nosaukums;
                
                if (data.attels) {
                    document.getElementById('current-image').style.display = 'block';
                    // Image data is now base64 encoded from PHP
                    document.getElementById('current-image-preview').src = `data:image/jpeg;base64,${data.attels}`;
                }
                
                document.getElementById('attels').required = false;
                document.getElementById('figuras-modal').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching figura:', error);
            showNotification('error', 'Kļūda!', 'Neizdevās ielādēt malu figūras datus.');
        });
    }

    function closeModal() {
        document.getElementById('figuras-modal').style.display = 'none';
    }

    function deleteFigura(id) {
    showConfirmModal(
        'Vai tiešām vēlaties dzēst šo mālu figūru? Šī darbība ir neatgriezeniska.',
        function() {
            // Confirmed - proceed with deletion
            const formData = new FormData();
            formData.append('delete_figura', '1');
            formData.append('id', id);
            
            fetch('db/materiali_delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('success', 'Veiksmīgi!', data.message);
                    loadFiguras();
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting figūra:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās dzēst mālu figūru.');
            });
        }
    );
}


    function loadFiguras() {
        fetch('db/materiali.php?fetch_figuras=1')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('figuras-table-body');
            tbody.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(figura => {
                    const datums = figura.datums || new Date().toISOString().slice(0, 19).replace('T', ' ');
                    
                    // Handle image display
                    let imageHtml = 'Nav attēla';
                    if (figura.attels) {
                        // Image data is now base64 encoded from PHP
                        imageHtml = `<img src="data:image/jpeg;base64,${figura.attels}" alt="${figura.nosaukums}" width="50">`;
                    }
                    
                    // Format last modified info
                    let lastModified = '';
                    if (figura.red_dat && figura.red_liet_name) {
                        const modifiedDate = new Date(figura.red_dat).toLocaleString('lv-LV');
                        lastModified = `${figura.red_liet_name}<br><small>${modifiedDate}</small>`;
                    } else if (figura.datums && figura.izveidots_liet_name) {
                        const createdDate = new Date(figura.datums).toLocaleString('lv-LV');
                        lastModified = `${figura.izveidots_liet_name}<br><small>${createdDate}</small>`;
                    }
                    
                    const row = `
                        <tr>
                            <td>${figura.id_malu_figura}</td>
                            <td>${figura.nosaukums}</td>
                            <td>${imageHtml}</td>
                            <td>${datums}</td>
                            <td>${lastModified}</td>
                            <td class='action-buttons'>
                                <button onclick="openEditModal(${figura.id_malu_figura})" class='btn edit-btn'><i class='fas fa-edit'></i></button>
                                <button onclick="deleteFigura(${figura.id_malu_figura})" class='btn delete-btn'><i class='fas fa-trash-alt'></i></button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = "<tr><td colspan='6' class='no-records'>Nav atrasta neviena malu figūra</td></tr>";
            }
        })
        .catch(error => {
            console.error('Error loading figuras:', error);
            showNotification('error', 'Kļūda!', 'Neizdevās ielādēt malu figūras.');
        });
    }

    function showNotification(type, title, message) {
        const container = document.querySelector('.notification-container');
        const notification = document.querySelector('.notification');
        const icon = notification.querySelector('i');
        const titleElement = notification.querySelector('h3');
        const messageElement = notification.querySelector('p');
        
        // Set notification content
        icon.className = type === 'success' ? 'fas fa-check-circle success' : 'fas fa-exclamation-circle error';
        titleElement.textContent = title;
        messageElement.textContent = message;
        
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
        loadFiguras();
        
        // Handle form submission - ONLY ONCE!
        document.getElementById('figuras-form-element').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const endpoint = editMode ? 'db/materiali_edit.php' : 'db/materiali_add.php';
            const action = editMode ? 'edit_figura' : 'add_figura';
            
            formData.append(action, '1');
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('success', 'Veiksmīgi!', data.message);
                    closeModal();
                    loadFiguras();
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error saving figura:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās saglabāt malu figūru.');
            });
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('figuras-modal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
