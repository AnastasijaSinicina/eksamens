<?php
    // Include admin header
    require 'header.php';
    
    // Database connection
    require 'db/atsauksmes_admin.php';

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
                        <span id="confirmButtonText">Jā</span>
                    </button>
                    <button class="confirm-btn confirm-btn-cancel" id="confirmNo">
                        <i class="fas fa-times"></i> Atcelt
                    </button>
                </div>
            </div>
        </div>
    </div>
    <section class="admin-content">
        <h1>Atsauksmju pārvaldība</h1>
        
        <!-- Filters -->
        <div class="filters-container">
            <form class="filters-form" method="GET">
                <div class="filter-group">
                    <label for="status">Statuss:</label>
                    <select id="status" name="status" onchange="this.form.submit()">
                        <option value="">Visi</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Gaida apstiprinājumu</option>
                        <option value="approved" <?= $status_filter == 'approved' ? 'selected' : '' ?>>Apstiprināts</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="rating">Vērtējums:</label>
                    <select id="rating" name="rating" onchange="this.form.submit()">
                        <option value="">Visi vērtējumi</option>
                        <option value="5" <?= $rating_filter == '5' ? 'selected' : '' ?>>5 zvaigznes</option>
                        <option value="4" <?= $rating_filter == '4' ? 'selected' : '' ?>>4 zvaigznes</option>
                        <option value="3" <?= $rating_filter == '3' ? 'selected' : '' ?>>3 zvaigznes</option>
                        <option value="2" <?= $rating_filter == '2' ? 'selected' : '' ?>>2 zvaigznes</option>
                        <option value="1" <?= $rating_filter == '1' ? 'selected' : '' ?>>1 zvaigzne</option>
                    </select>
                </div>
                
                <a href="atsauksmes.php" class="btn clear-filter-btn">Notīrīt filtrus</a>
            </form>
        </div>
        
        <!-- Feedback Table -->
        <div class="product-table-container">
            <div class="table-header">
                <h2>Atsauksmes</h2>
            </div>
            
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Klients</th>
                            <th>Vērtējums</th>
                            <th>Atsauksme</th>
                            <th>Datums</th>
                            <th>Statuss</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($feedback_result->num_rows > 0): ?>
                            <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $feedback['id_atsauksme'] ?></td>
                                    <td>
                                        <div class="client-info">
                                            <div class="client-details">
                                                <strong><?= htmlspecialchars($feedback['vards_uzvards']) ?></strong>
                                                <?php if ($feedback['lietotajvards']): ?>
                                                    <small>@<?= htmlspecialchars($feedback['lietotajvards']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="rating-display">
                                            <?php 
                                            $rating = (int)$feedback['zvaigznes'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                            <span class="rating-number">(<?= $rating ?>/5)</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="feedback-text">
                                            <?= htmlspecialchars(substr($feedback['atsauksme'], 0, 100)) ?>
                                            <?php if (strlen($feedback['atsauksme']) > 100): ?>
                                                <span class="read-more" onclick="showFullFeedback('<?= $feedback['id_atsauksme'] ?>')">... lasīt vairāk</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Hidden full text for modal -->
                                        <div id="full-feedback-<?= $feedback['id_atsauksme'] ?>" style="display: none;">
                                            <?= htmlspecialchars($feedback['atsauksme']) ?>
                                        </div>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($feedback['datums'])) ?></td>
                                    <td>
                                        <?php if ($feedback['apstiprinats']): ?>
                                            <span class="status approved">Apstiprināts</span>
                                        <?php else: ?>
                                            <span class="status pending">Gaida apstiprinājumu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <?php if (!$feedback['apstiprinats']): ?>
                                            <button onclick="approveFeedback(<?= $feedback['id_atsauksme'] ?>)" 
                                               class="btn approve-btn">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php else: ?>
                                            <button onclick="rejectFeedback(<?= $feedback['id_atsauksme'] ?>)" 
                                               class="btn reject-btn">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-records">Nav atrasta neviena atsauksme</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </section>
</main>

<!-- Modal for full feedback text -->
<div id="feedback-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Pilna atsauksme</h3>
            <span class="modal-close" onclick="closeFeedbackModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modal-feedback-text"></div>
        </div>
    </div>
</div>

<!-- JavaScript for notifications and modal -->
<script>
    // Function to show confirmation modal with custom button text
    function showConfirmModal(message, onConfirm, confirmButtonText = 'Dzēst', confirmButtonIcon = 'fas fa-trash-alt') {
        document.getElementById('confirmMessage').textContent = message;
        
        // Update button text and icon
        const confirmButton = document.getElementById('confirmYes');
        const buttonTextSpan = document.getElementById('confirmButtonText');
        const buttonIcon = confirmButton.querySelector('i');
        
        buttonIcon.className = confirmButtonIcon;
        buttonTextSpan.textContent = confirmButtonText;
        
        document.getElementById('confirmModal').style.display = 'block';
        
        // Handle confirm button click
        confirmButton.onclick = function() {
            document.getElementById('confirmModal').style.display = 'none';
            onConfirm();
        };
        
        // Handle cancel button click
        document.getElementById('confirmNo').onclick = function() {
            document.getElementById('confirmModal').style.display = 'none';
        };
    }
    
    // Function to approve feedback with confirmation
    function approveFeedback(id) {
        showConfirmModal(
            'Vai apstiprināt šo atsauksmi?',
            function() {
                // Redirect to approve action
                window.location.href = 'atsauksmes.php?approve=' + id;
            },
            'Apstiprināt',
            'fas fa-times'
        );
    }
    
    // Function to reject feedback with confirmation
    function rejectFeedback(id) {
        showConfirmModal(
            'Vai noraidīt šo atsauksmi?',
            function() {
                // Redirect to reject action
                window.location.href = 'atsauksmes.php?reject=' + id;
            },
            'Noraidīt',
            'fas fa-check'
        );
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
        }, 4000);
    }
    
    function showFullFeedback(feedbackId) {
        const fullText = document.getElementById('full-feedback-' + feedbackId).innerHTML;
        document.getElementById('modal-feedback-text').innerHTML = fullText;
        document.getElementById('feedback-modal').style.display = 'block';
    }
    
    function closeFeedbackModal() {
        document.getElementById('feedback-modal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const feedbackModal = document.getElementById('feedback-modal');
        const confirmModal = document.getElementById('confirmModal');
        
        if (event.target == feedbackModal) {
            feedbackModal.style.display = 'none';
        }
        
        if (event.target == confirmModal) {
            confirmModal.style.display = 'none';
        }
    }
</script>

<?php
$savienojums->close();
?>