<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$server_id = filter('server_id', '/^[0-9]{1,9}$/', 'serverID格式错误');
	$port = filter('port', '/^[0-9]{1,6}$/', 'port格式错误', '161');
	$version = filter('version', '/^2|3$/', 'version格式错误');
	$community = filter('community', '/^.{0,99}$/', 'community格式错误', true);
	$user = filter('user', '/^.{0,99}$/', 'user格式错误', true);
	$pass = filter('pass', '/^.{0,99}$/', 'pass格式错误', true);

	// $server_id = 16;
	// $port = 161;
	// $version = 2;
	// $community = 'nono';
	// $user = null;
	// $pass = null;

	$serverModel = model('server');
	$info = $serverModel->get($server_id);

	if(empty($info)) json(false, '服务器不存在');
	if($info['remove'] > 0) json(false, '服务器已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人服务器');

	$updateArray = array();
	$updateArray['snmp_port'] = $port;
	$updateArray['snmp_version'] = $version;
	if($version == 2){
		if($community == null) $community = 'public';
		$updateArray['snmp_token'] = jencode($community);
	}else{
		if($user == null) json(false, 'community不能为空');
		if($pass == null) json(false, 'pass不能为空');
		$updateArray['snmp_token'] = jencode($user).'|'.jencode($pass);
	}

	$result = $serverModel->update($server_id, $updateArray);
	if($result > 0) json(true, '更新成功');
	json(false, '未进行更新');


?>