<?php
require 'header.php';
require 'admin/db/con_db.php';
$_SESSION['redirect_after_login'] = "atstajatsauksmi.php";

// Display session notification if exists
if (isset($_SESSION['pazinojums'])) {
    echo '<div class="success-message">' . htmlspecialchars($_SESSION['pazinojums']) . '</div>';
    unset($_SESSION['pazinojums']);
}

// Check if user is logged in to determine feedback availability
$user_can_leave_feedback = false;
$user_id = null;

if (isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['redirect_after_login'] = "atsauksmes.php";
    $username = $_SESSION['lietotajvardsSIN'];
    $user_query = "SELECT id_lietotajs, pas_skaits FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $user_stmt = $savienojums->prepare($user_query);
    
    if ($user_stmt) {
        $user_stmt->bind_param("s", $username);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        
        if ($user && $user['pas_skaits'] > 0) {
            $user_can_leave_feedback = true;
            $user_id = $user['id_lietotajs'];
        }
        $user_stmt->close();
    }
}

// Get approved feedback with improved query including user photos
$feedback_sql = "SELECT a.id_atsauksme, a.vards_uzvards, a.zvaigznes, a.atsauksme, a.datums,
                        l.lietotajvards, l.foto
                 FROM sparkly_atsauksmes a
                 LEFT JOIN lietotaji_sparkly l ON a.lietotajs_id = l.id_lietotajs
                 ORDER BY a.datums DESC";

$result = $savienojums->query($feedback_sql);
$approved_feedback = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $approved_feedback[] = $row;
    }
}

// Calculate average rating
$average_rating = 0;
$total_ratings = 0;
if (!empty($approved_feedback)) {
    $sum_ratings = array_sum(array_column($approved_feedback, 'zvaigznes'));
    $total_ratings = count($approved_feedback);
    $average_rating = $sum_ratings / $total_ratings;
}

$savienojums->close();
require 'header.php';
require 'admin/db/con_db.php';
$_SESSION['redirect_after_login'] = "atstajatsauksmi.php";

// Display session notification if exists
if (isset($_SESSION['pazinojums'])) {
    echo '<div class="success-message">' . htmlspecialchars($_SESSION['pazinojums']) . '</div>';
    unset($_SESSION['pazinojums']);
}

// Check if user is logged in to determine feedback availability
$user_can_leave_feedback = false;
$user_id = null;

if (isset($_SESSION['lietotajvardsSIN'])) {
    $_SESSION['redirect_after_login'] = "atsauksmes.php";
    $username = $_SESSION['lietotajvardsSIN'];
    $user_query = "SELECT id_lietotajs, pas_skaits FROM lietotaji_sparkly WHERE lietotajvards = ?";
    $user_stmt = $savienojums->prepare($user_query);
    
    if ($user_stmt) {
        $user_stmt->bind_param("s", $username);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        
        if ($user && $user['pas_skaits'] > 0) {
            $user_can_leave_feedback = true;
            $user_id = $user['id_lietotajs'];
        }
        $user_stmt->close();
    }
}

// Get approved feedback with improved query including user photos
$feedback_sql = "SELECT a.id_atsauksme, a.vards_uzvards, a.zvaigznes, a.atsauksme, a.datums,
                        l.lietotajvards, l.foto
                 FROM sparkly_atsauksmes a
                 LEFT JOIN lietotaji_sparkly l ON a.lietotajs_id = l.id_lietotajs
                 ORDER BY a.datums DESC";

$result = $savienojums->query($feedback_sql);
$approved_feedback = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $approved_feedback[] = $row;
    }
}

// Calculate average rating
$average_rating = 0;
$total_ratings = 0;
if (!empty($approved_feedback)) {
    $sum_ratings = array_sum(array_column($approved_feedback, 'zvaigznes'));
    $total_ratings = count($approved_feedback);
    $average_rating = $sum_ratings / $total_ratings;
}

$savienojums->close();
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
            <?php if ($user_can_leave_feedback): ?>
                <a href="atstajatsauksmi.php" class="btn">
                    <i class="fas fa-star"></i> Atstāt atsauksmi
                </a>
            <?php else: ?>
                <span class="btn btn-disabled" title="Nepieciešams pabeigt vismaz vienu pasūtījumu">
                    <i class="fas fa-star"></i> Atstāt atsauksmi
                </span>
            <?php endif; ?>
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
        <?php endif; ?>
    </div>
    
    <!-- Feedback action button -->
    <div class="feedback-action">
        <?php if (isset($_SESSION['lietotajvardsSIN'])): ?>
            <?php if ($user_can_leave_feedback): ?>
                <a href="atstajatsauksmi.php" class="btn">
                    <i class="fas fa-star"></i> Atstāt atsauksmi
                </a>
            <?php else: ?>
                <span class="btn btn-disabled" title="Nepieciešams pabeigt vismaz vienu pasūtījumu">
                    <i class="fas fa-star"></i> Atstāt atsauksmi
                </span>
            <?php endif; ?>
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
            <?php endforeach; ?>
        </div>
        
        <!-- Load more button if there are many feedbacks -->
        <?php if (count($approved_feedback) > 6): ?>
            <div class="load-more-container" style="display: none;">
                <button class="btn btn-secondary" onclick="loadMoreFeedback()">
                    Rādīt vairāk atsauksmju
                </button>
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

<style>

</style>

<?php require 'footer.php'; ?>
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

<style>
/* Enhanced styles for feedback page */
#atsauksmesNew {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.feedback-header {
    text-align: center;
    margin-bottom: 2rem;
}

.feedback-header h1 {
    color: var(--tumsa);
    margin-bottom: 1rem;
}

.feedback-stats {
    background-color: var(--light3);
    padding: 1.5rem;
    border-radius: 1rem;
    margin-bottom: 1rem;
}

.average-rating {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.rating-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--maincolor);
}

.stars {
    color: #ffd700;
    font-size: 1.5rem;
}

.rating-count {
    color: var(--text);
    font-size: 1rem;
}

.feedback-action {
    text-align: center;
    margin: 2rem 0;
}

.feedback-action .btn {
    background-color: var(--maincolor);
    color: white;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    border-radius: 2rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.feedback-action .btn:hover {
    background-color: var(--tumsa);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(3, 135, 206, 0.3);
}

.btn-disabled {
    background-color: var(--light1) !important;
    color: var(--text) !important;
    cursor: not-allowed !important;
}

.btn-disabled:hover {
    transform: none !important;
    box-shadow: none !important;
}

/* Grid layout for feedback cards */
.feedback-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.feedback-card {
    background-color: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid var(--light3);
}

.feedback-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.feedback-header-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 80px;
    height: 80px;
    background-color: var(--light3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--maincolor);
    font-size: 2rem;
    position: relative;
    overflow: hidden;
    border: 3px solid var(--light3);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.profile-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    position: absolute;
    top: 0;
    left: 0;
}

.default-avatar {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--light3);
    border-radius: 50%;
    color: var(--maincolor);
    font-size: 2rem;
}

.user-details h3 {
    margin: 0;
    color: var(--tumsa);
    font-size: 1.1rem;
}

.feedback-date {
    color: var(--text);
    font-size: 0.9rem;
}

.rating {
    color: #ffd700;
    font-size: 1.2rem;
}

.feedback-content p {
    color: var(--text);
    line-height: 1.6;
    font-style: italic;
    margin: 0;
}

/* No feedback state */
.no-feedback {
    text-align: center;
    padding: 3rem;
    background-color: var(--light3);
    border-radius: 1rem;
    margin: 2rem auto;
    max-width: 600px;
}

.no-feedback i {
    font-size: 3rem;
    color: var(--light1);
    margin-bottom: 1rem;
}

.no-feedback h3 {
    color: var(--tumsa);
    margin-bottom: 1rem;
}

.no-feedback p {
    color: var(--text);
    margin-bottom: 2rem;
}

/* Footer */
.feedback-footer {
    text-align: center;
    margin-top: 3rem;
}

.btn-outline {
    background-color: transparent;
    color: var(--maincolor);
    border: 2px solid var(--maincolor);
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background-color: var(--maincolor);
    color: white;
}

/* Load more button */
.load-more-container {
    text-align: center;
    margin: 2rem 0;
}

/* Success message */
.success-message {
    background-color: #e8f5e9;
    color: #2e7d32;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    text-align: center;
    border-left: 4px solid #4caf50;
}

/* Modal styles */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background-color: white;
    margin: 15% auto;
    border-radius: 1rem;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--light3);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    color: var(--tumsa);
    margin: 0;
    font-size: 1.3rem;
}

.close {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--text);
    cursor: pointer;
    transition: color 0.3s ease;
}

.close:hover {
    color: var(--tumsa);
}

.modal-body {
    padding: 1.5rem;
    text-align: center;
}

.modal-body p {
    color: var(--text);
    margin-bottom: 2rem;
    line-height: 1.6;
}

.modal-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.modal-buttons .btn {
    padding: 0.8rem 1.5rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: var(--tumsa);
    color: white;
}

.btn-primary:hover {
    background-color: var(--maincolor);
}

.btn-secondary {
    background-color: var(--light3);
    color: var(--tumsa);
}

.btn-secondary:hover {
    background-color: var(--text);
    color: white;
}

/* Responsive design */
@media (max-width: 768px) {
    .feedback-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .feedback-action .btn {
        width: 90%;
        justify-content: center;
    }
    
    .average-rating {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .rating-value {
        font-size: 2rem;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .feedback-header-card {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    #atsauksmesNew {
        padding: 1rem;
    }
    
    .feedback-card {
        padding: 1rem;
    }
    
    .modal-buttons {
        flex-direction: column;
    }
    
    .modal-buttons .btn {
        width: 100%;
    }
}
</style>

<?php require 'footer.php'; ?>