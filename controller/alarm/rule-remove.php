<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$rule_id = filter('rule_id', '/^[0-9]{1,9}$/', 'rule_id格式错误');

	// $rule_id = 1;

	$alarmModel = model('alarm');
	$info = $alarmModel->getRule($rule_id);
	if(empty($info)) json(false, '该规则不存在');
	if($info['remove'] > 0) json(false, '规则已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人规则');

	$result = $alarmModel->updateRule($rule_id, array('remove' => 1));
	if($result > 0) json(true, '移除成功');
	json(false, '未更改');



?>