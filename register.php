<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/style_login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <title>Reģistrācija</title>
</head>
<body>
<div class="wrapper">
    <h1>Reģistrācija</h1>
    <?php
        if(isset($_SESSION['pazinojums'])){
            echo "<p class='login-notif'>".$_SESSION['pazinojums']."</p>";
            unset($_SESSION['pazinojums']);
        }
    ?>
    <form action="admin/db/register_function.php" method="post" id="registerForm">
        <div class="input-box">
            <input type="email" name="epasts" required>
            <label>E-pasts</label>
        </div>
        <div class="input-box">
            <input type="text" name="vards" required>
            <label>Vārds</label>
        </div>
        <div class="input-box">
            <input type="text" name="uzvards" required>
            <label>Uzvārds</label>
        </div>
        <div class="input-box">
            <input type="text" name="lietotajvards" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
            <label>Lietotājvārds</label>
        </div>
        <div class="input-box password-box">
            <input type="password" name="parole" id="parole" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
            <label>Parole</label>
        </div>
        <div class="input-box password-box">
            <input type="password" name="parole_atkartoti" id="parole_atkartoti" required>
            <label>Parole atkārtoti</label>
            <span class="icon-container match-container" style="display: none;">
                <i class="fas fa-check password-match-icon"></i>
            </span>
            <span class="icon-container mismatch-container" style="display: none;">
                <i class="fas fa-times password-mismatch-icon"></i>
            </span>
        </div>
        <button class="button" name="registreties">Reģistrēties</button>
        <p>Jau reģistrēts? <a href="login.php">Ielogoties</a></p>
        <a href="./" class="button">Atgriezties uz sākumlapu</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('parole');
    const confirmPassword = document.getElementById('parole_atkartoti');
    
    // Function to check if passwords match
    function checkPasswordMatch() {
        const matchContainer = document.querySelector('.match-container');
        const mismatchContainer = document.querySelector('.mismatch-container');
        
        if (confirmPassword.value === '') {
            // If confirm password is empty, hide both icons
            matchContainer.classList.remove('visible');
            mismatchContainer.classList.remove('visible');
        } else if (password.value === confirmPassword.value) {
            // Passwords match
            matchContainer.classList.add('visible');
            mismatchContainer.classList.remove('visible');
        } else {
            // Passwords don't match
            matchContainer.classList.remove('visible');
            mismatchContainer.classList.add('visible');
        }
    }
    
    // Check passwords on input
    password.addEventListener('input', checkPasswordMatch);
    confirmPassword.addEventListener('input', checkPasswordMatch);
    
    // Form validation before submit
    document.getElementById('registerForm').addEventListener('submit', function(event) {
        if (password.value !== confirmPassword.value) {
            event.preventDefault();
            alert('Paroles nesakrīt!');
        }
    });
});
</script>
</body>
</html>