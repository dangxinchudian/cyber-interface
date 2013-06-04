<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$start_time = time() - 60 * 60 * 24;
	$stop_time = time();

	$attackModel = model('attack');
	$siteModel = model('site');
	$sites = array();
	$siteList = $siteModel->getUser($user_id);
	foreach ($siteList as $key => $value) $sites[] = $value['site_id'];
	$attackDaily = $attackModel->hour($sites, $start_time, $stop_time);

	// $http = array();
	$attack = array();
	for($i = 0 ; $i <= ($stop_time - $start_time) / (3600); $i++){
		$attack[date('Ymd H', $start_time + 3600 * $i)] = 0;
	}

	// print_r($attackDaily);

	foreach ($attackDaily as $key => $value) {
		$attack[$value['group_time']] += (int)$value['count'];
	}

	$result = array(
		0 => array_keys($attack),
		1 => array_values($attack)
	);

	json(true, $result);


?>