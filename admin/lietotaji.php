<?php
    session_start();

       if (!isset($_SESSION['lietotajvardsSIN']) || $_SESSION['loma'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }

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
        <h1>Administrātoru un moderātoru pārvaldība</h1>
        
        <!-- Users Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošie lietotāji</h2>
                <button onclick="openAddModal()" class="btn"><i class="fas fa-plus"></i></button>
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
                            <th>Loma</th>
                            <th>Izveidoja</th>
                            <th>Izveidošanas datums</th>
                            <th>Pēdējās izmaiņas</th>
                            <th>Rediģēja</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">     
                        <!-- Table content will be loaded via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modal for Add/Edit Users -->
        <div id="users-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-title">Pievienot jaunu lietotāju</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="users-form-element">
                        <input type="hidden" id="user-id" name="id">
                        
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
                            <input type="email" id="epasts" name="epasts" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="parole">Parole:</label>
                            <input type="password" id="parole" name="parole">
                            <small id="password-hint" style="display: none; color: #666;">Atstājiet tukšu, lai nemainītu esošo paroli</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="loma">Loma:</label>
                            <select id="loma" name="loma" required>
                                <option value="">Izvēlieties lomu</option>
                                <option value="admin">Administrators</option>
                                <option value="moder">Moderators</option>
                            </select>
                        </div>
                        
                        <div class="modal-buttons">
                            <button type="submit" class="btn btn-primary" id="submit-btn">Pievienot lietotāju</button>
                            <button type="button" onclick="closeModal()" class="btn btn-secondary">Atcelt</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        
    </section>
</main>

<!-- JavaScript for managing users -->
<script>
    let editMode = false;

    // Function definitions
    function openAddModal() {
        editMode = false;
        document.getElementById('modal-title').textContent = 'Pievienot jaunu lietotāju';
        document.getElementById('submit-btn').textContent = 'Pievienot lietotāju';
        document.getElementById('users-form-element').reset();
        document.getElementById('user-id').value = '';
        document.getElementById('parole').required = true;
        document.getElementById('password-hint').style.display = 'none';
        document.getElementById('users-modal').style.display = 'block';
    }

    function openEditModal(id) {
        editMode = true;
        document.getElementById('modal-title').textContent = 'Rediģēt lietotāju';
        document.getElementById('submit-btn').textContent = 'Atjaunināt lietotāju';
        document.getElementById('parole').required = false;
        document.getElementById('password-hint').style.display = 'block';
        
        // Fetch user data
        fetch(`db/users.php?fetch_user_single=1&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                document.getElementById('user-id').value = data.id_lietotajs;
                document.getElementById('vards').value = data.vards;
                document.getElementById('uzvards').value = data.uzvards;
                document.getElementById('lietotajvards').value = data.lietotajvards;
                document.getElementById('epasts').value = data.epasts;
                document.getElementById('loma').value = data.loma;
                document.getElementById('parole').value = '';
                document.getElementById('users-modal').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching user:', error);
            showNotification('error', 'Kļūda!', 'Neizdevās ielādēt lietotāja datus.');
        });
    }

    function closeModal() {
        document.getElementById('users-modal').style.display = 'none';
    }

    function deleteUser(id) {
    showConfirmModal(
        'Vai tiešām vēlaties dzēst šo lietotāju? Šī darbība ir neatgriezeniska.',
        function() {
            // Confirmed - proceed with deletion
            const formData = new FormData();
            formData.append('delete_user', '1');
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
                console.error('Error deleting lietotāju:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās dzēst lietotāju.');
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

    function loadUsers() {
        fetch('db/users.php?fetch_users=1')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('users-table-body');
            tbody.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(user => {
                    const row = `
                        <tr>
                            <td>${user.id_lietotajs}</td>
                            <td>${user.vards}</td>
                            <td>${user.uzvards}</td>
                            <td>${user.lietotajvards}</td>
                            <td>${user.epasts}</td>
                            <td>${user.loma.charAt(0).toUpperCase() + user.loma.slice(1)}</td>
                            <td>${user.izveidots_liet_name || ''}</td>
                            <td>${formatDate(user.datums)}</td>
                            <td>${formatDate(user.red_dat) || ''}</td>
                            <td>${user.red_liet_name || ''}</td>
                            <td class='action-buttons'>
                                <button onclick="openEditModal(${user.id_lietotajs})" class='btn edit-btn'><i class='fas fa-edit'></i></button>
                                <button onclick="deleteUser(${user.id_lietotajs})" class='btn delete-btn'><i class='fas fa-trash-alt'></i></button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = "<tr><td colspan='11' class='no-records'>Nav atrasts neviens administrators vai moderators</td></tr>";
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            showNotification('error', 'Kļūda!', 'Neizdevās ielādēt lietotājus.');
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
        loadUsers();
        
        // Handle form submission
        document.getElementById('users-form-element').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const endpoint = editMode ? 'db/users_edit.php' : 'db/users_add.php';
            const action = editMode ? 'edit_user' : 'add_user';
            
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
                    loadUsers();
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error saving user:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās saglabāt lietotāju.');
            });
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('users-modal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>