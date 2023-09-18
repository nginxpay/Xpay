<?php
require_once 'XXpay/Pay.php';
require_once('XXpay/Xpay.php');

use XXpay\Xpay;

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(0);
$merchant_id = '799000018';
$key = '847460CBEBA0AF602E5DDFC87E33ECE7';
$apiHost = 'https://nginxpay.com';
$xpay = new Xpay($merchant_id, $key, '', $apiHost);
$orderNo=rand(1,10000).time();
$data=$xpay->pay(200.00,$orderNo,'','Xpay','https://nginxpay.com','product');
var_dump($data);