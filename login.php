<?php
        session_start();
    ?>
    <!DOCTYPE html>
    <html lang="lv">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Ielogošanās</title>

        <title>IT atbalsts - ielogošanās</title>

        <link rel="stylesheet" href="assets/style_login.css">
        <script src="assets/script_animate.js" defer></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
        <link rel="shortcut icon" href="images/bumba.png" type="image/x-icon">
    </head>
    <body>
    <i id="theme-icon" class="fa fa-moon" style="display: none;"></i>

        <div class="wrapper">
            <h1>Ielogošanās</h1>
                <?php
                    if(isset($_SESSION['pazinojums'])){
                        echo "<p class='login-notif'>".$_SESSION['pazinojums']."</p>";
                        unset($_SESSION['pazinojums']);
                    }
                ?>
                <form action="admin/db/login_function.php" method="post">
                <div class="input-box">    

                   <input type="text" name="lietotajvards" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                    <label>Lietotājvārds vai e-pasts:</label>
                </div>
                <div class="input-box"> 
                    <input type="password" name="parole" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                    <label>Parole:</label>
                </div>
                
                <button class="button" name="ielogoties">Ielogoties</button>
                <p>Nav konta? <a href="register.php">Reģistrēties</a></p>
                <a href="./" class="button">Atgriezties uz sākumlapu</a>
                </form>

            </div>
        
    </body>
    </html>