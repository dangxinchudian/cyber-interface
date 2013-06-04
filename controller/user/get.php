<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$info = $user->get($user_id);

	if(empty($info)) json(false, '用户不存在');
	if(isset($info['code_mobile'])) unset($info['code_mobile']);
	if(isset($info['code_mail'])) unset($info['code_mail']);

	json(true, $info);



?>
