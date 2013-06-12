<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$siteModel = model('site');
	$constantModel = model('constant');

	$siteList = $siteModel->getUser($user_id);
	$constantInfo = array('work' => 0, 'unwork' => 0);
	foreach ($siteList as $key => $value){
		$work = $constantModel->nowFault($value['site_id']);
		if($work) $constantInfo['work']++;
		else $constantInfo['unwork']++;
	}
	
	json(true, $constantInfo);


?>