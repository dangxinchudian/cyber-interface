<?php

/*用于派发至监测点*/

function httpHeader($url, $port = 80){

	$curl = curl_init();
	$timeOut = 5;
	$userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5';

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_PORT, $port);
	curl_setopt($curl, CURLOPT_TIMEOUT, $timeOut);
	curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($curl, CURLOPT_NOBODY, true);

	$a = curl_exec($curl);
	//echo $a;

	$curlinfo = curl_getinfo($curl);
	//print_r($curlinfo);

	$result = array(
		'http_code' => $curlinfo['http_code'],
		'total_time' => $curlinfo['total_time'],
		'starttransfer_time' => $curlinfo['starttransfer_time'],
		'pretransfer_time' => $curlinfo['pretransfer_time'],
		'namelookup_time' => $curlinfo['namelookup_time'],
		'connect_time' => $curlinfo['connect_time'],
		'redirect_time' => $curlinfo['redirect_time'],
		'time' => date('Y-m-d H:i:s')
	);
	return $result;
}

$result = httpHeader($_GET['url'], $_GET['port']);

echo json_encode($result);

?>
