<?php



	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$rule_id = filter('rule_id', '/^[0-9]{1,9}$/', 'rule_id格式错误');
	$max_limit = filter('max_limit', '/^[0-9]{1,9}$/', 'max_limit格式错误',true);
	$min_limit = filter('min_limit', '/^[0-9]{1,9}$/', 'min_limit格式错误',true);

	$keep_time = filter('keep_time', '/^[0-9]{1,9}$/', 'keep_time格式错误', true);
	$cool_down_time = filter('cool_down_time', '/^[0-9]{1,9}$/', 'keep_time格式错误', true);
	$notice_limit = filter('notice_limit', '/^[0-9]{1,9}$/', 'notice_limit格式错误', true);

	// $rule_id = 1;
	// $max_limit = 99;
	// $min_limit = 0;
	// $keep_time = 300;
	// $cool_down_time = 600;
	// $notice_limit = 3;

	// if($min_limit == 0 && $max_limit == 0) json(false, '不能为无限制');

	$alarmModel = model('alarm');
	$info = $alarmModel->getRule($rule_id);
	if(empty($info)) json(false, '该规则不存在');
	if($info['remove'] > 0) json(false, '规则已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人规则');

	$updateArray = $info;
	unset($updateArray['alarm_rule_id'], $updateArray['user_id'], $updateArray['site_id'], $updateArray['type'],$updateArray['remove']);

	if(!empty($max_limit)) $updateArray['max_limit'] = $max_limit;
	if(!empty($min_limit)) $updateArray['min_limit'] = $min_limit;
	if(!empty($keep_time)) $updateArray['keep_time'] = $keep_time;
	if(!empty($cool_down_time)) $updateArray['cool_down_time'] = $cool_down_time;
	if(!empty($notice_limit)) $updateArray['notice_limit'] = $notice_limit;

	if($updateArray['max_limit'] == 0 && $updateArray['min_limit'] == 0) json(false, '不能为无限制');
	
	// $updateArray = array(
	// 	'max_limit' => $max_limit,
	// 	'min_limit' => $min_limit,
	// 	'keep_time' => $keep_time,
	// 	'cool_down_time' => $cool_down_time,
	// 	'notice_limit' => $notice_limit
	// );

	$result = $alarmModel->updateRule($rule_id, $updateArray);
	if($result > 0) json(true, '更改成功');
	json(false, '未更改');



?>