<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$server_id = filter('server_id', '/^[0-9]{1,9}$/', 'serverID格式错误');
	// $server_id = 4;

	$serverModel = model('server');
	if($server_id == 0) $info = $serverModel->get($user_id, 'user_id');
	else $info = $serverModel->get($server_id);

	if(empty($info)) json(false, '服务器不存在');
	if($info['remove'] > 0) json(false, '服务器已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人服务器');

	$list = $serverModel->item();
	foreach ($list as $key => $value) {
		$result =  $serverModel->selectWatch($info['server_id'], $value['server_item_id']);
		if(empty($result)) $list[$key]['server_watch_id'] = 0;
		else{
			if(!empty($result['last_watch_data'])) $result['last_watch_data'] = jdecode($result['last_watch_data']);
			$list[$key]['server_watch_id'] = $result['server_watch_id'];
			$list[$key]['watch_info'] = $result;
		}
	}
	$result = array(
		'server_id' => $info['server_id'],
		'item' => $list
	);
	json(true, $result);

?>