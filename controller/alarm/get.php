<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	// $admin = 0;

	$id = filter('id', '/^[a-zA-Z0-9]{8}\-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/', 'id格式错误');

	// $id = '3b2d8388-c36b-11e2-bb97-00163e020fb1';

	$alarmModel = model('alarm');
	$alarm = $alarmModel->get($id);
	$siteModel = model('site');
	$info = $siteModel->get($alarm['site_id']);

	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	// print_r($alarm);
	json(true, $alarm);


?>