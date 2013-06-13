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
	else{
		$watch = $serverModel->selectWatch($server_id, $item_id);
		if(empty($watch)){
			$snmpCatch = model('snmpCatch');

			$info = $serverModel->get($server_id);
			if(empty($info)) json(false, '服务器不存在');
			if($info['remove'] > 0) json(false, '服务器已经被移除');
			if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人服务器');
			$snmpCatch->ip = $info['ip'];


			$item = $serverModel->item($item_id);
			if(empty($item)) json(false, '监控项目不存在');
			if($item['os'] != 'all' && $item['os'] != $info['os']) json(false, '该操作系统不支持该监控项目');
			
			//查询硬件是否注册,如果server_hardware_id为0则无需注册
			if($item['server_hardware_id'] != 0){
				$hardware = $serverModel->hardware($item['server_hardware_id']);
				switch ($hardware['value']) {
					case 'disk':
						$disk = $snmpCatch->disk();
						if($disk === NULL) json(false, 'SNMP服务对应字段抓取失败');
						if(!$disk) json(false, '无法连接SNMP服务');
						$device = array();
						foreach ($disk as $key => $value) {
							$device[] = array(
								'hash' => $serverModel->device_hash($server_id, $item['server_hardware_id'], $value['name']),
								'name' => jencode(str2utf8($value['name']))
							);
							
						}
						$serverModel->setDevice($info['user_id'], $server_id, $item['server_hardware_id'], $device);

						break;

					case 'network':
						$network = $snmpCatch->network();
						if($network === NULL) json(false, 'SNMP服务对应字段抓取失败');
						if(!$network) json(false, '无法连接SNMP服务');
						$device = array();
						foreach ($network as $key => $value) {
							$device[] = array(
								'hash' => $serverModel->device_hash($server_id, $item['server_hardware_id'], $value['descr'].$value['physAddress']),
								'name' => jencode(str2utf8($value['descr']))
							);
							
						}
						$serverModel->setDevice($info['user_id'], $server_id, $item['server_hardware_id'], $device);

						break;

					case 'cpu':
						$cpu = $snmpCatch->cpu();
						if($cpu === NULL) json(false, 'SNMP服务对应字段抓取失败');
						if(!$cpu) json(false, '无法连接SNMP服务');
						$device = array();
						foreach ($cpu as $key => $value) {
							$device[] = array(
								'hash' => $serverModel->device_hash($server_id, $item['server_hardware_id'], $key),
								'name' => jencode("处理器{$key}")
							);
							
						}			
						$serverModel->setDevice($info['user_id'], $server_id, $item['server_hardware_id'], $device);				
						break;

					/*case 'memory':
						$memory = $snmpCatch->memory_total();
						if(!$memory) json(false, '无法连接SNMP服务');
						//$memory = array_shift($memory);
						$process = $snmpCatch->process();
						if(!$process) json(false, '无法连接SNMP服务');
						$used_memory = 0;
						foreach ($process as $key => $value) {
							$used_memory += (int)$value['memory'];
						}
						echo $memory;
						//echo $used_memory;
						//print_r($memory);

						break;*/

					default:
						json(false, '未识别硬件格式');
						break;
				}
			}else{
				switch ($item['table_name']) {
					case 'memory_log':
						$result = $snmpCatch->memory_total();
						if($result === NULL) json(false, 'SNMP服务对应字段抓取失败');
						if(!$result) json(false, '无法连接SNMP服务');
						$result = $snmpCatch->process();
						if($result === NULL) json(false, 'SNMP服务对应字段抓取失败');
						if(!$result) json(false, '无法连接SNMP服务');
						break;

					case 'processcount_log':
						$result = $snmpCatch->process();
						if($result === NULL) json(false, 'SNMP服务对应字段抓取失败');
						if(!$result) json(false, '无法连接SNMP服务');
						break;
					
				}
			}

			$result = $serverModel->addWatch($server_id, $item_id, $item['table_name'], $info['user_id']);
			if($result > 0) $watch = $serverModel->selectWatch($result);
			else json(false, '初始化失败');
		}
	}

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