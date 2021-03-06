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
	// $start_time = time() - 260935;
	// // echo $start_time;
	// $stop_time = time();

	if($limit <= 0) $limit = 1;
	if($page < 1) $page = 1;
	$start = ($page - 1) * $limit;

	$siteModel = model('site');
	$constantModel = model('constant');
	$result = $siteModel->siteList($user_id, $start, $limit, 0);
	$count = $siteModel->siteCount($user_id, 0);
	foreach ($result as $key => $value) {
		//$result[$key]['work_time'] = $constantModel->work_time($value['site_id'], $start_time, $stop_time, $value['period'], 0);
//		$result[$key]['fault_time'] = $constantModel->log_fault_time($value['site_id'], $start_time, $stop_time, $value['period'], 0);		//临时替代
//		$result[$key]['available'] = $constantModel->available($value['site_id'], $start_time, $stop_time);
		$result[$key]['fault_time'] = $constantModel->table_fault_time($value['site_id'], $start_time, $stop_time);
		// $result[$key]['available'] = $constantModel->available_time($value['site_id'], $start_time, $stop_time);

		if($value['creat_time'] > $start_time) $start_time = $value['creat_time'];
		$wholeTime = $stop_time - $start_time;
		if($wholeTime <= 0){
			$available = 0;
		}else{
			if($result[$key]['fault_time'] > $wholeTime) $result[$key]['fault_time'] = $wholeTime;
			$available = round(($wholeTime - $result[$key]['fault_time']) / $wholeTime, 4) * 100;
		}
		// $result[$key]['test'] = $constantModel->table_test($value['site_id'], $start_time, $stop_time);
		$result[$key]['wholeTime'] = $wholeTime;
		$result[$key]['available'] = $available;
		$result[$key]['keey_day'] = (int)(($stop_time-$start_time)/(24*3600));
		$result[$key]['work'] = $constantModel->nowFault($value['site_id']);

	}
	$array = array(
		'page' => $page,
		'limit' => $limit,
		'list' => $result,
		'total' => $count 
	);

	json(true, $array);


?>