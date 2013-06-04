<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误', true);
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误', true);

	/*$site_id = 0;
	$start_time = time() - 60 * 60 * 24 * 5;
	$stop_time = time();*/

	$siteModel = model('site');
	if($site_id == 0){
		if($admin) $user_id = 0;
		$info = $siteModel->get($user_id, 'user_id');
	}else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	if($stop_time != null && $start_time != null){
		if($stop_time < $start_time) json(false, 'time error!');
		$constantModel = model('constant');
		$info['fault_time'] = $constantModel->log_fault_time($info['site_id'], $start_time, $stop_time, $info['period'], 0);		//临时替代
		$info['available'] = $constantModel->available($info['site_id'], $start_time, $stop_time);
		$info['fault_count'] = $constantModel->faultCount($info['site_id'], $start_time, $stop_time);
	}

	if($info['server_id'] != 0){
		$serverModel = model('server');
		$info['server'] = $serverModel->get($info['server_id']);
	}

	json(true, $info);


?>