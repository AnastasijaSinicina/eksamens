<?php
session_start();

// Handle payment cancellation
if (isset($_GET['payment']) && $_GET['payment'] == 'cancelled') {
    $_SESSION['pazinojums'] = "Maksājums tika atcelts. Jūs varat mēģināt vēlreiz.";
    header("Location: ../pasutisana.php");
    exit();
}

if (!empty($_GET['session_id']) && isset($_SESSION['pending_order'])) {
    $session_id = $_GET['session_id'];
    $order = $_SESSION['pending_order'];

    require_once '../../../stripe-php-master/init.php';
    require_once 'config.php';
    require_once '../admin/db/con_db.php';

    try {
        $checkout_session = \Stripe\Checkout\Session::retrieve($session_id);
        $customer_email = $checkout_session->customer_details->email;

        $paymentIntent = \Stripe\PaymentIntent::retrieve($checkout_session->payment_intent);

        if ($paymentIntent->status == 'succeeded') {
            $transactionID = $paymentIntent->id;

            // Insert the payment record
            $payment_query = $savienojums->prepare("INSERT INTO sparkly_maksajumi(maks_reference, epasts) VALUES (?, ?)");
            $payment_query->bind_param("ss", $transactionID, $customer_email);
            $payment_query->execute();

            // Create the order
            $status = 'Iesniegts';
            $insert_order = $savienojums->prepare("INSERT INTO sparkly_pasutijumi (lietotajs_id, kopeja_cena, apmaksas_veids, piegades_veids, produktu_skaits, vards, uzvards, epasts, talrunis, pilseta, adrese, pasta_indeks, statuss, stripe_payment_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $insert_order->bind_param("idsisssssssss", 
                $order['lietotajs_id'],
                $order['kopeja_cena'],
                $order['apmaksas_veids'],
                $order['piegades_veids'],
                $order['produktu_skaits'],
                $order['vards'],
                $order['uzvards'],
                $order['epasts'],
                $order['telefons'],
                $order['pilseta'],
                $order['adrese'],
                $order['pasta_indekss'],
                $status,
                $transactionID
            );

            if ($insert_order->execute()) {
                $pasutijums_id = $savienojums->insert_id;

                // Insert order items
                foreach ($order['cart_items'] as $item) {
                    $insert_items = $savienojums->prepare("INSERT INTO sparkly_pasutijuma_vienumi 
                                    (pasutijuma_id, produkta_id, daudzums_no_groza, cena) 
                                    VALUES (?, ?, ?, ?)");
                    
                    $insert_items->bind_param("iiid", 
                        $pasutijums_id, 
                        $item['bumba_id'], 
                        $item['daudzums'], 
                        $item['cena']
                    );
                    $insert_items->execute();
                }

                // Update cart status
                $username = $_SESSION['lietotajvardsSIN'];
                $update_cart = $savienojums->prepare("UPDATE grozs_sparkly SET statuss = 'pasūtīts' WHERE lietotajvards = ? AND statuss = 'aktīvs'");
                $update_cart->bind_param("s", $username);
                $update_cart->execute();

                // Clear pending order from session
                unset($_SESSION['pending_order']);

                $_SESSION['pazinojums'] = "Pasūtījums veiksmīgi apmaksāts un noformēts!";
                header("Location: ../pasutijums_apstiprinats.php?id=" . $pasutijums_id);
                exit();
            } else {
                error_log("Error inserting order: " . $insert_order->error);
                $_SESSION['pazinojums'] = "Maksājums veiksmīgs, bet radusies kļūda saglabājot pasūtījumu. Lūdzu sazinieties ar atbalsta dienestu.";
                header("Location: ../");
                exit();
            }
        } else {
            $_SESSION['pazinojums'] = "Problēmas ar maksājuma apstrādi!";
            header("Location: ../pasutisana.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Stripe success processing error: " . $e->getMessage());
        $_SESSION['pazinojums'] = "Nav iespējams iegūt maksājuma informāciju: " . $e->getMessage();
        header("Location: ../pasutisana.php");
        exit();
    }
} else {
    $_SESSION['pazinojums'] = "Nekorekti maksājuma dati";
    header("Location: ../pasutisana.php");
    exit();
}
?>