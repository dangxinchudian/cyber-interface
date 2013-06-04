<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$server_id = filter('server_id', '/^[0-9]{1,9}$/', 'serverID格式错误');

	//$server_id = 0;

	$serverModel = model('server');
	if($server_id == 0) $info = $serverModel->get($user_id, 'user_id');
	else $info = $serverModel->get($server_id);

	if(empty($info)) json(false, '服务器不存在');
	if($info['remove'] > 0) json(false, '服务器已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人服务器');

	$info['cpu'] = 0; 
	$info['disk'] = 1; 
	$info['memory'] = 2;
	$info['sys_descr'] = jdecode($info['sys_descr']);
	$info['sys_name'] = jdecode($info['sys_name']);
	$info['sys_uptime'] = jdecode($info['sys_uptime']);
	$token = $info['snmp_token'];
	unset($info['snmp_token']);
	
	$info['snmp_community'] = '';
	$info['snmp_user'] = '';
	$info['snmp_pass'] = '';
	if($info['snmp_version'] == 3){
		$token = explode('|', $token);
		$info['snmp_user'] = $token[0];
		$info['snmp_pass'] = $token[1];
	}else{
		$info['snmp_community'] = jdecode($token);
	}

	json(true, $info);


?>