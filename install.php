<?php
require 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
$app_url = 'http://erp.kraftly.com/sf';

//$db = new Mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASS'), getenv('MYSQL_DB')); 
var_dump($_GET);
//die();
if (!empty($_GET)) {

    $store = $_GET['shop'];
	console.log($store);
    $api_key = 'eee124821e6f72e520f9bb63eac1e6a5';//getenv('SHOPIFY_APIKEY');
    $scopes = "read_orders,read_products,write_products";//getenv('SHOPIFY_SCOPES');
    $redirect_uri = urlencode("http://erp.kraftly.com/sf/auth.php");//urlencode(getenv('SHOPIFY_REDIRECT_URI'));
	$url = "https://".$store."/admin/oauth/authorize?client_id=".$api_key."&scope=".$scopes."&redirect_uri=".$redirect_uri;
	header("Location: {$url}");
}
?>