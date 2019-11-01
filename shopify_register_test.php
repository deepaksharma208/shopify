<?php
include 'index.php';

$result       = makeRequest('125c821421f2b0c7bc8a8d3a23a75858', 'shiny-water.myshopify.com');
// print_r($result);die;
$shop = 'shiny-water.myshopify.com';
$access_token = '125c821421f2b0c7bc8a8d3a23a75858';
$name         = explode(" ", $result['shop']['shop_owner'],2);
$fname        = $name[0];
$lname        = $name[1];
$phone		  = $result['shop']['phone'];
$email        = $result['shop']['email'];
$address 	  = array(
        	       $result['shop']['address1'],
                   substr($result['shop']['address2'], 0, 255),
                   $result['shop']['city'],
                   $result['shop']['country_name'],
                   $result['shop']['province'],
                   $result['shop']['zip']);

// print_r($email.", ".$fname.", ".$lname.", ".$shop.", ".$access_token.", ".$phone."\n" );;

// send_ga_event($email);

check_user($email, $fname, $lname, $shop, $access_token,$phone, $address);

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
  
  print_r($response);
    curl_close($curl);

    return json_decode($response, 1);
}