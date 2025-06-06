<?php
require 'header.php';
require 'admin/db/atsauksmes.php';
?>

<section id="atsauksmesNew">
    <div class="feedback-header">
        <h1>Klientu atsauksmes</h1>
        
        <?php if (!empty($approved_feedback)): ?>
            <div class="feedback-stats">
                <div class="average-rating">
                    <span class="rating-value"><?php echo number_format($average_rating, 1); ?></span>
                    <div class="stars">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= floor($average_rating)) {
                                echo '<i class="fa-solid fa-star"></i>';
                            } elseif ($i == ceil($average_rating) && $average_rating > floor($average_rating)) {
                                echo '<i class="fa-solid fa-star-half-stroke"></i>';
                            } else {
                                echo '<i class="fa-regular fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <span class="rating-count">(<?php echo $total_ratings; ?> atsauksmes)</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
        <!-- Feedback action button -->
            <div class="feedback-action">
                <?php if (isset($_SESSION['lietotajvardsSIN'])): ?>
                    <a href="atstajatsauksmi.php" class="btn">
                        <i class="fas fa-star"></i> Atstāt atsauksmi
                    </a>
                <?php else: ?>
                    <a href="#" class="btn" onclick="showLoginPrompt()">
                        <i class="fas fa-star"></i> Atstāt atsauksmi
                    </a>
                <?php endif; ?>
            </div>
    
    <?php if (empty($approved_feedback)): ?>
        <div class="no-feedback">
            <i class="fas fa-comments"></i>
            <h3>Pagaidām nav atsauksmju</h3>
            <p>Esiet pirmais, kas dalās ar savu pieredzi!</p>
        </div>
    <?php else: ?>
        <div class="feedback-grid">
            <?php foreach ($approved_feedback as $feedback): ?>
                <div class="feedback-card">
                    <div class="feedback-header-card">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php
                                // Profile image processing similar to profils.php
                                $profile_image = !empty($feedback['foto']) 
                                    ? 'data:image/jpeg;base64,'.base64_encode($feedback['foto']) 
                                    : null;
                                ?>
                                <?php if ($profile_image): ?>
                                    <img src="<?php echo $profile_image; ?>" 
                                         alt="Profila attēls" 
                                         class="profile-image"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                
                                <div class="default-avatar" 
                                     style="display: <?php echo $profile_image ? 'none' : 'flex'; ?>">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="user-details">
                                <h3><?php echo htmlspecialchars($feedback['vards_uzvards']); ?></h3>
                                <small class="feedback-date">
                                    <?php echo date('d.m.Y', strtotime($feedback['datums'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="rating">
                            <?php 
                            $rating = (int)$feedback['zvaigznes'];
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fa-solid fa-star"></i>';
                                } else {
                                    echo '<i class="fa-regular fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="feedback-content">
                        <p>"<?php echo htmlspecialchars($feedback['atsauksme']); ?>"</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Load more button if there are many feedbacks -->
        <?php if (count($approved_feedback) > 6): ?>
            <div class="load-more-container" style="display: none;">
                <button class="btn btn-secondary" onclick="loadMoreFeedback()">
                    Rādīt vairāk atsauksmju
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="feedback-footer">
        <a href="index.php#produkcija" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Atgriezties uz sākumlapu
        </a>
    </div>
</section>

<!-- Login prompt modal -->
<div id="loginModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Nepieciešama pieteikšanās</h2>
            <span class="close" onclick="closeLoginModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Lai atstātu atsauksmi, jums nepieciešams pieteikties sistēmā un pabeigt vismaz vienu pasūtījumu.</p>
            <div class="modal-buttons">
                <a href="login.php" class="btn btn-primary">Pieteikties</a>
                <a href="register.php" class="btn btn-secondary">Reģistrēties</a>
            </div>
        </div>
    </div>
</div>

<script>
function showLoginPrompt() {
    document.getElementById('loginModal').style.display = 'block';
}

function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Show only first 6 feedbacks initially if there are more
document.addEventListener('DOMContentLoaded', function() {
    const feedbackCards = document.querySelectorAll('.feedback-card');
    const loadMoreContainer = document.querySelector('.load-more-container');
    
    if (feedbackCards.length > 6) {
        // Hide cards after the 6th one
        for (let i = 6; i < feedbackCards.length; i++) {
            feedbackCards[i].style.display = 'none';
        }
        // Show the load more button
        if (loadMoreContainer) {
            loadMoreContainer.style.display = 'block';
        }
    }
});

function loadMoreFeedback() {
    const hiddenCards = document.querySelectorAll('.feedback-card[style*="display: none"]');
    const loadMoreBtn = document.querySelector('.load-more-container');
    
    // Show next 6 cards
    for (let i = 0; i < Math.min(6, hiddenCards.length); i++) {
        hiddenCards[i].style.display = 'block';
    }
    
    // Hide load more button if all cards are visible
    if (hiddenCards.length <= 6) {
        loadMoreBtn.style.display = 'none';
    }
}
</script>

<?php require 'footer.php'; ?>