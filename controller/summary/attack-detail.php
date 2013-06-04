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

	
	if($limit <= 0) $limit = 1;
	if($page < 1) $page = 1;
	$start = ($page - 1) * $limit;

	$siteModel = model('site');
	$attackModel = model('attack');

	$sites = array();
	$siteList = $siteModel->getUser($user_id);
	foreach ($siteList as $key => $value) $sites[] = $value['site_id'];
	$result = $attackModel->detail($sites, $start_time, $stop_time, $start, $limit);
	// print_r($sites);

	// $attackModel = model('attack');
	// $result = $attackModel->detail($info['site_id'], $start_time, $stop_time, $start, $limit, $severityArray);

	foreach ($result['list'] as $key => $value) {
		$result['list'][$key]['site'] = $siteModel->get($value['site_id']);
	}
	$result['limit'] = $limit;
	$result['page'] = $page;

	// print_r($result);
	json(true, $result);


?>