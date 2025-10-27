<?php
session_start();
require_once "../stripe-php-master/init.php";
require_once "config.php";

// Validate plan and price
if (!isset($_GET['plan'], $_GET['price'])) {
    die("Nav norādīts plāns vai cena!");
}

$plan = trim($_GET['plan']);
$plan = html_entity_decode($plan, ENT_QUOTES, 'UTF-8'); // decode any HTML entities
$price = (int)$_GET['price']; // cents

try {
$plan = trim($_GET['plan']); // e.g., "Normālais"
$checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
"success_url" => "https://kristovskis.lv/4pt/kliecis/Net-Pakalpojumi/payments/success.php?session_id={CHECKOUT_SESSION_ID}",
"cancel_url" => "https://kristovskis.lv/4pt/kliecis/Net-Pakalpojumi/",

    "locale" => "lv",
    "line_items" => [[
        "quantity" => 1,
        "price_data" => [
            "currency" => "eur",
            "unit_amount" => $price,
            "product_data" => [
                "name" => $plan . " plāns" // display name for Stripe
            ]
        ]
    ]],
    "metadata" => [
        "plan_name" => $plan // store "Normālais" only
    ]
]);


    header("Location: " . $checkout_session->url);
    exit;

} catch (\Exception $e) {
    die("Neizdevās izveidot maksājuma sesiju: " . $e->getMessage());
}
