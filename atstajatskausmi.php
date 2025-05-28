<?php
require 'header.php';
require 'admin/db/con_db.php';

// Redirect if not logged in
if (!isset($_SESSION['lietotajs'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['id_lietotajs'];
$error_message = '';
$success_message = '';

// Check if user has completed orders using LEFT JOIN
$check_orders_sql = "SELECT COUNT(*) as order_count 
                    FROM pasutijumi_sparkly p
                    LEFT JOIN lietotaji_sparkly l ON p.id_lietotajs = l.id_lietotajs
                    WHERE p.id_lietotajs = ? AND p.statuss IN ('nosūtīts', 'saņemts')";
$stmt = $savienojums->prepare($check_orders_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order_count = $result->fetch_assoc()['order_count'];

if ($order_count == 0) {
    $error_message = "Jums jābūt vismaz vienam pabegtam pasūtījumam, lai varētu atstāt atsauksmi.";
}

// Get user's completed orders using LEFT JOIN
$orders_sql = "SELECT p.id_pasutijums, p.kopeja_cena, p.izveidots, 
                      GROUP_CONCAT(DISTINCT pr.nosaukums SEPARATOR ', ') as produkti
               FROM pasutijumi_sparkly p
               LEFT JOIN lietotaji_sparkly l ON p.id_lietotajs = l.id_lietotajs
               LEFT JOIN pasutijumu_preces pp ON p.id_pasutijums = pp.id_pasutijums
               LEFT JOIN preces_sparkly pr ON pp.id_prece = pr.id_prece
               WHERE p.id_lietotajs = ? AND p.statuss IN ('nosūtīts', 'saņemts')
               GROUP BY p.id_pasutijums
               ORDER BY p.izveidots DESC";
$stmt = $savienojums->prepare($orders_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_orders = $stmt->get_result();

// Handle form submission
if ($_POST && $order_count > 0) {
    $id_pasutijums = $_POST['id_pasutijums'];
    $vards_uzvards = $_POST['vards_uzvards'];
    $ratejums = (int)$_POST['ratejums'];
    $atsauksme = trim($_POST['atsauksme']);
    
    // Validate input
    if (empty($vards_uzvards) || empty($atsauksme) || $ratejums < 1 || $ratejums > 5) {
        $error_message = "Lūdzu, aizpildiet visus laukus un izvēlieties derīgu vērtējumu (1-5 zvaigznes).";
    } else {
        // Check if user hasn't already left feedback for this order using LEFT JOIN
        $check_feedback_sql = "SELECT f.id_feedback 
                              FROM feedback_sparkly f
                              LEFT JOIN lietotaji_sparkly l ON f.id_lietotajs = l.id_lietotajs
                              LEFT JOIN pasutijumi_sparkly p ON f.id_pasutijums = p.id_pasutijums
                              WHERE f.id_lietotajs = ? AND f.id_pasutijums = ?";
        $stmt = $savienojums->prepare($check_feedback_sql);
        $stmt->bind_param("ii", $user_id, $id_pasutijums);
        $stmt->execute();
        $existing_feedback = $stmt->get_result();
        
        if ($existing_feedback->num_rows > 0) {
            $error_message = "Jūs jau esat atstājis atsauksmi par šo pasūtījumu.";
        } else {
            // Verify that the order belongs to this user using LEFT JOIN
            $verify_order_sql = "SELECT p.id_pasutijums 
                                FROM pasutijumi_sparkly p
                                LEFT JOIN lietotaji_sparkly l ON p.id_lietotajs = l.id_lietotajs
                                WHERE p.id_pasutijums = ? AND p.id_lietotajs = ? 
                                AND p.statuss IN ('nosūtīts', 'saņemts')";
            $stmt = $savienojums->prepare($verify_order_sql);
            $stmt->bind_param("ii", $id_pasutijums, $user_id);
            $stmt->execute();
            $verify_result = $stmt->get_result();
            
            if ($verify_result->num_rows == 0) {
                $error_message = "Nederīgs pasūtījums vai jums nav tiesību atstāt atsauksmi par šo pasūtījumu.";
            } else {
                // Insert feedback
                $insert_sql = "INSERT INTO feedback_sparkly (id_lietotajs, id_pasutijums, vards_uzvards, ratejums, atsauksme) 
                              VALUES (?, ?, ?, ?, ?)";
                $stmt = $savienojums->prepare($insert_sql);
                $stmt->bind_param("iisis", $user_id, $id_pasutijums, $vards_uzvards, $ratejums, $atsauksme);
                
                if ($stmt->execute()) {
                    $success_message = "Jūsu atsauksme ir veiksmīgi iesniegta un gaida administratora apstiprinājumu.";
                    // Refresh the page to clear the form
                    echo "<script>
                            setTimeout(function() {
                                window.location.href = 'atstajatsauksmi.php';
                            }, 3000);
                          </script>";
                } else {
                    $error_message = "Kļūda, iesniedzot atsauksmi. Lūdzu, mēģiniet vēlreiz.";
                }
            }
        }
    }
}

// Get user's previous feedback using LEFT JOIN
$previous_feedback_sql = "SELECT f.*, 
                         f.izveidots as feedback_date,
                         CONCAT('Pasūtījums #', f.id_pasutijums) as order_ref,
                         CASE 
                            WHEN f.apstiprināts = 1 THEN 'Apstiprināta'
                            ELSE 'Gaida apstiprinājumu'
                         END as status_text
                         FROM feedback_sparkly f
                         LEFT JOIN pasutijumi_sparkly p ON f.id_pasutijums = p.id_pasutijums
                         LEFT JOIN lietotaji_sparkly l ON f.id_lietotajs = l.id_lietotajs
                         WHERE f.id_lietotajs = ?
                         ORDER BY f.izveidots DESC";
$stmt = $savienojums->prepare($previous_feedback_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$previous_feedback = $stmt->get_result();

$savienojums->close();
?>

<main>
    <section id="atsauksmesforma">
        <h1>Atstāt atsauksmi</h1>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($order_count > 0 && !$success_message): ?>
            <div class="feedback-form-container">
                <div class="form-description">
                    <p>Dalieties ar savu pieredzi! Jūsu atsauksme palīdzēs citiem klientiem pieņemt lēmumus un mums uzlabot mūsu pakalpojumus.</p>
                </div>
                
                <form class="feedback-form" method="POST">
                    <div class="form-group">
                        <label for="id_pasutijums">Izvēlēties pasūtījumu:</label>
                        <select id="id_pasutijums" name="id_pasutijums" required>
                            <option value="">-- Izvēlēties pasūtījumu --</option>
                            <?php 
                            $completed_orders->data_seek(0); // Reset result pointer
                            while ($order = $completed_orders->fetch_assoc()): ?>
                                <option value="<?php echo $order['id_pasutijums']; ?>">
                                    Pasūtījums #<?php echo $order['id_pasutijums']; ?> 
                                    (<?php echo date('d.m.Y', strtotime($order['izveidots'])); ?>) - 
                                    €<?php echo number_format($order['kopeja_cena'], 2); ?>
                                    <?php if ($order['produkti']): ?>
                                        - <?php echo substr($order['produkti'], 0, 50) . (strlen($order['produkti']) > 50 ? '...' : ''); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="vards_uzvards">Vārds un uzvārds (tiks rādīts publiski):</label>
                        <input type="text" id="vards_uzvards" name="vards_uzvards" 
                               value="<?php echo $_SESSION['vards'] . ' ' . substr($_SESSION['uzvards'], 0, 1) . '.'; ?>" 
                               maxlength="100" required>
                        <small>Jūs varat mainīt, kā jūsu vārds tiks rādīts (piemēram, tikai vārds vai iniciāļi)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="ratejums">Vērtējums:</label>
                        <div class="star-rating" id="star-rating">
                            <input type="radio" name="ratejums" value="5" id="star5" required>
                            <label for="star5" title="5 zvaigznes">★</label>
                            <input type="radio" name="ratejums" value="4" id="star4">
                            <label for="star4" title="4 zvaigznes">★</label>
                            <input type="radio" name="ratejums" value="3" id="star3">
                            <label for="star3" title="3 zvaigznes">★</label>
                            <input type="radio" name="ratejums" value="2" id="star2">
                            <label for="star2" title="2 zvaigznes">★</label>
                            <input type="radio" name="ratejums" value="1" id="star1">
                            <label for="star1" title="1 zvaigzne">★</label>
                        </div>
                        <div class="rating-text" id="rating-text"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="atsauksme">Jūsu atsauksme:</label>
                        <textarea id="atsauksme" name="atsauksme" rows="6" maxlength="1000" required 
                                  placeholder="Pastāstiet par savu pieredzi ar mūsu produktiem un pakalpojumiem..."></textarea>
                        <small><span id="char-count">0</span>/1000 rakstzīmes</small>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Iesniegt atsauksmi
                        </button>
                        <a href="index.php" class="btn btn-secondary">Atcelt</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if ($previous_feedback->num_rows > 0): ?>
            <div class="previous-feedback-container">
                <h2>Jūsu iepriekšējās atsauksmes</h2>
                
                <?php while ($feedback = $previous_feedback->fetch_assoc()): ?>
                    <div class="feedback-item">
                        <div class="feedback-header">
                            <div class="feedback-info">
                                <h3><?php echo htmlspecialchars($feedback['order_ref']); ?></h3>
                                <div class="feedback-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= $feedback['ratejums'] ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="feedback-status">
                                <span class="status-badge <?php echo $feedback['apstiprināts'] ? 'approved' : 'pending'; ?>">
                                    <?php echo $feedback['status_text']; ?>
                                </span>
                                <small><?php echo date('d.m.Y H:i', strtotime($feedback['feedback_date'])); ?></small>
                            </div>
                        </div>
                        <div class="feedback-content">
                            <p><strong><?php echo htmlspecialchars($feedback['vards_uzvards']); ?></strong></p>
                            <p><?php echo nl2br(htmlspecialchars($feedback['atsauksme'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        
    </section>
</main>

<!-- JavaScript for enhanced form interaction -->
<script>
// Star rating interaction
document.addEventListener('DOMContentLoaded', function() {
    const starRating = document.getElementById('star-rating');
    const ratingText = document.getElementById('rating-text');
    const charCount = document.getElementById('char-count');
    const textarea = document.getElementById('atsauksme');
    
    // Rating descriptions
    const ratingDescriptions = {
        1: 'Slikti - neapmierināts',
        2: 'Viduvēji - nav īpaši apmierināts',
        3: 'Labi - apmierināts',
        4: 'Ļoti labi - ļoti apmierināts',
        5: 'Izcili - pilnīgi apmierināts'
    };
    
    // Handle star rating
    if (starRating) {
        const stars = starRating.querySelectorAll('label');
        const inputs = starRating.querySelectorAll('input');
        
        stars.forEach((star, index) => {
            star.addEventListener('mouseenter', function() {
                highlightStars(5 - index);
            });
            
            star.addEventListener('click', function() {
                const value = 5 - index;
                ratingText.textContent = ratingDescriptions[value];
                ratingText.className = 'rating-text visible';
            });
        });
        
        starRating.addEventListener('mouseleave', function() {
            // Reset to selected rating
            const checkedInput = starRating.querySelector('input:checked');
            if (checkedInput) {
                highlightStars(checkedInput.value);
            } else {
                removeAllHighlights();
            }
        });
        
        function highlightStars(rating) {
            stars.forEach((star, index) => {
                if (5 - index <= rating) {
                    star.classList.add('highlight');
                } else {
                    star.classList.remove('highlight');
                }
            });
        }
        
        function removeAllHighlights() {
            stars.forEach(star => star.classList.remove('highlight'));
        }
    }
    
    // Character count for textarea
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
});
</script>

<style>
/* Feedback form styles */
#atsauksmesforma {
    padding: 4rem 6%;
    max-width: 1000px;
    margin: 0 auto;
}

.error-message, .success-message {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
    text-align: center;
    font-weight: 500;
}

.error-message {
    background-color: #ffebee;
    color: #d32f2f;
    border: 1px solid #ffcdd2;
}

.success-message {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.feedback-form-container {
    background-color: white;
    border-radius: 1rem;
    box-shadow: var(--box-shadow);
    padding: 2rem;
    margin-bottom: 3rem;
}

.form-description {
    background-color: var(--light2);
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
    border-left: 4px solid var(--maincolor);
}

.form-description p {
    margin: 0;
    color: var(--text);
    font-style: italic;
}

.feedback-form .form-group {
    margin-bottom: 1.5rem;
}

.feedback-form label {
    display: block;
    font-weight: 500;
    color: var(--tumsa);
    margin-bottom: 0.5rem;
}

.feedback-form input, 
.feedback-form select, 
.feedback-form textarea {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid var(--light3);
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.feedback-form input:focus, 
.feedback-form select:focus, 
.feedback-form textarea:focus {
    border-color: var(--maincolor);
    outline: none;
    box-shadow: 0 0 0.5rem rgba(3, 135, 206, 0.3);
}

.feedback-form small {
    display: block;
    color: var(--text);
    margin-top: 0.3rem;
    font-size: 0.9rem;
}

/* Star rating styles */
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    margin: 0.5rem 0;
}

.star-rating input {
    display: none;
}

.star-rating label {
    cursor: pointer;
    font-size: 2rem;
    color: #ddd;
    transition: color 0.3s ease;
    margin-right: 0.1rem;
}

.star-rating label:hover,
.star-rating label.highlight,
.star-rating input:checked ~ label {
    color: #ffc107;
}

.rating-text {
    margin-top: 0.5rem;
    font-weight: 500;
    color: var(--tumsa);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.rating-text.visible {
    opacity: 1;
}

/* Form buttons */
.form-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
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

/* Previous feedback styles */
.previous-feedback-container {
    background-color: white;
    border-radius: 1rem;
    box-shadow: var(--box-shadow);
    padding: 2rem;
}

.previous-feedback-container h2 {
    color: var(--tumsa);
    margin-bottom: 1.5rem;
    text-align: center;
}

.feedback-item {
    border: 1px solid var(--light3);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    background-color: #fafafa;
}

.feedback-item:last-child {
    margin-bottom: 0;
}

.feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.feedback-info h3 {
    color: var(--tumsa);
    margin-bottom: 0.5rem;
}

.feedback-rating .star {
    color: #ddd;
    font-size: 1.2rem;
}

.feedback-rating .star.filled {
    color: #ffc107;
}

.feedback-status {
    text-align: right;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.approved {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-badge.pending {
    background-color: #fff3e0;
    color: #f57c00;
}

.feedback-content p {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

/* Responsive design */
@media (max-width: 768px) {
    .feedback-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .feedback-status {
        text-align: left;
    }
    
    .form-buttons {
        flex-direction: column;
    }
    
    .star-rating label {
        font-size: 1.5rem;
    }
}
</style>

<?php require 'footer.php'; ?>