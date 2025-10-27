<?php
session_start();
require_once "../stripe-php-master/init.php";
require_once "../con_db.php";
require_once "config.php"; // Stripe API key

if (!isset($_SESSION['username']) || !isset($_GET['offer_id'])) {
    header("Location: ../login/login.php");
    exit;
}

$offer_id = intval($_GET['offer_id']);
$username = $_SESSION['username'];

// Fetch offer info from DB
$stmt = $savienojums->prepare("SELECT name, price FROM net_offers WHERE id=?");
$stmt->bind_param("i", $offer_id);
$stmt->execute();
$offer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$offer) {
    $_SESSION["pazinojums"] = "Nepareizs pakalpojums.";
    header("Location: ../index.php#pakalpojumi");
    exit;
}

// Stripe Checkout Session
$session = \Stripe\Checkout\Session::create([
    "payment_method_types" => ["card"],
    "line_items" => [[
        "price_data" => [
            "currency" => "eur",
            "unit_amount" => $offer['price'] * 100, // in cents
            "product_data" => ["name" => $offer['name']]
        ],
        "quantity" => 1
    ]],
    "mode" => "payment",
    "success_url" => "https://kristovskis.lv/4pt/kliecis/Net-Pakalpojumi/payments/success_offer.php?offer_id={$offer_id}&session_id={CHECKOUT_SESSION_ID}",
    "cancel_url" => "https://kristovskis.lv/4pt/kliecis/Net-Pakalpojumi/#pakalpojumi",
    "locale" => "lv"
]);

header("Location: " . $session->url);
exit;
