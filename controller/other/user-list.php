<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if(!$admin) json(false, '非管理员无权访问！');

	$siteModel = model('site');
	$serverModel = model('server');
	$db = $siteModel->db();

	$sql = "SELECT * FROM user WHERE remove = 0";
	$result = $db->query($sql, 'array');

	$site = $siteModel->siteList(0, 0, 9999);
	$server = $serverModel->serverList(0, 0, 9999);

	$userArray = array();
	foreach ($result as $key => $value){
		$userArray[$value['user_id']] = $value;
		$userArray[$value['user_id']]['site'] = array();
		$userArray[$value['user_id']]['server'] = array();
	}

	foreach ($site as $key => $value) $userArray[$value['user_id']]['site'][] = $value;
	foreach ($server as $key => $value) $userArray[$value['user_id']]['server'][] = $value;
	
	$result = array_values($userArray);
	json(true, $result);
	// print_r($result);


?>