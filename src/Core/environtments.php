<?php
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__.'/../..');
$dotenv->load();
    
$dotenv->required('APP_MODE')->notEmpty()->allowedValues(['production', 'development']);

$dotenv->required('BASE_PATH')->notEmpty();
$dotenv->required('PUBLIC_URL')->notEmpty();
$dotenv->required('SECURITY_TOKEN')->notEmpty();