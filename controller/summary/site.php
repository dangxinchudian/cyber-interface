<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$siteModel = model('site');
	$constantModel = model('constant');
	$attackModel = model('attack');

	$result = $siteModel->siteList($user_id, 0, 999, 0);



	foreach ($result as $key => $value) {
		$result[$key]['work'] = $constantModel->nowFault($value['site_id']);
		$result[$key]['attack'] = $attackModel->total_count($value['site_id'], time() - 300, time());
	}

	$alarm = array();
	$normal = array();

	foreach ($result as $key => $value) {
		if($value['work'] == 0 || $value['attack'] > 0){
			$alarm[] = $value;
		}else{
			$normal[] = $value;
		}
	}

	$result = array_merge($alarm, $normal);

	json(true, $result);

?>