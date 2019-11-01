<?php
require 'vendor/autoload.php';

include 'index.php';

use Carbon\Carbon;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
// Live
$api_key    = 'fc14b70f3ba850c4411e17ab2a8833d4';
$secret_key = 'd550e77211c3039a1e63eeae055cf022';

//Dev
// $api_key    = 'eee124821e6f72e520f9bb63eac1e6a5';
// $secret_key = '69c1bfa568489e8202490e017dc041ed';

$query = $_GET;
$code  = $query['code'];
$shop  = $query['shop'];

// Check validity of the request
if (!isset($query['code'], $query['hmac'], $query['shop'], $query['timestamp'])) {
    echo "Invalid access";
    die;
}

$one_minute_ago = Carbon::now()
                        ->subSeconds(60)->timestamp;
if ($query['timestamp'] < $one_minute_ago) {
    print_r("Time less than 1 minute");
    exit;
}

$hmac  = $query['hmac'];
$store = $query['shop'];
unset($query['hmac']);

foreach ($query as $key => $val) {
    $params[] = "$key=$val";
}

asort($params);
$params = implode('&', $params);

$calculated_hmac = hash_hmac('sha256', $params, $secret_key);

if ($hmac == $calculated_hmac) {
    $access_token = get_token($code, $shop, $api_key, $secret_key);

    setWebhook($access_token, $shop);

    $result       = makeRequest($access_token, $shop);
    $name         = explode(" ", $result['shop']['shop_owner'],2);
    $fname        = $name[0];
    $lname        = $name[1];
    $phone        = $result['shop']['phone'];
    $email        = $result['shop']['email'];
    $address      = array(
                       $result['shop']['address1'],
                       substr($result['shop']['address2'], 0, 255),
                       $result['shop']['city'],
                       $result['shop']['country_name'],
                       $result['shop']['province'],
                       $result['shop']['zip']);

    write_1 ( $email.", ".$fname.", ".$lname.", ".$shop.", ".$access_token.", ".$phone."\n" );

    send_ga_event($email);

    check_user($email, $fname, $lname, $shop, $access_token,$phone, $address);
} 
else {
    console . log('hmac not matched');
}

function get_token($code, $shop)
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://{$shop}/admin/oauth/access_token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => "client_id=fc14b70f3ba850c4411e17ab2a8833d4&client_secret=d550e77211c3039a1e63eeae055cf022&code=" . $code,
        CURLOPT_HTTPHEADER     => [
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded"
        ]
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($response, 1);
    // write($shop."->".$result['access_token']);
    return $result['access_token'];
}

function send_ga_event($email){
    $url = "http://www.google-analytics.com/collect?v=1&t=event&tid=UA-86119117-2&cid=922f3f47-39e9-41cf-a19b-699bb225e968&ec=Shopify&ea=GetLinkClick&el={$email}";
    $len = strlen($url);
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache"
          ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if($response)
        print_r($response);
    else
        print_r($err);
}

function setWebhook($token, $store){
    // echo $token."\t".$hook."\n";
    $result = json_decode(webhook($token, $store, "GET"), 1);
    // write_1(json_encode($result));
    if($result['errors'])
    {    
        write_1(json_encode($result));
        return true;
    }
    else if(count($result['webhooks']) > 0)
    {
        write_1("Webhook already exists");
        return true;
    }

    $data = json_encode([
                            "webhook"   => 
                            [ 
                                "topic"     => 'app/uninstalled',
                                "address"   => 'https://erp.kraftly.com/shopify/webhook.php',
                                "format"    => 'json'
                            ]
                        ]
                    );
    $result = json_decode(webhook($token, $store, "POST", $data), 1);
    if($result['errors'])
        write_1(json_encode($result));
    else
        write_1("Webhook created : ".$result['webhook']['id']);

}

function webhook($token, $store, $request, $data=''){
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://{$store}/admin/webhooks.json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => $request,
        CURLOPT_POSTFIELDS      => $data,
        CURLOPT_HTTPHEADER     => [
            "content-type: application/json",
            "x-shopify-access-token: " . $token
        ],
    ]);

    $response = curl_exec($curl);
    $err      = curl_error($curl);
    
    if($response)
        return $response;
    else
        return $err;
}

function makeRequest($access_token, $store)
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://{$store}/admin/shop.json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "GET",
        CURLOPT_HTTPHEADER     => [
            "content-type: application/x-www-form-urlencoded",
            "x-shopify-access-token: " . $access_token
        ],
    ]);

    $response = curl_exec($curl);
    $err      = curl_error($curl);
    if($err)
        return $err;
    
    print_r($response);
    curl_close($curl);

    return json_decode($response, 1);
}

function write_1($data){
    $fh = fopen("access_token_log.txt", 'a');
    fwrite($fh, $data);
    fwrite($fh, "\n");
    fclose($fh);
}
?>