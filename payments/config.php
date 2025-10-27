<?php 
    
    require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require 'payments/config.php';


$stripe_secret_key = getenv('STRIPE_SECRET_KEY'); //Noņēmu atsēlgu

    \Stripe\Stripe::setApiKey($stripe_secret_key);


?>  