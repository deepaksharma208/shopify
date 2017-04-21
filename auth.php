<?php
require 'vendor/autoload.php';

use Carbon\Carbon;
use GuzzleHttp\Client;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$api_key = 'eee124821e6f72e520f9bb63eac1e6a5';
$secret_key = '69c1bfa568489e8202490e017dc041ed';

$query = $_GET; 
var_dump($query);
if (!isset($query['code'], $query['hmac'], $query['shop'], $query['timestamp'])) {
    exit; //or redirect to an error page
}

$one_minute_ago = Carbon::now()->subSeconds(60)->timestamp;
var_dump($query['timestamp']);
if ($query['timestamp'] < $one_minute_ago) {
    exit; //or redirect to an error page
}

$hmac = $query['hmac'];
$store = $query['shop'];
unset($query['hmac']);

foreach ($query as $key => $val) {
    $params[] = "$key=$val";
} 

asort($params);
$params = implode('&', $params);

$calculated_hmac = hash_hmac('sha256', $params, $secret_key);
//var_dump($hmac);
//var_dump("\n".$calculated_hmac);
if($hmac == $calculated_hmac){
    $url = "https://app.shiprocket.in/register";
	header("Location: {$url}");
}
else {
	console.log('hmac not matched');
}
?>