<?php


	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$mobile = filter('mobile', '/^[0-9]{11}$/', '手机需要为11位');
	// $mobile = 15067175241;

	$info = $user->get($user_id);
	if($info['mobile'] != 0) json(false, '手机必须要解绑');
	$user->update($user_id, array('wait_mobile' => $mobile));

	$code = $user->mobileCodeCreat($user_id);
	$sms = "你的验证码为{$code}";
	send_sms($mobile, $sms);

	json(true, '发送成功');


?>