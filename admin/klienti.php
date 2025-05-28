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
        <h1>Klientu pārvaldība</h1>
        
        <!-- Clients Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošie klienti</h2>
                <button onclick="openAddModal()" class="btn "><i class="fas fa-plus"></i></button>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vārds</th>
                            <th>Uzvārds</th>
                            <th>Lietotājvārds</th>
                            <th>E-pasts</th>
                            <th>Reģistrēšanās datums</th>
                            <th>Pēdējās izmaiņas</th>
                            <th>Rediģēja</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody id="clients-table-body">     
                        <!-- Table content will be loaded via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modal for Add/Edit Clients -->
        <div id="clients-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-title">Pievienot jaunu klientu</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="clients-form-element">
                        <input type="hidden" id="client-id" name="id">
                        
                        <div class="form-group">
                            <label for="vards">Vārds:</label>
                            <input type="text" id="vards" name="vards" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="uzvards">Uzvārds:</label>
                            <input type="text" id="uzvards" name="uzvards" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="lietotajvards">Lietotājvārds:</label>
                            <input type="text" id="lietotajvards" name="lietotajvards" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="epasts">E-pasts:</label>
                            <input type="email" id="epasts" name="epasts" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="parole">Parole:</label>
                            <input type="password" id="parole" name="parole">
                            <small id="password-hint" style="display: none; color: #666;">Atstājiet tukšu, lai nemainītu esošo paroli</small>
                        </div>
                        
                        <div class="modal-buttons">
                            <button type="submit" class="btn btn-primary" id="submit-btn">Pievienot klientu</button>
                            <button type="button" onclick="closeModal()" class="btn btn-secondary">Atcelt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </section>
</main>

<!-- JavaScript for managing clients -->
<script>
    let editMode = false;

    // Function definitions
    function openAddModal() {
        editMode = false;
        document.getElementById('modal-title').textContent = 'Pievienot jaunu klientu';
        document.getElementById('submit-btn').textContent = 'Pievienot klientu';
        document.getElementById('clients-form-element').reset();
        document.getElementById('client-id').value = '';
        document.getElementById('parole').required = true;
        document.getElementById('password-hint').style.display = 'none';
        document.getElementById('clients-modal').style.display = 'block';
    }

    function openEditModal(id) {
        editMode = true;
        document.getElementById('modal-title').textContent = 'Rediģēt klientu';
        document.getElementById('submit-btn').textContent = 'Atjaunināt klientu';
        document.getElementById('parole').required = false;
        document.getElementById('password-hint').style.display = 'block';
        
        // Fetch client data
        fetch(`db/users.php?fetch_client_single=1&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                document.getElementById('client-id').value = data.id_lietotajs;
                document.getElementById('vards').value = data.vards;
                document.getElementById('uzvards').value = data.uzvards;
                document.getElementById('lietotajvards').value = data.lietotajvards;
                document.getElementById('epasts').value = data.epasts;
                document.getElementById('parole').value = '';
                document.getElementById('clients-modal').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching client:', error);
            showNotification('error', 'Kļūda!', 'Neizdevās ielādēt klienta datus.');
        });
    }

    function closeModal() {
        document.getElementById('clients-modal').style.display = 'none';
    }

    function deleteClient(id) {
    showConfirmModal(
        'Vai tiešām vēlaties dzēst šo klientu? Šī darbība ir neatgriezeniska.',
        function() {
            // Confirmed - proceed with deletion
            const formData = new FormData();
            formData.append('delete_client', '1');
            formData.append('id', id);
            
            fetch('db/materiali_delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('success', 'Veiksmīgi!', data.message);
                    loadAudums();
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting klientu:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās dzēst klientu.');
            });
        }
    );
}


    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleString('lv-LV', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }

    function loadClients() {
        fetch('db/users.php?fetch_clients=1')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('clients-table-body');
            tbody.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(client => {
                    const row = `
                        <tr>
                            <td>${client.id_lietotajs}</td>
                            <td>${client.vards}</td>
                            <td>${client.uzvards}</td>
                            <td>${client.lietotajvards}</td>
                            <td>${client.epasts}</td>
                            <td>${formatDate(client.datums)}</td>
                            <td>${formatDate(client.red_dat) || ''}</td>
                            <td>${client.red_liet_name || ''}</td>
                            <td class='action-buttons'>
                                <button onclick="openEditModal(${client.id_lietotajs})" class='btn edit-btn'><i class='fas fa-edit'></i></button>
                                <button onclick="deleteClient(${client.id_lietotajs})" class='btn delete-btn'><i class='fas fa-trash-alt'></i></button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = "<tr><td colspan='9' class='no-records'>Nav atrasts neviens klients</td></tr>";
            }
        })
        .catch(error => {
            console.error('Error loading clients:', error);
            showNotification('error', 'Kļūda!', 'Neizdevās ielādēt klientus.');
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
        loadClients();
        
        // Handle form submission
        document.getElementById('clients-form-element').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const endpoint = editMode ? 'db/users_edit.php' : 'db/users_add.php';
            const action = editMode ? 'edit_client' : 'add_client';
            
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
                    loadClients();
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error saving client:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās saglabāt klientu.');
            });
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('clients-modal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>