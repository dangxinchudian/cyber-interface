<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$server_id =  filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');
	//$server_id = 1;

	$serverModel = model('server');
	$info = $serverModel->get($server_id);
	if(empty($info)) json(false, '服务器不存在');
	if($info['remove'] > 0) json(false, '服务器已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人服务器');

	$snmpModel = model('snmpCatch');
	$snmpModel->ip = $info['ip'];
	$snmpModel->community = jdecode($info['snmp_token']);
	//$snmpModel->ip = '61.175.163.196';

	$result['os'] = $snmpModel->os();
	if($result['os'] === NULL) json(false, 'SNMP服务对应字段抓取失败');
	if($result['os'] === false) json(false, 'SNMP服务未开启');
	$result['sys_descr'] = $snmpModel->sys_descr();
	$result['sys_name'] = $snmpModel->sys_name();
	$result['sys_uptime'] = $snmpModel->sys_uptime();

	$updateArray = array(
		'os' => $result['os'],
		'sys_descr' => jencode($result['sys_descr']),
		'sys_name' => jencode($result['sys_name']),
		'sys_uptime' => jencode($result['sys_uptime']),
	);
	$serverModel->update($server_id, $updateArray);
	json(true, $result);


?>