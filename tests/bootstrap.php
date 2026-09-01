<?php

require __DIR__.'/../vendor/autoload.php';

$cle = 'base64:'.base64_encode(random_bytes(32));

putenv("APP_KEY=$cle");
$_ENV['APP_KEY'] = $cle;
$_SERVER['APP_KEY'] = $cle;
