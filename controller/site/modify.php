<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'site_id格式错误');
	$path = filter('path', '/^\/.{0,255}+$/', '监控路径格式错误', true);
	$custom_name = filter('name', '/^.{0,255}$/', '别名格式错误', true);
	$port = filter('port', '/^[0-9]{1,5}$/', '端口格式错误', true);
	$period = filter('period', '/^[0-9]{1,5}$/', '间隔时间格式错误', true);
	
	$siteModel = model('site');
	$info = $siteModel->get($site_id);
	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '你没有权限操作该站点');

	$updateArray = array();
	if($path != null) $updateArray['path'] = $path;
	if($custom_name != null) $updateArray['custom_name'] = $custom_name;
	if($port != null) $updateArray['port'] = $port;
	if($period != null){
		if($period < 60) $period = 60;
		$updateArray['period'] = $period;
	}
	if(empty($updateArray)) json(false, '未提交修改参数');
	$result = $siteModel->update($site_id, $updateArray);
	if($result > 0) json(true, '更改成功');
	json(false, '未进行更改');


?>