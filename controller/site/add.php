<?php

//router('site-add',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	// if($admin) $user_id = 0;

	$domain = filter('domain', '/^[a-zA-z0-9\-\.]+\.[a-zA-z0-9\-\.]+$/', '域名格式错误');
	$path = filter('path', '/^\/.{0,255}+$/', '监控路径格式错误', '/');
	$custom_name = filter('name', '/^.{0,255}$/', '别名格式错误', '');
	$port = filter('port', '/^[0-9]{1,5}$/', '端口格式错误', 80);
	$period = filter('period', '/^[0-9]{1,5}$/', '间隔时间格式错误', 60);
	$input_user_id = filter('user_id', '/^[0-9]{1,5}$/', '用户ID格式错误', true);

	if($period < 60) $period = 60;
	if($admin && $user_id != null) $user_id = $input_user_id;

	$siteModel = model('site');
	$info = $siteModel->get($domain, 'domain');
	if(!empty($info)) json(false, '该站点已经被添加');

	$result = $siteModel->add($domain, $user_id,  $custom_name, $port, $path, $period);
	if($result == false) json(false, '添加失败');

	//init alarm rule
	$alarmModel = model('alarm');
	$alarmModel->addRule($user_id, $result, 'constant', 0, 90, 300, 600, 3);
	$alarmModel->addRule($user_id, $result, 'attack', 50, 0, 300, 600, 3);

	json(true, '添加成功');

//});

?>