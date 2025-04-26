<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Get the current directory path for more reliable inclusion
$basePath = __DIR__;

// Fix the paths to point to the correct location using absolute paths
require $basePath . '/PHPMailer/src/Exception.php';
require $basePath . '/PHPMailer/src/SMTP.php';
require $basePath . '/PHPMailer/src/PHPMailer.php';


//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

if(isset($_POST["nosutit"])){

try {
    //Server settings
    $mail->CharSet = "UTF-8"; 
    $mail->SMTPDebug = 0;                                    //1 - lai redzet kludu, 0 lai paslept
    $mail->isSMTP();                                            //Send using SMTPš
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'sparkly.dream.shop@gmail.com';                     //SMTP username
    $mail->Password   = 'jhpy rcii onmi dnws';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;                                 //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('sparkly.dream.shop@gmail.com', 'Sparkly Dream saziņa');
    $mail->addAddress('sparkly.dream.shop@gmail.com', 'Sparkly Dream saziņa');     //Add a recipient

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Sparkly Dream - Jauna ziņa no kontaktu formas';
    $mail->Body    = 'Ziņas sutītāja vārds, uzvārds: <b>'.$_POST['vards'].'</b><br> 
    Ziņas sūtītāja e-pasts: <b>'.$_POST['epasts'].'</b><br>
    Ziņas sūtītāja tālrunis:  <b>'.$_POST['talrunis'].'</b><br>
    Ziņojums: <b>'.$_POST['zinojums'].'</b>';


    $mail->send();
    echo "<div id='pazinojums'>
        <p>Ziņa nosutīta! Sazinasimies ar jums pavisam drīz!
            <a onclick='x()'><i class='fas fa-times'></i></a>
        </p>
    </div>";
} catch (Exception $e) {
    echo "Ziņu nevar nosūtīt! Sistēmas kļūda: {$mail->ErrorInfo}";
    echo "<br>Детали ошибки: " . $e->getMessage();
}
}
?>