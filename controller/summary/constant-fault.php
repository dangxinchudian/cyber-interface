<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;
	
	$page = filter('page', '/^[0-9]{1,9}$/', '页码格式错误');
	$limit = filter('limit', '/^[0-9]{1,9}$/', '偏移格式错误');

	// $page = 1;
	// $limit = 10;

	$start_time = time() - 60 * 60 * 24;
	$stop_time = time();

	if($page < 1) $page = 1;
	$start = ($page - 1) * $limit;

	$siteModel = model('site');
	$constantModel = model('constant');
	$siteList = $siteModel->getUser($user_id);
	foreach ($siteList as $key => $value) $sites[] = $value['site_id'];

	$result = $constantModel->fault($sites, $start_time, $stop_time, $start, $limit);
	$result['limit'] = $limit;
	$result['page'] = $page;
	foreach ($result['list'] as $key => $value) {
		$result['list'][$key]['end_time'] = 0;
		if($value['status'] == 'slove'){
			$result['list'][$key]['end_time'] = date('Y-m-d H:i:s', strtotime($value['time']) + $value['keep_time']);
		}
		$result['list'][$key]['msg'] = errorHeader($value['http_code']);
		$result['list'][$key]['site'] = $siteModel->get($value['site_id']);
	}

	json(true, $result);

?>