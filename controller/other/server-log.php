<?php

$json = filter('json', '/^.+$/', 'JSON字符格式错误');
// $json = '{"host_token":"653988","type":"processcount","data":20}';

$json = json_decode($json, true);
if(!$json) json(false, 'JSON字符串无法解析');

$serverModel = model('server');
$db = $serverModel->db();
$server = $serverModel->get($json['host_token'], 'ip');
if(empty($server)) json(false, '服务器不存在');
if($server['remove'] > 0) json(false, '服务器已经被移除');


switch ($json['type']) {
	case 'sys':
		$data = $json['data'];
		$result = array();

		$result['os'] = 'linux';
		if(stristr($data[1], 'windows')) $result['os'] = 'windows';
		if(stristr($data[1], 'linux')) $result['os'] = 'linux';
		if(stristr($data[1], 'unix')) $result['os'] = 'linux';

		$updateArray = array(
			'os' => $result['os'],
			'sys_descr' => jencode($data[1]),
			'sys_name' => jencode($data[0]),
			'sys_uptime' => jencode($data[2]),
		);

		$serverModel->update($server['server_id'], $updateArray);
		json(true, '存放成功');
		break;
	
	case 'disk':
		$item_id = 1;

		$item = $serverModel->item($item_id);
		if(empty($item)) json(false, '监控项目不存在');
		$data = $json['data'];

		$table = "moserver_{$server['server_id']}.{$item['table_name']}";
		$watch = $serverModel->selectWatch($server['server_id'], $item_id);

		if(empty($watch)) $result = $serverModel->addWatch($server['server_id'], $item_id, $item['table_name'], $server['user_id']);

		$device = $serverModel->getDevice($server['server_id'], $item['server_hardware_id']);
		$deviceList = array();
		foreach ($device as $key => $value) $deviceList[$value['hash']] = $value;

		foreach ($data as $key => $value){
			$snmpDevice[$serverModel->device_hash($server['server_id'], $item['server_hardware_id'], $value[0])] = array(
					'name' => $value[0],
					'used' => $value[1],
					'total' => $value[2]
				);
		}

		if(count($snmpDevice) != count($deviceList)){
			$device = array();
			foreach ($data as $key => $value) {
				$device[] = array(
					'hash' => $serverModel->device_hash($server['server_id'], $item['server_hardware_id'], $value[0]),
					'name' => jencode(str2utf8($value[0]))
				);
				
			}
			$serverModel->setDevice($server['user_id'], $server['server_id'], $item['server_hardware_id'], $device);

			//重新再获取一次
			$device = $serverModel->getDevice($server['server_id'], $item['server_hardware_id']);
			$deviceList = array();
			foreach ($device as $key => $value) $deviceList[$value['hash']] = $value;
		}

		$snmpDevice = array_intersect_key($snmpDevice, $deviceList);
		foreach ($snmpDevice as $key => $value) $snmpDevice[$key]['device_id'] = $deviceList[$key]['server_device_id'];
		$sql = "INSERT INTO {$table} (id, used_amount, total_amount, device_id, time) VALUES ";
		$sqlArray = array();
		$time = date('Y-m-d H:i:s');
		foreach ($snmpDevice as $key => $value) {
			$sqlArray[] = "(uuid(), '{$value['used']}', '{$value['total']}', '{$value['device_id']}', '{$time}')";
		}
		$sql .= implode(',', $sqlArray);
		$dataArray = $snmpDevice;
		foreach ($dataArray as $key => $value){
			$dataArray[$key]['name'] = '';
		}

		break;

	case 'cpu':
		$item_id = 3;

		$item = $serverModel->item($item_id);
		if(empty($item)) json(false, '监控项目不存在');
		$data = $json['data'];

		$watch = $serverModel->selectWatch($server['server_id'], $item_id);
		$table = "moserver_{$server['server_id']}.{$item['table_name']}";

		if(empty($watch)) $result = $serverModel->addWatch($server['server_id'], $item_id, $item['table_name'], $server['user_id']);

		$device = $serverModel->getDevice($server['server_id'], $item['server_hardware_id']);
		$deviceList = array();
		foreach ($device as $key => $value) $deviceList[$value['hash']] = $value;

		foreach ($data as $key => $value) $snmpDevice[$serverModel->device_hash($server['server_id'], $item['server_hardware_id'], $key)] = $value;

		if(count($snmpDevice) != count($deviceList)){
			$device = array();
			foreach ($data as $key => $value) {
				$device[] = array(
					'hash' => $serverModel->device_hash($server['server_id'], $item['server_hardware_id'], $key),
					'name' => jencode("处理器{$key}")
				);
				
			}
			$serverModel->setDevice($server['user_id'], $server['server_id'], $item['server_hardware_id'], $device);

			//重新再获取一次
			$device = $serverModel->getDevice($server['server_id'], $item['server_hardware_id']);
			$deviceList = array();
			foreach ($device as $key => $value) $deviceList[$value['hash']] = $value;
		}

		$snmpDevice = array_intersect_key($snmpDevice, $deviceList);
		foreach ($snmpDevice as $key => $value){
			$snmpDevice[$key] = array();
			if($value < 0) $value = 0;
			$snmpDevice[$key]['load'] = $value;
			$snmpDevice[$key]['device_id'] = $deviceList[$key]['server_device_id'];
		}

		$sql = "INSERT INTO {$table} (id, used, device_id, time) VALUES ";
		$sqlArray = array();
		$time = date('Y-m-d H:i:s');
		foreach ($snmpDevice as $key => $value) $sqlArray[] = "(uuid(), '{$value['load']}', '{$value['device_id']}', '{$time}')";
		$sql .= implode(',', $sqlArray);

		$dataArray = $snmpDevice;

		break;

	case 'network':
		$item_id = 2;

		$item = $serverModel->item($item_id);
		if(empty($item)) json(false, '监控项目不存在');
		$data = $json['data'];

		$watch = $serverModel->selectWatch($server['server_id'], $item_id);
		$table = "moserver_{$server['server_id']}.{$item['table_name']}";

		if(empty($watch)) $result = $serverModel->addWatch($server['server_id'], $item_id, $item['table_name'], $server['user_id']);

		$device = $serverModel->getDevice($server['server_id'], $item['server_hardware_id']);
		$deviceList = array();
		foreach ($device as $key => $value) $deviceList[$value['hash']] = $value;

		foreach ($data as $key => $value) $snmpDevice[$serverModel->device_hash($server['server_id'], $item['server_hardware_id'], $value[0])] = array(
				'name' => $value[0],
				'in' => $value[1],
				'out' => $value[2]
			);

		if(count($snmpDevice) != count($deviceList)){
			$device = array();
			foreach ($data as $key => $value) {
				$device[] = array(
					'hash' => $serverModel->device_hash($server['server_id'], $item['server_hardware_id'], $value[0]),
					'name' => jencode(str2utf8($value[0]))
				);
				
			}
			$serverModel->setDevice($server['user_id'], $server['server_id'], $item['server_hardware_id'], $device);

			//重新再获取一次
			$device = $serverModel->getDevice($server['server_id'], $item['server_hardware_id']);
			$deviceList = array();
			foreach ($device as $key => $value) $deviceList[$value['hash']] = $value;
		}

		$snmpDevice = array_intersect_key($snmpDevice, $deviceList);
		$deviceArray = array();
		foreach ($snmpDevice as $key => $value){
			$deviceArray[$deviceList[$key]['server_device_id']] = $value;
			$deviceArray[$deviceList[$key]['server_device_id']]['last'] = array();
		}

		$watchEle = $serverModel->selectWatch($watch['server_watch_id']);
		$last = jdecode($watchEle['last_watch_data']);
		if($last) foreach ($last as $key => $value){
			if(isset($deviceArray[$value['device_id']])) $deviceArray[$value['device_id']]['last'] = $value;
		}
		// print_r($last);
		//print_r($deviceArray);

		$sql = '';
		$time = date('Y-m-d H:i:s');
		foreach ($deviceArray as $key => $value) {
			if(empty($value['last'])){
				$sql .= " INSERT INTO {$table} (id, in_total, out_total, device_id, time) VALUES (uuid(), '{$value['in']}', '{$value['out']}', '{$key}', '{$time}'); ";
			}else{
				$delta = time() - $value['last']['time'];
				$in_speed = (int)(gmp_intval(gmp_add($value['in'], "-{$value['last']['in']}")) / $delta);
				if($in_speed < 0) $in_speed = 0;
				$out_speed = (int)(gmp_intval(gmp_add($value['out'], "-{$value['last']['out']}")) / $delta);
				if($out_speed < 0) $out_speed = 0;
				$sql .= " INSERT INTO {$table} (id, in_total, out_total, in_speed, out_speed, device_id, time) VALUES (uuid(), '{$value['in']}', '{$value['out']}', '{$in_speed}', '{$out_speed}', '{$key}', '{$time}'); ";
				// echo $in_speed;
			}
			$deviceArray[$key]['in_speed'] = (isset($in_speed)) ? $in_speed : 0;
			$deviceArray[$key]['out_speed'] = (isset($out_speed)) ? $out_speed : 0;
			$deviceArray[$key]['time'] = time();
			$deviceArray[$key]['device_id'] = $key;
			unset($deviceArray[$key]['last']);
		}

		$dataArray = array();
		// $dataArray = $deviceArray;
		foreach ($deviceArray as $key => $value) {
			$dataArray[$serverModel->device_hash($watch['server_id'], $item['server_hardware_id'], $value['name'])] = $value;
		}

		break;

	case 'memory':
		$item_id = 5;


		$item = $serverModel->item($item_id);
		if(empty($item)) json(false, '监控项目不存在');
		$data = $json['data'];

		$watch = $serverModel->selectWatch($server['server_id'], $item_id);
		$table = "moserver_{$server['server_id']}.{$item['table_name']}";

		if(empty($watch)) $result = $serverModel->addWatch($server['server_id'], $item_id, $item['table_name'], $server['user_id']);

		$time = date('Y-m-d H:i:s');
		$sql = " INSERT INTO {$table} (id, total_amount, used_amount, time) VALUES (uuid(), '{$data[1]}', '{$data[0]}',  '{$time}'); ";
		$dataArray = array(
			'total' => $data[1],
			'used_memory' => $data[0]
		);
		break;

	case 'processcount':
		$item_id = 6;


		$item = $serverModel->item($item_id);
		if(empty($item)) json(false, '监控项目不存在');
		$data = $json['data'];

		$watch = $serverModel->selectWatch($server['server_id'], $item_id);
		$table = "moserver_{$server['server_id']}.{$item['table_name']}";

		if(empty($watch)) $result = $serverModel->addWatch($server['server_id'], $item_id, $item['table_name'], $server['user_id']);

		$time = date('Y-m-d H:i:s');
		$sql = " INSERT INTO {$table} (id, amount, time) VALUES (uuid(), '{$data}',  '{$time}'); ";
		$dataArray = array('count' => $data);

		break;


	default:
		json(false, '未记录数据');
		break;
		
}
// echo $sql;

$db->query($sql, 'exec');
$update = array('last_watch_time' => time(), 'last_watch_data' => jencode($dataArray));
$db->update('server_watch', $update, "server_watch_id = '{$watch['server_watch_id']}'");
json(true, '数据记录成功');


?>