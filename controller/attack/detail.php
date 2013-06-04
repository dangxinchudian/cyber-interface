<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');
	$page = filter('page', '/^[0-9]{1,9}$/', '页码格式错误');
	$limit = filter('limit', '/^[0-9]{1,9}$/', '偏移格式错误');
	$rank = filter('rank', '/^high|medium|low$/', 'rank格式错误', true);
	$severity = filter('rank', '/^.{1,99}$/', 'severity格式错误', true);

	// $site_id = 0;
	// $start_time = time() - 60 * 60 * 24 * 5;
	// $stop_time = time();
	// $page = 1;
	// $limit = 10;
	// $rank = 'low';
	// $severity = null;
	
	if($limit <= 0) $limit = 1;
	if($page < 1) $page = 1;
	$start = ($page - 1) * $limit;

	$siteModel = model('site');
	if($site_id == 0) $info = $siteModel->get($user_id, 'user_id');
	else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	$severityArray = array();
	if(!empty($rank)){
		$rankArray = array(
			'high' => array('EMERGENCY', 'ALERT', 'CRITICAL'),
			'medium' => array('ERROR', 'WARNING'),
			'low' => array('INFO', 'DEBUG', 'NOTICE')			
		);
		$severityArray = $rankArray[$rank];
	}elseif(!empty($severity)){
		$attack = array('EMERGENCY','ALERT','CRITICAL','ERROR','WARNING','NOTICE','INFO','DEBUG');
		if(!in_array($severity, $attack)) json(false, 'severity格式错误');
		$severityArray = array($severity);
	}else json(false, 'rank,severity必须提交一个');

	$attackModel = model('attack');
	$result = $attackModel->detail($info['site_id'], $start_time, $stop_time, $start, $limit, $severityArray);
	$result['limit'] = $limit;
	$result['page'] = $page;

	json(true, $result);


?>