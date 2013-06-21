<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if(!$admin) json(false, '非管理员无权访问！');


	$siteModel = model('site');
	$serverModel = model('server');
	$user_id = filter('user_id', '/^[0-9]{1,9}$/', 'userID错误');

	$info = $user->get($user_id);
	if(empty($info)) json(false, 'userID不存在');

	$info['site'] = $siteModel->getUser($user_id);
	$info['server'] = $serverModel->getUser($user_id);

	json(true, $info);


?>