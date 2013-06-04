<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');	
	$active = filter('active', '/^start|stop$/', '监测动作格式错误');
	
	/*$site_id = 8;
	$active = 'start';*/

	$siteModel = model('site');
	$info = $siteModel->get($site_id);
	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	if($active == 'start') $updateArray = array('constant_status' => 1);
	else $updateArray = array('constant_status' => 0);

	$result = $siteModel->update($site_id, $updateArray);

	if($result > 0) json(true, '更改监控状态成功');
	json(false, '未更改');

?>