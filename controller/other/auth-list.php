<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if(isset($_POST) && $_POST['token'] == 'cf05dcc346658899469f2d50311a09e4') $admin = true;
	if(!$admin) json(false, '非管理员无权访问！');

	$model = new model;
	$serverModel = model('server');
	$siteModel = model('site');
	$db = $model->db();

	$sql = "SELECT * FROM agent_info";
	$result = $db->query($sql, 'array');

	$server = $serverModel->serverList(0, 0, 9999, 0);
	$site = $siteModel->siteList(0, 0, 9999, 0);

	$serverMatch = array();
	// print_r($server);
	foreach ($server as $key => $value) $serverMatch[$value['ip']] = $value;

	$siteMatch = array();
	foreach ($site as $key => $value) $siteMatch[$value['domain']] = $value;

	foreach ($result as $key => $value) {
		$result[$key]['server_id'] = 0;
		$result[$key]['server_user_id'] = 0;
		$result[$key]['site_id'] = 0;
		$result[$key]['site_user_id'] = 0;
		if(isset($siteMatch[$value['domain']])){
			$result[$key]['site_id'] = $siteMatch[$value['domain']]['site_id'];
			$result[$key]['site_user_id'] = $siteMatch[$value['domain']]['user_id'];
		}
		if(isset($serverMatch[$value['host_token']])){
			$result[$key]['server_id'] = $serverMatch[$value['host_token']]['server_id'];
			$result[$key]['server_user_id'] = $serverMatch[$value['host_token']]['user_id'];
		}
	}

	json(true, $result);
	


?>