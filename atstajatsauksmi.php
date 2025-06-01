<?php
require 'header.php';

// Redirect if not logged in
if (!isset($_SESSION['lietotajvardsSIN'])) {
    header("Location: index.php");
    exit();
}

// Include database operations
require 'admin/db/atstajatsauksmi.php';

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
    
    <?php if ($order_count > 0 || $custom_order_count > 0): ?>
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
require 'footer.php';
?>