<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$start_time = time() - 60 * 60 * 24;
	$stop_time = time();

	$siteModel = model('site');
	$attackModel = model('attack');
	$constantModel = model('constant');

	//监控网站数
	$siteList = $siteModel->getUser($user_id);
	$site_count = count($siteList);

	//受攻击数量
	foreach ($siteList as $key => $value) $sites[] = $value['site_id'];
	$result = $attackModel->detail($sites, $start_time, $stop_time, 0, 1);
	$attack_count = $result['total'];
	// print_r($result);

	//网站故障次数
	$result = $constantModel->fault($sites, $start_time, $stop_time, 0, 1);
	$fault_count = $result['total'];

	//性能告警
	//none

	$result = array(
		'site' => $site_count,
		'attack' => $attack_count,
		'fault' => $fault_count,
		'server' => 0
	);

	json(true, $result);


?>