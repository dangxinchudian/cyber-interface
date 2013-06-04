<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$server_id = filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');
	$custom_name = filter('name', '/^.{0,255}$/', '别名格式错误', true);
	$period = filter('period', '/^[0-9]{1,5}$/', '间隔时间格式错误', true);

	/*$server_id = 1;
	$custom_name = 'test server';
	$period = 10;*/

	$serverModel = model('server');
	$info = $serverModel->get($server_id);
	if(empty($info)) json(false, '服务器不存在');
	if($info['remove'] > 0) json(false, '服务器已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人服务器');

	$updateArray = array();
	if($custom_name != null) $updateArray['custom_name'] = $custom_name;
	if($period != null){
		if($period < 60) $period = 60;
		$updateArray['period'] = $period;
	}
	if(empty($updateArray)) json(false, '未提交修改参数');
	$result = $serverModel->update($server_id, $updateArray);
	if($result > 0) json(true, '更改成功');
	json(false, '未进行更改');


?>