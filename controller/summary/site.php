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
		if($value['server_id'] == 0) $result[$key]['server'] = array();
		else{
			$server = $serverModel->get($value['server_id']);
			if(empty($server)) $result[$key]['server'] = array();
			else{
				$server['sys_descr'] = jdecode($server['sys_descr']);
				$server['sys_name'] = jdecode($server['sys_name']);
				$server['sys_uptime'] = jdecode($server['sys_uptime']);

				$server['cpu'] = -1;
				$server['in_speed'] = -1;
				$server['out_speed'] = -1;
				$server['memory'] = -1;
				$server['disk'] = -1;

			}
		}
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