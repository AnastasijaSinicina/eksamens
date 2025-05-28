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
        <h1>Audumu pārvaldība</h1>
        
        <!-- Fabric Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Esošie audumi</h2>
                <button onclick="openAddModal()" class="btn "><i class="fas fa-plus"></i></button>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nosaukums</th>
                            <th>Pievienošanas datums</th>
                            <th>Pēdējās izmaiņas</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody id="audums-table-body">     
                        <!-- Table content will be loaded via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modal for Add/Edit Audums -->
        <div id="audums-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-title">Pievienot jaunu audumu</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="audums-form-element">
                        <input type="hidden" id="audums-id" name="id">
                        
                        <div class="form-group">
                            <label for="nosaukums">Nosaukums:</label>
                            <input type="text" id="nosaukums" name="nosaukums" required>
                        </div>
                        
                        <div class="modal-buttons">
                            <button type="submit" class="btn btn-primary" id="submit-btn">Pievienot audumu</button>
                            <button type="button" onclick="closeModal()" class="btn btn-secondary">Atcelt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </section>
</main>

<!-- JavaScript for managing audums -->
<script>
    let editMode = false;


    // Function definitions
    function openAddModal() {
        editMode = false;
        document.getElementById('modal-title').textContent = 'Pievienot jaunu audumu';
        document.getElementById('submit-btn').textContent = 'Pievienot audumu';
        document.getElementById('audums-form-element').reset();
        document.getElementById('audums-id').value = '';
        document.getElementById('audums-modal').style.display = 'block';
    }

    function openEditModal(id) {
        editMode = true;
        document.getElementById('modal-title').textContent = 'Rediģēt audumu';
        document.getElementById('submit-btn').textContent = 'Atjaunināt audumu';
        
        // Fetch audums data
        fetch(`db/materiali.php?fetch_audums_single=1&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                document.getElementById('audums-id').value = data.id_audums;
                document.getElementById('nosaukums').value = data.nosaukums;
                document.getElementById('audums-modal').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching audums:', error);
            showNotification('error', 'Kļūda!', 'Neizdevās ielādēt auduma datus.');
        });
    }

    function closeModal() {
        document.getElementById('audums-modal').style.display = 'none';
    }

    function deleteAudums(id) {
    showConfirmModal(
        'Vai tiešām vēlaties dzēst šo audumu? Šī darbība ir neatgriezeniska.',
        function() {
            // Confirmed - proceed with deletion
            const formData = new FormData();
            formData.append('delete_audums', '1');
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
                console.error('Error deleting audums:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās dzēst audumu.');
            });
        }
    );
}

    function loadAudums() {
    fetch('db/materiali.php?fetch_audums=1')
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        const tbody = document.getElementById('audums-table-body');
        tbody.innerHTML = '';
        
        if (data.length > 0) {
            data.forEach(audums => {
                const datums = audums.datums || new Date().toISOString().slice(0, 19).replace('T', ' ');
                
                // Format last modified info
                let lastModified = '';
                if (audums.red_dat && audums.red_liet_name) {
                    const modifiedDate = new Date(audums.red_dat).toLocaleString('lv-LV');
                    lastModified = `${audums.red_liet_name}<br><small>${modifiedDate}</small>`;
                } else if (audums.datums && audums.izveidots_liet_name) {
                    const createdDate = new Date(audums.datums).toLocaleString('lv-LV');
                    lastModified = `${audums.izveidots_liet_name}<br><small>${createdDate}</small>`;
                }
                
                const row = `
                    <tr>
                        <td>${audums.id_audums}</td>
                        <td>${audums.nosaukums}</td>
                        <td>${datums}</td>
                        <td>${lastModified}</td>
                        <td class='action-buttons'>
                            <button onclick="openEditModal(${audums.id_audums})" class='btn edit-btn'><i class='fas fa-edit'></i></button>
                            <button onclick="deleteAudums(${audums.id_audums})" class='btn delete-btn'><i class='fas fa-trash-alt'></i></button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='5' class='no-records'>Nav atrasts neviens audums</td></tr>";
        }
    })
    .catch(error => {
        console.error('Error loading audums:', error);
        showNotification('error', 'Kļūda!', 'Neizdevās ielādēt audumus.');
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
        loadAudums();
        
        // Handle form submission - ONLY ONCE!
        document.getElementById('audums-form-element').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const endpoint = editMode ? 'db/materiali_edit.php' : 'db/materiali_add.php';
            const action = editMode ? 'edit_audums' : 'add_audums';
            
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
                    loadAudums();
                } else {
                    showNotification('error', 'Kļūda!', data.message);
                }
            })
            .catch(error => {
                console.error('Error saving audums:', error);
                showNotification('error', 'Kļūda!', 'Neizdevās saglabāt audumu.');
            });
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('audums-modal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
