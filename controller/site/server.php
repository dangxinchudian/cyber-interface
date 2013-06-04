<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');
	$server_id = filter('server_id', '/^[\-0-9]{1,9}$/', 'serverID格式错误');

	// $site_id = 8;
	// $server_id = -1;

	$siteModel = model('site');
	if($site_id == 0){
		if($admin) $user_id = 0;
		$info = $siteModel->get($user_id, 'user_id');
	}else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '你没有权限操作该站点');

	$serverModel = model('server');

	if($server_id <= 0){
		$ip = gethostbyname($info['domain']);
		$server = $serverModel->get($ip, 'ip');
		$creat = false;
		if($server_id == 0){
			if(empty($server)) json(false, '服务器不存在');
			if($server['remove'] > 0) json(false, '服务器已经被移除');
			if(!$admin) if($server['user_id'] != $user_id) json(false, '不允许操作他人服务器');
			$server_id = $server['server_id'];
		}elseif(empty($server)) $creat = true;
		elseif(!empty($server)){
			if($server['remove'] > 0) $creat = true;
			if(!$admin) if($server['user_id'] != $user_id) json(false, '不允许操作他人服务器');
			$server_id = $server['server_id'];
		}
		if($creat){
			$server_id = $serverModel->add($ip, $user_id);
		}
	}else{
		$server = $serverModel->get($server_id);

		if(empty($server)) json(false, '服务器不存在');
		if($server['remove'] > 0) json(false, '服务器已经被移除');
		if($server['user_id'] != $user_id) json(false, '不允许操作他人服务器');
	}

	$result = $siteModel->update($site_id, array('server_id' => $server_id));
	if($result > 0) json(true, '设置服务器成功');
	else json(false, '未进行更改');


?>