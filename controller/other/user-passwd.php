<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if(!$admin) json(false, '非管理员无权访问！');

	$pass = filter('pass', '/^.{6,30}$/', '密码需要为6-30位字符');
	$user_id = filter('user_id', '/^[0-9]{1,9}$/', 'userID错误');
	// $user_id = 1;
	// $pass = 'dddddd';

	$info = $user->get($user_id);
	if(empty($info)) json(false, 'userID不存在');

	$user->setPass($user_id, $pass);

	json(true, '修改成功');


?>