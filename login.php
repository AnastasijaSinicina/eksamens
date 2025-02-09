<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/style_login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Login</title>
</head>
<body>
<div class="wrapper">
    <h1>Login</h1>
    <div class="input-box">
        <input type="text" required>
        <label>Username</label>
    </div>
    <div class="input-box">
        <input type="password" required>
        <label>Password</label>
    </div>
    <button class="button">Login</button>
    <p>Don't have an account? <a href="register.php">Register</a></p>
    <a href="./" class="button">Atgriezties uz sākumlapu</a>
</div>
</body>
</html>