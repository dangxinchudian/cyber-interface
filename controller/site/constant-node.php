<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');
	// $site_id = 0;
	// $start_time = time() - 3600*24*5;
	// $stop_time = time();

	$siteModel = model('site');
	if($site_id == 0) $info = $siteModel->get($user_id, 'user_id');
	else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	$constantModel = model('constant');
	$node = $constantModel->node();
	$local = array(array('constant_node_id' => 0, 'name' => '本地'));
	$node = array_merge($local, $node);

	foreach ($node as $key => $value) {
		//后续升级日志加入每次间隔时间，可以使故障时间更精确
		$node[$key]['keep_watch_time'] = $constantModel->log_work_time($info['site_id'], $start_time, $stop_time, $info['period'], $value['constant_node_id']);
		$node[$key]['fault_time'] = $constantModel->log_fault_time($info['site_id'], $start_time, $stop_time, $info['period'], $value['constant_node_id']);
		$node[$key]['available'] = 100 - round($node[$key]['fault_time'] / $node[$key]['keep_watch_time'] * 100, 2);
		$node[$key]['last'] = $constantModel->get_last($info['site_id'], $value['constant_node_id']);
		if(!empty($node[$key]['last'])) $node[$key]['last']['msg'] = errorHeader($node[$key]['last']['status']);

	}

	json(true, $node);


?>