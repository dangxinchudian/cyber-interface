<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$ip =  filter('ip', '/^([0-9]{1,3}.){3}[0-9]{1,3}$/', 'IP格式错误');
	$custom_name = filter('name', '/^.{0,255}$/', '别名格式错误', '');
	$period = filter('period', '/^[0-9]{1,5}$/', '间隔时间格式错误', 60);
	if($period < 60) $period = 60;
	//$ip = '61.175.163.196';

	$serverModel = model('server');
	$info = $serverModel->get($ip, 'ip');

	if(!empty($info)) json(false, '该IP已经被添加');

	$result = $serverModel->add($ip, $user_id, $custom_name, $period);
	if($result == false) json(false, '添加失败');
	json(true, $result);




?>
