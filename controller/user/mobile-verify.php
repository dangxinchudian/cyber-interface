<?php



	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$code = filter('code', '/^[0-9]{6}$/', 'code需要为6位数字');
	// $code = 774890;

	$info = $user->get($user_id);
	if(empty($info)) json(false, '用户不存在');
	if($code != $info['code_mobile']) json(false, '验证码错误');
	if($info['code_mobile_time'] < time()) json(false, '已经过期');

	$updateArray = array('mobile' => $info['wait_mobile']);
	$user->update($info['user_id'], $updateArray);

	json(true, '验证成功');



?>