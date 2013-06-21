<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if(!$admin) json(false, '非管理员无权访问！');

	$user_id = filter('user_id', '/^[0-9]{1,9}$/', 'userID错误');
	// $user_id = 46;

	$info = $user->get($user_id);
	if(empty($info)) json(false, 'userID不存在');


	$siteModel = model('site');
	$serverModel = model('server');

	$site = $siteModel->getUser($user_id);
	$server = $serverModel->getUser($user_id);

	$querySite = array();
	$queryName = array();
	$queryToken = array();

	foreach ($site as $key => $value){
		$querySite[] = $value['domain'];
		$queryName[] = $value['custom_name'];
	}

	foreach ($server as $key => $value) $queryToken[] = $value['ip'];

	if(count($querySite) > 0) $querySite = "OR domain in ('".implode("','", $querySite)."')";
	else $querySite = '';
	if(count($queryName) > 0) $queryName = "OR name in ('".implode("','", $queryName)."')";
	else $queryName = '';
	if(count($queryToken) > 0) $queryToken = 'OR host_token in ('.implode(',', $queryToken).')';
	else $queryToken = '';

	$db = $user->db();
	$sql = "SELECT * FROM agent_info WHERE mail = '{$info['mail']}' {$querySite} {$queryName} {$queryToken}";
	$result = $db->query($sql, 'array');
	// print_r($result);

	json(true, $result);



?>