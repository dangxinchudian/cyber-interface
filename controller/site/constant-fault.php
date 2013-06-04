<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');
	$page = filter('page', '/^[0-9]{1,9}$/', '页码格式错误');
	$limit = filter('limit', '/^[0-9]{1,9}$/', '偏移格式错误');

	// $site_id = 17;
	// $start_time = time() - 3600*24*5;
	// $stop_time = time();
	// $page = 1;
	// $limit = 10;

	if($stop_time < $start_time) json(false, 'time error!');
	if($limit <= 0) $limit = 1;
	if($page < 1) $page = 1;
	$start = ($page - 1) * $limit;

	$siteModel = model('site');
	if($site_id == 0) $info = $siteModel->get($user_id, 'user_id');
	else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	$constantModel = model('constant');
	$result = $constantModel->fault($info['site_id'], $start_time, $stop_time, $start, $limit);
	$result['limit'] = $limit;
	$result['page'] = $page;
	foreach ($result['list'] as $key => $value) {
		$result['list'][$key]['end_time'] = 0;
		if($value['status'] == 'slove'){
			$result['list'][$key]['end_time'] = date('Y-m-d H:i:s', strtotime($value['time']) + $value['keep_time']);
		}
		$result['list'][$key]['msg'] = errorHeader($value['http_code']);
	}

	json(true, $result);

?>