<?php

// Live
define('DOMAIN', 'https://apiv2.shiprocket.in/v1/external/shopify');
define('APP_URL', 'http://erp.kraftly.com/shopify/Shopify_app.php?message=');
define('ACCESS_TOKEN', 'GMjQac4K5XpA5ogYsroxas3K2Y7RuAMEsn0NumDxBlbbN64uhgOYn8SH98upr3SGYeJbKUhSwINCN5B2WTtFrLFsVXfwQQG9JoZLjIpsRNzfpiRQDCO5YoJFdWyDSoNtWATx6LfvESmq6VoqQnn0BxSdmCo2URF7VVDAChuDqhdzy9Yps0ji7yv1grNvWkq0OhmcBUe9lkkBf2Ap75wtrfAwZRxxbezLGRqfhhOTlOUvCZ87eaaFvfECS5hI3NU');
define('API_KEY', 'fc14b70f3ba850c4411e17ab2a8833d4');
define('SHARED_SECRET', 'd550e77211c3039a1e63eeae055cf022');


// Dev
//define('DOMAIN', 'http://krmct001.kartrocket.com/v1/external/shopify');
//define('APP_URL', 'https://erp.kraftly.com/sf/Shopify_app.php?message=');
//define('ACCESS_TOKEN', '6L5HJxUGGSsWGJgWtNrbSJrsvqSk8xS2dgImAqCo9Pr2jVsG19hMgZuwkMNSMW2VO2rmIGBy5i7GUx6zWeU07SfGI9Wdz1RpLrnUGGZDoKCaVglO9V1nAT60QPzCI17LAhJ9fTvwEvy46OIbEoB2gctYfzCG06v1rjnmspSg99fMfSCC3trxrdKo5uCjRxRbz9s8wQXDqcIc6eiMOI2TdRNOR08YisCnkPUWZqOSMf3yAh82zR0j3g81c0x1Bug');
//define('API_KEY', 'eee124821e6f72e520f9bb63eac1e6a5');
//define('SHARED_SECRET', '69c1bfa568489e8202490e017dc041ed');


function check_user($email, $fName, $last_name, $company, $token, $phone, $address)
{
	// todo: Remove below line before going live
	// $email = "deepak.sharma@kartrocket.com1608957491";//$email . rand();
	// print_r("\n\n".$email.", ".$fName.", ".$last_name.", ".$company.", ".$token.", ".$phone);
	write($email.",". $fName.",". $last_name.",". $company.",". $token.",". $phone.",". print_r($address,true));
	$login_response = request(DOMAIN . "/auth/login", ['email' => $email, 'auth' =>
														json_encode(
																	[
																		'api_key'	=>	API_KEY,
																		'api_password' 	=> $token,
																		'shared_secret' => SHARED_SECRET,
																		'store_url' 	=> $company
																	]
																)
													]);
	// var_dump($login_response);die;

	$status = $login_response['status'];
	if ($status == 0) {
		$register_result = request(DOMAIN . "/auth/register", ['email'        		=> $email,
															   'first_name'   		=> $fName,
															   'last_name'    		=> $last_name,
															   'mobile'		  		=> $phone,
															   'company_name' 		=> preg_replace("/.myshopify.com$/s", '', $company),
															   'billing_address'	=> $address[0],
															   'billing_address_2'	=> $address[1],
															   'billing_city'		=> $address[2],
															   'billing_country'	=> $address[3],
															   'billing_pin_code'	=> $address[5],
															   'billing_state'		=> $address[4],
															   'billing_phone'		=> $phone,
															   'utm_source'         => 'shopify',
															   'utm_medium'         => 'New',
															   'utm_campaign'       => 'shopify-marketplace',
															   'utm_content'        => 'signup',
															   'utm_term'           => 'marketplace-signup'
															]);
		// var_dump($register_result);die;
		if ($register_result['status'] == 0) {
			header("Location:" . APP_URL . urlencode("Error in register call: " . print_r($register_result, 1)));
		} else if ($register_result['status'] == 1) {
			$itegrate_result = integrateChannel($register_result['merchant_id'], $token, $company, $register_result['redirect_url'], 1);
		}
		elseif ($register_result['status'] == 3) {
			header("Location: https://app.shiprocket.in/register?log_id=".$register_result['log_id']);
		}
	} else if ($status == 1 && !$login_response['shopify_integrated']) {
		$itegrate_result = integrateChannel($login_response['merchant_id'], $token, $company, $login_response['redirect_url']);
	} else if ($status == 1 && $login_response['shopify_integrated']) {
		header('Location:' . $login_response['redirect_url']);// . urlencode('Channel Already Integrated') .'&redirect_url=' . urlencode($login_response['redirect_url']));
	}

	return $login_response;
}

// Make a channel integration call
function integrateChannel($merchant_id, $merchant_token, $company, $redirect_url, $user_register = 0)
{
	$itegrate_result = request(DOMAIN . "/channel", "{\"name\":\"Shopify\",\"merchant_id\":\"{$merchant_id}\",\"orders_sync\":true,\"inventory_sync\":false,\"auth\":{\"api_key\":\"" . API_KEY . "\",\"shared_secret\":\"" . SHARED_SECRET . "\",\"api_password\":\"{$merchant_token}\",\"store_url\":\"https:\\/\\/{$company}\"}}", 1);
	var_dump($itegrate_result);
	if ($itegrate_result['status'] == 1) {
		header('Location:' . $redirect_url);//APP_URL . urlencode('Channel Integrated' . ($user_register ? '' : ' after registering the user')) . '&redirect_url='.urlencode($redirect_url));
	} else if ($itegrate_result['status'] == 0) {
		header('Location:' . $redirect_url );//. urlencode('Error in integration call' . ($user_register ? ' after register' : ' for existing user')) . '&redirect_url=' . urlencode($redirect_url));
	}
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
	else
		write($response);
	print_r(json_decode($response, 1));

	return json_decode($response, 1);
}

function write($data){
	$file = 'Shopify_error_log.txt';
	$fh = fopen($file, 'a');
	fwrite($fh, $data.'\n');
	fclose($fh);
}