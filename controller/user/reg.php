<?php


	$mail = filter('mail', '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', '邮箱格式不符');
	$pass = filter('pass', '/^.{6,30}$/', '密码需要为6-30位字符');

	/*$mail = 'zje2008@qq.com';
	$pass = 'b123456';*/

	$user = model('user');
	$info = $user->get($mail, 'mail');

	if(!empty($info)) json(false, '该邮箱已注册');

	$user_id = $user->creat($mail, $pass);
	if($user_id === false) json(false, '创建失败');

	$user->login($user_id);
	json(true, '创建成功');


?>