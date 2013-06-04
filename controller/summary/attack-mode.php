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

	$siteList = $siteModel->getUser($user_id);
	foreach ($siteList as $key => $value) $sites[] = $value['site_id'];
	$result = $attackModel->mode($sites, $start_time, $stop_time);

	$modeArray = array();
	foreach ($result as $key => $value) {
		if(!isset($modeArray[$value['attack_type']])){
			$modeArray[$value['attack_type']] = $value;
		}else{
			$modeArray[$value['attack_type']]['count'] += $value['count'];
		}
	}
	// print_r($modeArray);
	json(true, array_values($result));


?>