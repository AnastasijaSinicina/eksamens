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
        <h1>Formu pārvaldība</h1>
        
        <!-- Forms Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošās formas</h2>
                <button onclick="openAddModal()" class="btn "><i class="fas fa-plus"></i></button>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Forma</th>
                            <th>Datums</th>
                            <th>Pēdējās izmaiņas</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody id="formas-table-body">     
                        <!-- Table content will be loaded via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modal for Add/Edit Forms -->
        <div id="formas-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-title">Pievienot jaunu formu</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="formas-form-element">
                        <input type="hidden" id="forma-id" name="id">
                        
                        <div class="form-group">
                            <label for="forma">Forma:</label>
                            <input type="text" id="forma" name="forma" required>
                        </div>
                        
                        <div class="modal-buttons">
                            <button type="submit" class="btn btn-primary" id="submit-btn">Pievienot formu</button>
                            <button type="button" onclick="closeModal()" class="btn btn-secondary">Atcelt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </section>
</main>

<!-- JavaScript for managing formas -->
<script>
    let editMode = false;

    // Function definitions
    function openAddModal() {
        editMode = false;
        document.getElementById('modal-title').textContent = 'Pievienot jaunu formu';
        document.getElementById('submit-btn').textContent = 'Pievienot formu';
        document.getElementById('formas-form-element').reset();
        document.getElementById('forma-id').value = '';
        document.getElementById('formas-modal').style.display = 'block';
    }

    function openEditModal(id) {
        editMode = true;
        document.getElementById('modal-title').textContent = 'Rediģēt formu';
        document.getElementById('submit-btn').textContent = 'Atjaunināt formu';
        
        // Fetch forma data
        fetch(`db/materiali.php?fetch_formas_single=1&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                document.getElementById('forma-id').value = data.id_forma;
                document.getElementById('forma').value = data.forma;
                document.getElementById('formas-modal').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching forma:', error);
            showNotification('error', 'Kļūda!', 'Neizdevās ielādēt formas datus.');
        });
    }

    function closeModal() {
        document.getElementById('formas-modal').style.display = 'none';
    }

    function deleteForma(id) {
    showConfirmModal(
        'Vai tiešām vēlaties dzēst šo formu? Šī darbība ir neatgriezeniska.',
        function() {
            // Confirmed - proceed with deletion
            const formData = new FormData();
            formData.append('delete_forma', '1');
            formData.append('id', id);
            
            fetch('db/materiali_delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('success', 'Veiksmīgi!', data.message);
                    loadFormas();
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting forma:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās dzēst formu.');
            });
        }
    );
}


    function loadFormas() {
        fetch('db/materiali.php?fetch_formas=1')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('formas-table-body');
            tbody.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(forma => {
                    const datums = forma.datums || new Date().toISOString().slice(0, 19).replace('T', ' ');
                    
                    // Format last modified info
                    let lastModified = '';
                    if (forma.red_dat && forma.red_liet_name) {
                        const modifiedDate = new Date(forma.red_dat).toLocaleString('lv-LV');
                        lastModified = `${forma.red_liet_name}<br><small>${modifiedDate}</small>`;
                    } else if (forma.datums && forma.izveidots_liet_name) {
                        const createdDate = new Date(forma.datums).toLocaleString('lv-LV');
                        lastModified = `${forma.izveidots_liet_name}<br><small>${createdDate}</small>`;
                    }
                    
                    const row = `
                        <tr>
                            <td>${forma.id_forma}</td>
                            <td>${forma.forma}</td>
                            <td>${datums}</td>
                            <td>${lastModified}</td>
                            <td class='action-buttons'>
                                <button onclick="openEditModal(${forma.id_forma})" class='btn edit-btn'><i class='fas fa-edit'></i></button>
                                <button onclick="deleteForma(${forma.id_forma})" class='btn delete-btn'><i class='fas fa-trash-alt'></i></button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = "<tr><td colspan='5' class='no-records'>Nav atrasta neviena forma</td></tr>";
            }
        })
        .catch(error => {
            console.error('Error loading formas:', error);
            showNotification('error', 'Kļūda!', 'Neizdevās ielādēt formas.');
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
        loadFormas();
        
        // Handle form submission - ONLY ONCE!
        document.getElementById('formas-form-element').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const endpoint = editMode ? 'db/materiali_edit.php' : 'db/materiali_add.php';
            const action = editMode ? 'edit_forma' : 'add_forma';
            
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
                    loadFormas();
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error saving forma:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās saglabāt formu.');
            });
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('formas-modal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>