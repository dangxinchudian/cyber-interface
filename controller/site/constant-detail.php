<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID错误');
	$time_unit = filter('time_unit', '/^day|month|year$/', '时间单位错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');
	$node_id = filter('node_id', '/^[0-9\-]{1,10}$/', 'node_id错误');
	// $site_id = 0;
	// $time_unit = 'day';
	// $start_time = time() - 3600*24*5;
	// $stop_time = time();
	// $node_id = 0;

	if($stop_time < $start_time) json(false, 'time error!');

	$siteModel = model('site');
	if($site_id == 0) $info = $siteModel->get($user_id, 'user_id');
	else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	$constantModel = model('constant');
	$result = $constantModel->log_data($info['site_id'], $time_unit, $start_time, $stop_time, $node_id);

	$data = array();
	//date&data complete
	if($time_unit == 'day'){
		for($i = 0 ; $i<= ($stop_time - $start_time) / (3600*24); $i++){
			$data[date('Y-m-d', $start_time + 3600 * 24 * $i)] = 0;
		}
	}elseif($time_unit == 'month'){
		for($i = 0 ; $i<= ($stop_time - $start_time) / (3600*24*30); $i++){
			$data[date('Y-m', $start_time + 3600 * 24 * $i * 30)] = 0;
		}
	}elseif($time_unit == 'year'){
		for($i = 0 ; $i<= ($stop_time - $start_time) / (3600*24*365); $i++){
			$data[date('Y', $start_time + 3600 * 24 * $i * 365)] = 0;
		}			
	}
	// for($i = 0 ; $i< ($stop_time - $start_time) / (3600*24); $i++){
	// 	if($time_unit == 'day') $data[date('Y-m-d', $start_time + 3600 * 24 * $i)] = 0;
	// 	elseif($time_unit == 'month') $data[date('Y-m', $start_time + 3600 * 24 * $i * 30)] = 0;
	// 	elseif($time_unit == 'year') $data[date('Y-m', $start_time + 3600 * 24 * $i * 365)] = 0;
	// }

	foreach ($result as $key => $value){
		$data[$value['group_time']] = $value['available'];
	}
	$return = array(
		'data' => array_values($data),
		'date' => array_keys($data)
	);

	json(true, $return);

?>