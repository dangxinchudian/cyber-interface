<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if(!$admin) json(false, '非管理员无权访问！');

	$mail = filter('mail', '/^.{1,999}$/', '用户名格式错误');
	$user_id = filter('user_id', '/^[0-9]{1,9}$/', 'userID错误');
	// $user_id = 1;
	// $mail = 'zcoson@qq.com';

	$info = $user->get($user_id);
	if(empty($info)) json(false, 'userID不存在');


	$info = $user->get($mail, 'mail');
	if(!empty($info)) json(false, '该用户名已经被使用');

	$user->update($user_id, array('mail' => $mail));

	json(true, '修改成功');


?>