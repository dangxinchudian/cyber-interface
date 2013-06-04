<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$server_id = filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');
	$destroy = filter('destroy', '/^false|true$/', 'destroy格式错误');
	/*$server_id = 2;
	$destroy = 'true';*/

	$serverModel = model('server');
	$info = $serverModel->get($server_id);
	if(empty($info)) json(false, '该服务器不存在');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '你没有权限操作该服务器');
	if($info['remove'] == 2) json(false, '该服务器已销毁');
	if($destroy == 'true'){
		$result = $serverModel->remove($server_id, true);
	}else{
		if($info['remove'] == 1) json(false, '该服务器已删除');
		$result = $serverModel->remove($server_id, false);
	}

	if($result) json(true, '删除成功');

	json(true, '未进行删除');


?>