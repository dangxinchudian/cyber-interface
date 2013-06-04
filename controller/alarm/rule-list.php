<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');
	$type = filter('type', '/^constant|attack$/', 'type格式错误', true);
	// $site_id = 8;

	$siteModel = model('site');
	if($site_id == 0) $info = $siteModel->get($user_id, 'user_id');
	else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');
	
	$alarmModel = model('alarm');
	if($type != null){
		$rule = $alarmModel->selectRule($info['site_id'], $type);	
	}else{
		$rule = $alarmModel->selectRule($info['site_id']);	
	}

	json(true, $rule);


?>