<?php
require 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
$app_url = 'http://erp.kraftly.com/shopify';

//$db = new Mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASS'), getenv('MYSQL_DB')); 

if (!empty($_GET)) {

    $store = $_GET['shop'];
    $api_key = 'fc14b70f3ba850c4411e17ab2a8833d4';//getenv('SHOPIFY_APIKEY');
    $scopes = "read_orders,write_orders,read_fulfillments,write_fulfillments,read_products,write_products";//getenv('SHOPIFY_SCOPES');
    $redirect_uri = urlencode($app_url."/auth.php");//urlencode(getenv('SHOPIFY_REDIRECT_URI'));
	$url = "https://{$store}/admin/oauth/authorize?client_id={$api_key}&scope={$scopes}&redirect_uri={$redirect_uri}";
	header("Location: {$url}");
}
?>
