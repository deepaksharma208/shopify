<?php
require 'vendor/autoload.php';

use Carbon\Carbon;
use GuzzleHttp\Client;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$api_key = 'kjhkjjhjhoi909xixeuox0990390';
$secret_key = '983209x90x09d909didi90i0990diddi';

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
