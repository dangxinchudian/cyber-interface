<?php

//router('user-mail-verify',function(){

	$user = model('user');
	/*$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});*/

	$mail = filter('mail', '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', '邮箱格式不符');
	$code = filter('code', '/^[a-fA-F0-9]{32}$/', 'code需要为32位hash字符');

	/*$mail = 'zje2008@qq.com';
	$code = '2e4c14a915e680fc7936f948fe9c69c0';*/

	$info = $user->get($mail, 'mail');
	if(empty($info)) json(false, '邮箱不存在');
	if($code != $info['code_mail']) json(false, '验证码错误');
	if($info['code_mail_time'] < time()) json(false, '已经过期');

	$updateArray = array('mail_verify' => 1);
	$user->update($info['user_id'], $updateArray);

	json(true, '验证成功');

//});

?>