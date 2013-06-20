<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	// $ip =  filter('ip', '/^([0-9]{1,3}.){3}[0-9]{1,3}$/', 'IP或者token格式错误');
	$ip =  filter('ip', '/^[0-9\.]{1,99}$/', 'IP或者token格式错误');
	$custom_name = filter('name', '/^.{0,255}$/', '别名格式错误', '');
	$period = filter('period', '/^[0-9]{1,5}$/', '间隔时间格式错误', 60);
	$input_user_id = filter('user_id', '/^[0-9]{1,5}$/', '用户ID格式错误', true);

	if($period < 60) $period = 60;
	if($admin && !empty($user_id)) $user_id = $input_user_id;
	//$ip = '61.175.163.196';

	$serverModel = model('server');
	$info = $serverModel->get($ip, 'ip');

	if(!empty($info)) json(false, '该IP已经被添加');

	$result = $serverModel->add($ip, $user_id, $custom_name, $period);
	if($result == false) json(false, '添加失败');
	json(true, $result);




?>
