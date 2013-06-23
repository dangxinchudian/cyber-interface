<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$newpass = filter('newpass', '/^.{6,30}$/', '新密码需要为6-30位字符');
	$oldpass = filter('oldpass', '/^.{6,30}$/', '旧密码需要为6-30位字符');

	$info = $user->get($user_id);
	if(empty($info)) json(false, 'userID不存在');

	$enPass = $user->passEncode($oldpass, $info['usalt']);
	if(strcasecmp($enPass, $info['passwd']) !== 0) json(false, '原有密码错误，无法修改');

	$user->setPass($user_id, $newpass);

	json(true, '修改成功');


?>