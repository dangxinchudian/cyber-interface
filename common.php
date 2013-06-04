<?php

function random($type = 'str', $length){
	$chars = array('num' => '0123456789', 'str' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890');
	$result = '';
	$chars_length = strlen($chars[$type]) - 1;
	for ($i = 0; $i < $length; $i++){
		if($type == 'num' && $i == 0) $result .= $chars[$type]{rand(1, $chars_length)};
		else $result .= $chars[$type]{rand(0, $chars_length)};
	}
	return $result;
}

function dns_a($domain){
	if(is_windows()){
		$url = "http://opencdn.sinaapp.com/dns.php?domain={$domain}&type=A";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$content = curl_exec($ch);
		curl_close($ch);
		return json_decode($content, true);
	}else return dns_get_record($domain, DNS_A);
}

function dns_cname($domain){
	if(is_windows()){
		$url = "http://opencdn.sinaapp.com/dns.php?domain={$domain}&type=CNAME";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$content = curl_exec($ch);
		curl_close($ch);
		return json_decode($content, true);
	}else return dns_get_record($domain, DNS_CNAME);
}


function is_windows(){
	if(PATH_SEPARATOR ==':') return false;
	return true;
}

function str_compress($result){
	return preg_replace('/[^\/:0-9A-Za-z\\\\]/','', $result);
}

function str2utf8($keyword){
	return mb_convert_encoding($keyword, 'UTF-8', 'GBK,GB2312');
}

function jencode($value){
	if(empty($value)) return false;
	return base64_encode(gzcompress(json_encode($value)));
}

function jdecode($value){
	if(empty($value)) return false;
	return json_decode(gzuncompress(base64_decode($value)), true);
}

function errorHeader($code){

	$hc = array();
	# Informational 1xx
	$hc['0'] = '无法连接服务器'; 
	$hc['100'] = 'Continue'; 
	$hc['101'] = 'Switching Protocols';
	# Successful 2xx 
	$hc['200'] = 'OK';
	$hc['201'] = 'Created';
	$hc['202'] = 'Accepted';
	$hc['203'] = 'Non-Authoritative Information';
	$hc['204'] = 'No Content';
	$hc['205'] = 'Reset Content';
	$hc['206'] = 'Partial Content';
	# Redirection 3xx 
	$hc['300'] = 'Multiple Choices';
	$hc['301'] = 'Moved Permanently';
	$hc['302'] = 'Moved Temporarily';
	$hc['303'] = 'See Other';
	$hc['304'] = 'Not Modified';
	$hc['305'] = 'Use Proxy';
	$hc['306'] = '(Unused)';
	$hc['307'] = 'Temporary Redirect';
	# Client Error 4xx 
	$hc['400'] = 'Bad Request';
	$hc['401'] = 'Unauthorized';
	$hc['402'] = 'Payment Required';
	$hc['403'] = 'Forbidden';
	$hc['404'] = 'Not Found';
	$hc['405'] = 'Method Not Allowed';
	$hc['406'] = 'Not Acceptable';
	$hc['407'] = 'Proxy Authentication Required';
	$hc['408'] = 'Request Timeout';
	$hc['409'] = 'Conflict';
	$hc['410'] = 'Gone';
	$hc['411'] = 'Length Required';
	$hc['412'] = 'Precondition Failed';
	$hc['413'] = 'Request Entity Too Large';
	$hc['414'] = 'Request-URI Too Long';
	$hc['415'] = 'Unsupported Media Type';
	$hc['416'] = 'Requested Range Not Satisfiable';
	$hc['417'] = 'Expectation Failed';
	# Server Error 5xx
	$hc['500'] = 'Internal Server Error';
	$hc['501'] = 'Not Implemented';
	$hc['502'] = 'Bad Gateway';
	$hc['503'] = 'Service Unavailable';
	$hc['504'] = 'Gateway Timeout';
	$hc['505'] = 'HTTP Version Not Supported';

	if(isset($hc[$code])) return $hc[$code];
	return 'Unkown Error';
}

function httpHeader($url, $port = 80){

	$curl = curl_init();
	$timeOut = 30;
	$userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5';

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_PORT, $port);
	curl_setopt($curl, CURLOPT_TIMEOUT, $timeOut);
	curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
	//curl_setopt($curl, CURLOPT_NOBODY, true);

	curl_exec($curl);

	$curlinfo = curl_getinfo($curl);

	$result = array(
		'code' => $curlinfo['http_code'],
		'status' => errorHeader($curlinfo['http_code']),
		'time' => $curlinfo['total_time']
	);
	return $result;
}
/*
function send_mail($mailAddress, $title, $content){

	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPDebug  = 1;
	//$mail->Host       = 'smtp.exmail.qq.com';
	$mail->Host       = 'smtp.gmail.com';
	$mail->Port       = 25;
	$mail->SMTPAuth   = true;
	//$mail->Username   = '2259523843@qq.com';
	$mail->Username   = 'support@secon.me';
	//$mail->Password   = 'zje06160616';
	$mail->Password   = 'secontech110';
	//$mail->SetFrom('2259523843@qq.com', 'SECON监控中心');
	$mail->SetFrom('support@secon.me', 'SECON用户');
	$mail->AddAddress($mailAddress, 'SECON支持团队');
	$mail->Subject = $title;
	$mail->MsgHTML($content);
	$mail->AltBody = 'This is a plain-text message body';
	if(!$mail->Send()) return false;
	else return true;

}*/

function send_mail($mailAddress, $title, $content){
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPDebug  = 0;
	$mail->Host       = 'smtp.gmail.com';
	$mail->Port       = 587;
	$mail->SMTPSecure = 'tls';
	$mail->SMTPAuth   = true;
	$mail->Username   = 'support@secon.me';
	$mail->Password   = 'secontech110';
	$mail->SetFrom('support@secon.me', 'SECON支持团队');
	$mail->AddAddress($mailAddress, 'SECON用户');
	$mail->Subject = $title;
	$mail->MsgHTML($content);
	$mail->AltBody = 'This is a plain-text message body';
	if(!$mail->Send()) return false;
	else return true;
}

function send_sms($mobile, $sms){
	$sms = urlencode($sms);
	$url = "http://utf8.sms.webchinese.cn/?Uid=zje2008&Key=f864d6a69e13eb59906d&smsMob={$mobile}&smsText={$sms}";
	httpHeader($url);
}

function rolling_curl($urls, $callback, $body = true){
    $queue = curl_multi_init();
    $map = array();
    $count = 0;
 
    foreach ($urls as $url) {
        $ch = curl_init();
 
        curl_setopt($ch, CURLOPT_URL, $url['url']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        if($body === false) curl_setopt($ch, CURLOPT_NOBODY, 1);
        if(isset($url['port']))curl_setopt($ch, CURLOPT_PORT, $url['port']);
 
        curl_multi_add_handle($queue, $ch);
        $map[(string) $ch] = $url;       //info
    }
 
   // $responses = array();
    do {
        while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;
 
        if ($code != CURLM_OK) { break; }
 
        // a request was just completed -- find out which one
		while ($done = curl_multi_info_read($queue)) {

			// get the info and content returned on the request
			$info = curl_getinfo($done['handle']);
			$error = curl_error($done['handle']);
            //$results = callback(curl_multi_getcontent($done['handle']));
			$callback(curl_multi_getcontent($done['handle']), $info, $map[(string) $done['handle']]);
			$count++;
            //$responses[$map[(string) $done['handle']]] = compact('info', 'error', 'results');
 
            // remove the curl handle that just completed
            curl_multi_remove_handle($queue, $done['handle']);
            curl_close($done['handle']);
        }
 
        // Block for data in / output; error handling is done by curl_multi_exec
        if ($active > 0) {
            curl_multi_select($queue, 0.5);
        }
 
    } while ($active);
 
    curl_multi_close($queue);
    //return $responses;
    return $count;
}


?>