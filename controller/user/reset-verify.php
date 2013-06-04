<?php

//router('user-reset-verify',function(){

	$user = model('user');
	/*$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});*/

	$mail = filter('mail', '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', '邮箱格式不符');
	$code = filter('code', '/^[a-fA-F0-9]{32}$/', 'code需要为32位hash字符');
	$pass = filter('pass', '/^.{6,30}$/', '密码需要为6-30位字符');

	/*$mail = 'zje2008@qq.com';
	$code = 'f05e6a0866eb69bb6660b3ad49dc6b46';
	$pass = 'a123456';*/

	$info = $user->get($mail, 'mail');
	if(empty($info)) json(false, '邮箱不存在');
	if($code != $info['code_reset']) json(false, '验证码错误');
	if($info['code_reset_time'] < time()) json(false, '已经过期');

	$user->setPass($info['user_id'], $pass);

	json(true, '重置成功');

//});

?>