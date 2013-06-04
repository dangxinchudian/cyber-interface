<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');
	//$site_id = 0;

	$siteModel = model('site');
	if($site_id == 0) $info = $siteModel->get($user_id, 'user_id');
	else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	$awsModel = model('aws');
	//$total = 1;
	$result= $awsModel->location($info['site_id']);

	$return = array();
	foreach ($result as $key => $value) {
		$return[$key] = array(
			'id' => $value['country_code'],
			'value' => $value['total_hits'],
			'title' => $value['country_desc']
		);
	}
	//print_r($return);
	json(true, $return);


?>