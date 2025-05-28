<?php
    if(!empty($_GET['session_id'])){
        session_start(); //nekā nav saīstīs ar maksājumu sesiju, tas ir paziņojumiem
        $session_id = $_GET['session_id'];

        require_once '../../../stripe-php-master/init.php';
        require_once 'config.php';
        

        try{
           $checkout_session = \Stripe\Checkout\Session::retrieve($session_id);
           $customer_email = $checkout_session->customer_details->email;

           $paymentIntent = \Stripe\PaymentIntent::retrieve($checkout_session->payment_intent);

           if($paymentIntent->status == 'succeeded'){
            $transactionID = $paymentIntent->id;

            require '../admin/database/con_db.php';
            
            $vaicajums = $savienojums->prepare("INSERT INTO sparkly_maksajumi(maks_reference, epasts) VALUES (?, ?)"); 
            $vaicajums->bind_param("ss", $transactionID, $customer_email);



            if ($stmt->execute()) {
                $statusMsg = "<h2>Maksājums veiksmīgi apstrādāts!</h2>
                <p>Lai turpmāk iegūt PRO privilēģijas, veicot jaunu pieteikumu, izmantojiet šo e-pastu: <b>$customer_email</b></p>
                <p>Maksājuma reference: <b>$transactionID</b></p>";
            } else {
                $statusMsg = "Neizdevās saglabāt maksājuma informāciju datubāzē: " . $stmt->error;
            }
        } else {
            $statusMsg = "Problēmas ar maksājuma apstrādi!";
        }
    } catch (Exception $e) {
        $statusMsg = "Nav iespējams iegūt maksājuma informāciju: " . $e->getMessage();
    }

    $_SESSION['pazinojums'] = $statusMsg;
}

// Redirect to the previous page
header("location: ../");

?>