<?php

//router('user-reset',function(){

	$user = model('user');
	/*$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});*/

	$mail = filter('mail', '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', '邮箱格式不符');

	//$mail = 'zje2008@qq.com';

	$info = $user->get($mail, 'mail');
	if(empty($info)) json(false, '邮箱不存在');
	$code = $user->resetCodeCreat($info['user_id']);
	$html = "<a href=\"http://monitor.secon.me/password-setting?code={$code}&mail={$info['mail']}\" target=\"_blank\">http://monitor.secon.me/password-setting?code={$code}&mail={$info['mail']}</a>";
	send_mail($info['mail'], '帐号找回', $html);

	json(true, '发送成功');

//});



?>