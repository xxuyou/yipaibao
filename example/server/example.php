<?php
require './lib/Xxuyou.class.php';
define('DS', DIRECTORY_SEPARATOR);

// params
$uri = 'https://xxuyou.com/rest/'; // WSS 服务地址
$authKey = 'Kmr5G7iCKwqGdJrVtpPmz5ez0cK4pVllFKfCe01q'; // app auth key
$appName = 'demo_whiteboard'; // app
$poolName= 'myTestPoolNew1'; // pool
$url = $uri.$authKey.DS.$appName.DS.$poolName; // combine to url string

// PUT
$method = 'PUT';
$payload = array(
	'event' => 'auction_close',
	'action_id' => 341,
	'result' => 1,
	'complete_price' => 1450000,
	'customer_id' => 87,
	'order_id' => 629
);
$result = postHelper($method, $url, $payload);
var_dump($result);

// POST
$method = 'POST';
$payload = array(
	'event' => 'auction_price',
	'new_price' => 1450000,
	'action_id' => 341,
	'customer_id' => 87
);
$result = postHelper($method, $url, $payload);
var_dump($result);

// DELETE
$method = 'DELETE';
$result = postHelper($method, $url);
var_dump($result);

/**
 * postHelper
 * 
 * @param string $method [GET|PUT|POST|DELETE]
 * @param string $url
 * @param array $payload
 */
function postHelper($method, $url, array $payload=array()) {
	if (preg_match('/(GET|PUT|POST|DELETE)/', $method) !== 1) return false;
	switch ($method) {
		case 'GET':
			$resBody = Xxuyou::get($url);
			break;
		case 'DELETE':
			$resBody = Xxuyou::delete($url);
			break;
		case 'PUT':
			$resBody = Xxuyou::put($url, $payload);
			break;
		case 'POST':
			$resBody = Xxuyou::post($url, $payload);
			break;
	};
	$resHeader = $resBody['info'];
	$resBody = json_decode($resBody['result'], true);
	if ($resBody === false) {
		$resKeyName = true;
	} else {
		if ($resBody['err'] == 0 && array_key_exists('data', $resBody)) {
			$resKeyName = $resBody['data']['key'];
		};
	};
	return $resHeader['http_code'] == 200 ? $resKeyName : false;
};