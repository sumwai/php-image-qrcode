<?php

error_reporting(E_ALL);
require_once "./vendor/autoload.php";

is_dir("images") or mkdir("images");

$qrcode = QRCode::create("Hello World");
[$width, $height] = $qrcode->size();
var_dump($width, $height);
$qrcode->save("./images/hello.jpg");
