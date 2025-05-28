<?php
session_start();

if (!isset($_SESSION['pending_order'])) {
    $_SESSION['pazinojums'] = "Nav atrasts gaidošs pasūtījums";
    header("Location: ../pasutisana.php");
    exit();
}

require_once '../../../stripe-php-master/init.php';
require_once 'config.php';

try {
    $order = $_SESSION['pending_order'];
    
    // Create line items for Stripe
    $line_items = [];
    foreach ($order['cart_items'] as $item) {
        $line_items[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item['nosaukums'],
                ],
                'unit_amount' => $item['cena'] * 100, // Convert to cents
            ],
            'quantity' => $item['daudzums'],
        ];
    }
    
    // Add delivery fee if applicable
    if ($order['piegades_veids'] == 'Kurjers') {
        // You can add delivery fee here if needed
        // $line_items[] = [
        //     'price_data' => [
        //         'currency' => 'eur',
        //         'product_data' => [
        //             'name' => 'Piegāde',
        //         ],
        //         'unit_amount' => 500, // 5 EUR delivery fee
        //     ],
        //     'quantity' => 1,
        // ];
    }
    
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'https://kristovskis.lv/3pt2/sinicina/eksamens/payment/success.php?session_id={CHECKOUT_SESSION_ID}',
        "cancel_url" => "https://kristovskis.lv/3pt2/sinicina/eksamens/",
        'customer_email' => $order['epasts'],
        'billing_address_collection' => 'required',
        'metadata' => [
            'order_id' => 'pending_' . time(),
            'delivery_method' => $order['piegades_veids']
        ]
    ]);
    
    header("Location: " . $checkout_session->url);
    exit();
    
} catch (Exception $e) {
    error_log("Stripe checkout error: " . $e->getMessage());
    $_SESSION['pazinojums'] = "Kļūda veidojot maksājumu: " . $e->getMessage();
    header("Location: ../pasutisana.php");
    exit();
}
?>