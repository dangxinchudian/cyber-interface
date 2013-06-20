<?php

$mail =  filter('mail', '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', '邮箱格式错误');
$name = filter('name', '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}\,\s]{1,255}$/u', '单位名称格式错误');
$domain = filter('domain', '/^[a-zA-z0-9\-\.\,]+\.[a-zA-z0-9\-\.\,]+$/', '域名格式错误');
$mobile = filter('mobile', '/^[0-9\-]{1,20}$/', '联系电话(手机)格式错误');
$incharge = filter('incharge', '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}\,\s]{1,255}$/u', '联系人格式错误');

// $mail = 'zje2008@qq.com';
// $name = '大公司';
// $domain = 'big.com';
// $mobile = 15067175241;
// $incharge = '哈哈';

$model = new model;
$db = $model->db();
$sql = "SELECT * FROM agent_info WHERE domain = '{$domain}' AND name = '{$name}'";
$result = $db->query($sql,'row');
if(!empty($result)) json(true, $result['host_token']);


$host_token = 0;
for ($i=0; $i < 15; $i++) { 
	$random = random('num', 6);
	$result = $db->query("SELECT * FROM agent_info WHERE host_token = '{$host_token}'", 'row');
	if(empty($result)){
		$host_token = $random;
		break;
	}
}
if(empty($host_token)) json(false, '未找到可用Token，请重试');

$insertArray = array(
	'mail' => $mail,
	'name' => $name,
	'domain' => $domain,
	'mobile' => $mobile,
	'incharge' => $incharge,
	'host_token' => $host_token
);

$result = $db->insert('agent_info', $insertArray);
if($result > 0) json(true, $host_token);
json(false, '创建Token失败');


?>