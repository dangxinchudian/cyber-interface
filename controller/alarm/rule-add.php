<?php



	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');
	$type = filter('type', '/^constant|attack$/', 'type格式错误');
	$max_limit = filter('max_limit', '/^[0-9]{1,9}$/', 'max_limit格式错误');
	$min_limit = filter('min_limit', '/^[0-9]{1,9}$/', 'min_limit格式错误');

	$keep_time = filter('min_limit', '/^[0-9]{1,9}$/', 'keep_time格式错误', 300);
	$cool_down_time = filter('min_limit', '/^[0-9]{1,9}$/', 'keep_time格式错误', 600);
	$notice_limit = filter('min_limit', '/^[0-9]{1,9}$/', 'notice_limit格式错误', 3);

	// $site_id = 8;
	// $type = 'attack';
	// $max_limit = 30;
	// $min_limit = 0;
	// $keep_time = 300;
	// $cool_down_time = 600;
	// $notice_limit = 3;

	if($min_limit == 0 && $max_limit == 0) json(false, '不能为无限制');

	$siteModel = model('site');
	$info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	$alarmModel = model('alarm');
	$rule = $alarmModel->selectRule($site_id, $type);
	if(!empty($rule)) json(false, '规则已添加');

	$result = $alarmModel->addRule($info['user_id'], $site_id, $type, $max_limit, $min_limit, $keep_time, $cool_down_time, $notice_limit);
	if($result == false) json(false, '添加失败');

	json(true, '添加成功');



?>