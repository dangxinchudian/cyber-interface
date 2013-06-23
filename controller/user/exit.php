<?php


	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	session_destroy();
	json(true, '退出成功');


?>