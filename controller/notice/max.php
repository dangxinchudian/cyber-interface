<?php

//router('notice-max',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$mail_max = filter('mail_max', '/^[0-9]{1,5}$/', '次数只能为数字', true);
	$mobile_max = filter('mobile_max', '/^[0-9]{1,5}$/', '次数只能为数字', true);
	//$max = 100;

	$updateArray = array();
	if($mail_max !== null) $updateArray['mail_max'] = $mail_max;
	if($mobile_max !== null) $updateArray['mobile_max'] = $mobile_max;
	if(empty($updateArray)) json(false, '参数不能为空');

	$result = $user->update($user_id, $updateArray);
	if($result > 0) json(true, '更新成功');

	json(false, '未更改');
//});


?>