<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$item_id = filter('item_id', '/^[0-9]{1,9}$/', 'itemID格式错误');
	$server_id = filter('server_id', '/^[0-9]{1,9}$/', 'serverID格式错误');
	// $item_id = 1;
	// $server_id = 1;

	$serverModel = model('server');
	$snmpCatch = model('snmpCatch');

	$info = $serverModel->get($server_id);
	if(empty($info)) json(false, '服务器不存在');
	if($info['remove'] > 0) json(false, '服务器已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人服务器');
	$snmpCatch->ip = $info['ip'];
	//$snmpCatch->ip = '1.1.1.1';

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
	if($result > 0) json(true, '初始化成功');
	json(false, '初始化失败');



?>