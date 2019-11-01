<?php

// include 'index.php';
//live
define('DOMAIN', 'https://apiv2.shiprocket.in/v1/external/shopify');
define('ACCESS_TOKEN', 'GMjQac4K5XpA5ogYsroxas3K2Y7RuAMEsn0NumDxBlbbN64uhgOYn8SH98upr3SGYeJbKUhSwINCN5B2WTtFrLFsVXfwQQG9JoZLjIpsRNzfpiRQDCO5YoJFdWyDSoNtWATx6LfvESmq6VoqQnn0BxSdmCo2URF7VVDAChuDqhdzy9Yps0ji7yv1grNvWkq0OhmcBUe9lkkBf2Ap75wtrfAwZRxxbezLGRqfhhOTlOUvCZ87eaaFvfECS5hI3NU');
define('API_KEY', 'fc14b70f3ba850c4411e17ab2a8833d4');
define('SHARED_SECRET', 'd550e77211c3039a1e63eeae055cf022');

// Dev
// define('DOMAIN', 'https://krmct000.kartrocket.com/v1/external/shopify');
//UAT
// define('DOMAIN', 'https://krmct.uat.api.kartrocket.com/v1/external/shopify');
// define('ACCESS_TOKEN', '6L5HJxUGGSsWGJgWtNrbSJrsvqSk8xS2dgImAqCo9Pr2jVsG19hMgZuwkMNSMW2VO2rmIGBy5i7GUx6zWeU07SfGI9Wdz1RpLrnUGGZDoKCaVglO9V1nAT60QPzCI17LAhJ9fTvwEvy46OIbEoB2gctYfzCG06v1rjnmspSg99fMfSCC3trxrdKo5uCjRxRbz9s8wQXDqcIc6eiMOI2TdRNOR08YisCnkPUWZqOSMf3yAh82zR0j3g81c0x1Bug');
// define('API_KEY', 'eee124821e6f72e520f9bb63eac1e6a5');
// define('SHARED_SECRET', '69c1bfa568489e8202490e017dc041ed');

$event 	= 	$_SERVER['HTTP_X_SHOPIFY_TOPIC'];
$hmac 	=  	$_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$domain =  	$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];

// write("Call To webhook");
write($event);
write($hmac);
write($domain);
// write("\n");
// write($_POST);
// foreach ($_POST as $value) {
// 	write($value);
// }
$data = file_get_contents("php://input");
write($data);
verifyWebhook($hmac, $data);


function verifyWebhook($hmac, $data){
	$calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHARED_SECRET, true));
	write($calculated_hmac .":". $hmac);//die;
	if ($calculated_hmac == $hmac){
		$data 	= json_decode($data, 1);
		$auth 	= json_encode(['api_key' => API_KEY]);
		$result = json_decode(request(DOMAIN.'/auth/deactivate', ['email' => $data['email'], 'auth' => $auth]));
		if($result['status'] == 1)
			write("Channel deactivate success");
		else
			write("Channel deactivate error");
	}
	else
		write("HMAC not matched");
}

function request($path, $data = [], $isJson = 0)
{
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL            => $path,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST  => "POST",
		CURLOPT_POSTFIELDS     => $data,
		CURLOPT_HTTPHEADER     => [
			"authorization: ACCESS_TOKEN:" . ACCESS_TOKEN]
		]
	);

	if ($isJson) {
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'authorization: ACCESS_TOKEN:' . ACCESS_TOKEN,
			'Content-Type:application/json'
		]);
	}

	$response = curl_exec($curl);

	$err      = curl_error($curl);
	curl_close($curl);

	if ($err) {
		echo "cURL Error #:" . $err;
		write($err);
	}
	// print_r(json_decode($response, 1));
	write($response);
	return json_decode($response, 1);
}

function write($data){
	$fh = fopen('webhook_log.txt', 'a');
	fwrite($fh, $data."\n");
	fclose($fh);
}