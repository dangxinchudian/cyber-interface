<?php



	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$mail = filter('mail', '/^0|1$/', '邮箱设置参数只能为1或0', true);
	$mobile = filter('mobile', '/^0|1$/', '手机设置参数只能为1或0', true);

	// $mail = 1;
	// $mobile = 1;

	// if($mail === null && $mobile === null) json(false, '手机邮箱设置必须有一个');

	$updateArray = array();
	if($mail !== null) $updateArray['notice_mail'] = $mail;
	if($mobile !== null) $updateArray['notice_mobile'] = $mobile;
	if(empty($updateArray)) json(false, '手机邮箱设置必须有一个');

	$result = $user->update($user_id, $updateArray);
	if($result > 0) json(true, '更新成功');

	json(false, '未更改');

?>