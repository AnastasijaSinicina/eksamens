<?php
require 'header.php';
require 'admin/db/con_db.php';

// Redirect if not logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    header("Location: index.php");
    exit();
}

// Get user data including completed orders count (pas_skaits)
$username = $_SESSION['lietotajvardsSIN'];
$user_query = "SELECT id_lietotajs, pas_skaits FROM lietotaji_sparkly WHERE lietotajvards = ?";
$user_stmt = $savienojums->prepare($user_query);

if (!$user_stmt) {
    die("User query prepare failed: " . $savienojums->error);
}

$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    // If user not found, redirect to login
    session_destroy();
    header("Location: login.php");
    exit();
}

$user_id = $user['id_lietotajs'];
$order_count = $user['pas_skaits'] ?? 0;

if ($order_count == 0) {
    $error_message = "Jums jābūt vismaz vienam pabegtam pasūtījumam, lai varētu atstāt atsauksmi.";
}

// Handle feedback submission
if ($_POST && isset($_POST['submit_feedback'])) {
    $rating = intval($_POST['rating']);
    $feedback_text = htmlspecialchars($_POST['feedback']);
    $user_name = htmlspecialchars($_POST['user_name']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($feedback_text) && !empty($user_name)) {
        // Insert feedback into database
        $insert_feedback_sql = "INSERT INTO sparkly_atsauksmes (lietotajs_id, vards_uzvards, zvaigznes, atsauksme, datums, apstiprinats) 
                               VALUES (?, ?, ?, ?, NOW(), 0)";
        
        $feedback_stmt = $savienojums->prepare($insert_feedback_sql);
        
        if ($feedback_stmt) {
            $feedback_stmt->bind_param("isis", $user_id, $user_name, $rating, $feedback_text);
            
            if ($feedback_stmt->execute()) {
                $success_message = "Jūsu atsauksme ir veiksmīgi nosūtīta! Tā tiks pārskatīta un apstiprināta īsā laikā.";
                $_SESSION['pazinojums'] = $success_message;
                header("Location: atsauksmes.php");
                exit();
            } else {
                $error_message = "Kļūda saglabājot atsauksmi: " . $feedback_stmt->error;
            }
            $feedback_stmt->close();
        } else {
            $error_message = "Kļūda sagatavojot vaicājumu: " . $savienojums->error;
        }
    } else {
        $error_message = "Lūdzu aizpildiet visus laukus un izvēlieties vērtējumu.";
    }
}

// Get completed orders for display (using correct table names)
$orders_sql = "SELECT p.id_pasutijums, p.kopeja_cena, p.pas_datums, 
                      GROUP_CONCAT(DISTINCT pr.nosaukums SEPARATOR ', ') as produkti
               FROM sparkly_pasutijumi p
               LEFT JOIN sparkly_pasutijuma_vienumi pv ON p.id_pasutijums = pv.pasutijuma_id
               LEFT JOIN produkcija_sprarkly pr ON pv.produkta_id = pr.id_bumba
               WHERE p.lietotajs_id = ? AND p.statuss IN ('nosūtīts', 'saņemts')
               GROUP BY p.id_pasutijums
               ORDER BY p.pas_datums DESC";

$stmt = $savienojums->prepare($orders_sql);
if (!$stmt) {
    die("Orders query prepare failed: " . $savienojums->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_orders = $stmt->get_result();
?>

<section id="atstajatsauksmi">
    <h1>Atstāt atsauksmi</h1>
    
    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        <div class="success-message">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($order_count > 0): ?>
        <div class="feedback-form-container">
            <h2>Dalieties ar savu pieredzi</h2>
            <p>Jūsu viedoklis ir mums svarīgs! Lūdzu, novērtējiet mūsu pakalpojumus.</p>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="user_name">Jūsu vārds un uzvārds*:</label>
                    <input type="text" id="user_name" name="user_name" required>
                </div>
                
                <div class="form-group">
                    <label for="rating">Vērtējums*:</label>
                    <div class="rating-input">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                            <label for="star<?php echo $i; ?>" class="star">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="feedback">Jūsu atsauksme*:</label>
                    <textarea id="feedback" name="feedback" rows="5" required placeholder="Pastāstiet par savu pieredzi ar mūsu produktiem un pakalpojumiem..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="submit_feedback" class="btn btn-primary">Nosūtīt atsauksmi</button>
                    <a href="atsauksmes.php" class="btn btn-secondary">Skatīt atsauksmes</a>
                </div>
            </form>
        </div>
        
        <div class="completed-orders">
            <h3>Jūsu pabegtie pasūtījumi</h3>
            <div class="orders-list">
                <?php while ($order = $completed_orders->fetch_assoc()): ?>
                    <div class="order-item">
                        <div class="order-details">
                            <strong>Pasūtījums #<?php echo $order['id_pasutijums']; ?></strong>
                            <span class="order-date"><?php echo date('d.m.Y', strtotime($order['pas_datums'])); ?></span>
                        </div>
                        <div class="order-products">
                            <?php echo $order['produkti'] ? htmlspecialchars($order['produkti']) : 'Nav norādīti produkti'; ?>
                        </div>
                        <div class="order-total">
                            €<?php echo number_format($order['kopeja_cena'], 2); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="no-orders">
            <i class="fas fa-shopping-cart"></i>
            <h3>Nav pabeigtu pasūtījumu</h3>
            <p>Lai atstātu atsauksmi, jums vispirms jāveic pasūtījums un tas jāsaņem.</p>
            <a href="produkcija.php" class="btn btn-primary">Apskatīt produktus</a>
        </div>
    <?php endif; ?>
</section>

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 0.1rem;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input .star {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.3s ease;
}

.rating-input .star:hover,
.rating-input .star:hover ~ .star,
.rating-input input[type="radio"]:checked ~ .star {
    color: #ffd700;
}

.completed-orders {
    margin-top: 2rem;
    background-color: var(--light3);
    padding: 1.5rem;
    border-radius: 1rem;
}

.order-item {
    background-color: white;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.order-details {
    display: flex;
    flex-direction: column;
}

.order-date {
    font-size: 0.9rem;
    color: var(--text);
}

.order-products {
    flex: 1;
    margin: 0 1rem;
    color: var(--text);
}

.order-total {
    font-weight: bold;
    color: var(--maincolor);
}

.no-orders {
    text-align: center;
    padding: 3rem;
    background-color: var(--light3);
    border-radius: 1rem;
}

.no-orders i {
    font-size: 3rem;
    color: var(--light1);
    margin-bottom: 1rem;
}

.error-message, .success-message {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.error-message {
    background-color: #fee;
    border: 1px solid #fcc;
    color: #c00;
}

.success-message {
    background-color: #efe;
    border: 1px solid #cfc;
    color: #060;
}

.feedback-form-container {
    background-color: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--tumsa);
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--light1);
    border-radius: 0.5rem;
    font-size: 1rem;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--maincolor);
    box-shadow: 0 0 0 3px rgba(3, 135, 206, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn-secondary {
    background-color: var(--light3);
    color: var(--tumsa);
}

.btn-secondary:hover {
    background-color: var(--light1);
}

@media (max-width: 768px) {
    .order-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-products {
        margin: 0.5rem 0;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<?php
$savienojums->close();
require 'footer.php';
?>