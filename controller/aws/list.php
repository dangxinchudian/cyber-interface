<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$page = filter('page', '/^[0-9]{1,9}$/', '页码格式错误');
	$limit = filter('limit', '/^[0-9]{1,9}$/', '偏移格式错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	// $page = 1;
	// $limit = 10;
	// $start_time = time() - 3600 * 24 *5;
	// $stop_time = time();

	if($limit <= 0) $limit = 1;
	if($page < 1) $page = 1;
	$start = ($page - 1) * $limit;

	$siteModel = model('site');
	$awsModel = model('aws');
	$attackModel = model('attack');

	$result = $siteModel->siteList($user_id, $start, $limit, 0);
	$count = $siteModel->siteCount($user_id, 0);
	foreach ($result as $key => $value) {
		$http = $awsModel->summary($value['site_id'], $start_time, $stop_time);
		$result[$key]['hits'] = (isset($http['hits'])) ? $http['hits'] : 0;
		$result[$key]['visits'] = (isset($http['visits'])) ? $http['visits'] : 0;
		$result[$key]['bandwidth'] = (isset($http['bandwidth'])) ? $http['bandwidth'] : 0;
		$result[$key]['attack_ip'] = $attackModel->ip_count($value['site_id'], $start_time, $stop_time);
		$result[$key]['attack_total'] = $attackModel->total_count($value['site_id'], $start_time, $stop_time);
		$result[$key]['percent'] = 0;
		if($result[$key]['hits'] != 0) $result[$key]['percent'] = round($result[$key]['attack_total'] / $result[$key]['hits'] * 100, 2);
		if($result[$key]['percent'] > 100) $result[$key]['percent'] = 100;
		
	}
	$array = array(
		'page' => $page,
		'limit' => $limit,
		'list' => $result,
		'total' => $count 
	);

	json(true, $array);


?>