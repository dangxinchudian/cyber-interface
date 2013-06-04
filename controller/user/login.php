<?php



	$mail = filter('mail', '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', '邮箱格式不符');
	$pass = filter('pass', '/^.{6,30}$/', '密码需要为6-30位字符');

	/*$mail = 'zje2008@qq.com';
	$pass = 'b123456';*/

	$user = model('user');
	$info = $user->get($mail, 'mail');
	// print_r($info);

	if(empty($info)) json(false, '邮箱不存在，登录失败');

	$enPass = $user->passEncode($pass, $info['usalt']);
	if(strcasecmp($enPass, $info['passwd']) !== 0) json(false, '邮箱或密码错误，登录失败');

	$_SESSION['admin'] = $info['admin'];
	$user->login($info['user_id']);

	setcookie('mail', $mail, time() + 3600 * 24 * 30, '/');
	json(true, '登录成功');



?>
