<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$watch_id = filter('watch_id', '/^[0-9]{1,9}$/', 'watch_id格式错误', true);
	$server_id = filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误', true);
	$item_id = filter('item_id', '/^[0-9]{1,9}$/', 'item_id格式错误', true);

	// $server_id = 4;
	// $item_id = 1;
	// $watch_id = null;
	// $start_time = null;
	// $stop_time = null;
	
	$start_time = strtotime(date('Y-m-d 0:0:0'));
	$stop_time = strtotime(date('Y-m-d 23:59:59'));

	$serverModel = model('server');
	if($watch_id != null) $watch = $serverModel->selectWatch($watch_id);
	else $watch = $serverModel->selectWatch($server_id, $item_id);

	if(empty($watch)) json(false, '监控不存在');
	if($watch['remove'] > 0) json(false, '监控已经被移除');
	if(!$admin) if($watch['user_id'] != $user_id) json(false, '不允许操作他人监控');

	$watch['item'] = $serverModel->item($watch['server_item_id']);
	$watch['device'] = array();
	if(!empty($watch['last_watch_data'])){
		$last_watch_data = jdecode($watch['last_watch_data']);
		$watch['snmp'] = true;
	}else $watch['snmp'] = false;
	unset($watch['last_watch_data']);

	//统计该日的信息
	$summary = $serverModel->log_data($watch['server_id'], $watch['item']['table_name'], 0, 'day', $start_time, $stop_time);
	$deviceSummary = array();
	foreach ($summary as $key => $value) {
		if($value['group_time'] == date('Y-m-d')){
			 if(isset($value['device_id'])) $deviceSummary[$value['device_id']] = $value;
			 else $deviceSummary[] = $value;
		}
	}
	// print_r($deviceSummary);

	if($watch['item']['server_hardware_id'] != 0){
		// $watch['device'] = $serverModel->getDevice($watch['item']['server_hardware_id'], 'hardware_id');
		$watch['device'] = $serverModel->getDevice($watch['server_id'], $watch['item']['server_hardware_id']);
		foreach ($watch['device'] as $key => $value) {
			$watch['device'][$key]['value'] = jdecode($value['value']);
			$watch['device'][$key]['last'] = array();
			if(isset($last_watch_data[$value['hash']])) $watch['device'][$key]['last'] = $last_watch_data[$value['hash']];
			// if(empty($watch['device'][$key]['last'])) $watch['device'][$key]['last'] = array();
			$watch['device'][$key]['summary'] = array();
			if(isset($deviceSummary[$value['server_device_id']])){
				$watch['device'][$key]['summary'] = $deviceSummary[$value['server_device_id']];
			}
		}
	}else{
		$watch['device'] = array();
		$watch['device'][0] = array(
			'server_device_id' => 0,
			'remove' => 0,
			'server_id' => $watch['server_id'],
			'user_id'  => $watch['user_id'],
			'server_hardware_id' => 0,
			'hash' => '',
			'value' => '',
			'name' => '',
			'last' => array()
		);
		if(isset($last_watch_data)) $watch['device'][0]['last'] = $last_watch_data;
		$watch['device'][0]['summary'] = array();
		if(count($deviceSummary) > 0) $watch['device'][0]['summary'] = $deviceSummary[0];
	}

	// if(!empty($watch['last_watch_data'])) $watch['last_watch_data'] = jdecode($watch['last_watch_data']);

	// print_r($watch);
	json(true, $watch);

?>