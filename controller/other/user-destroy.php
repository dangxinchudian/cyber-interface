<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if(!$admin) json(false, '非管理员无权访问！');

	$user_id = filter('user_id', '/^[0-9]{1,9}$/', 'userID错误');

	$siteModel = model('site');
	$serverModel = model('server');

	$site = $siteModel->getUser($user_id);
	$server = $serverModel->getUser($user_id);

	foreach ($site as $key => $value) {
		$result = $siteModel->remove($value['site_id'], true);
	}

	foreach ($server as $key => $value) {
		$result = $serverModel->remove($value['server_id'], true);
	}

	$sql = "DELETE FROM user WHERE user_id='{$user_id}'";
	$serverModel->db()->query($sql);

	json(true, '销毁账户成功！');

	// $result = $siteModel->remove($site_id, true);

?>