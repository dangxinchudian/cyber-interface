<?php

$watch_id = $matches[1];

$serverModel = model('server');
$watch = $serverModel->selectWatch($watch_id);
if(empty($watch)) json(false, '监控不存在');
if($watch['remove'] > 0) json(false, '监控已经被移除');
$server = $serverModel->get($watch['server_id']);
if(empty($server)) json(false, 'server不存在');
if($server['remove'] > 0) json(false, 'server已经被移除');

//判断间隔
if($watch['last_watch_time'] + $server['period'] > time()) json(false, '间隔时间过短');

$db = $serverModel->db();
$snmp = model('snmpCatch');
$snmp->ip = $server['ip'];
if($server['snmp_version'] == 2) $snmp->community = jdecode($server['snmp_token']);
else json(false, 'can\'t support v3');

//device 
$item = $serverModel->item($watch['server_item_id']);
$table = "moserver_{$watch['server_id']}.{$item['table_name']}";
$device = $serverModel->getDevice($watch['server_id'], $item['server_hardware_id']);
$deviceList = array();
foreach ($device as $key => $value) {
	$deviceList[$value['hash']] = $value;
}
switch ($item['table_name']) {
	case 'disk_log':
		$result = $snmp->disk();
		if(!$result) json(false, 'snmp error!');
		$snmpDevice = array();
		foreach ($result as $key => $value) {
			$snmpDevice[$serverModel->device_hash($watch['server_id'], $item['server_hardware_id'], $value['name'])] = $value;
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
		foreach ($dataArray as $key => $value) {
			$dataArray[$key]['name'] = '';
		}

		break;

	case 'network_log':
		$result = $snmp->network();
		if(!$result) json(false, 'snmp error!');
		$snmpDevice = array();
		foreach ($result as $key => $value) {
			$snmpDevice[$serverModel->device_hash($watch['server_id'], $item['server_hardware_id'], $value['descr'].$value['physAddress'])] = $value;
		}
		$snmpDevice = array_intersect_key($snmpDevice, $deviceList);
		$deviceArray = array();
		foreach ($snmpDevice as $key => $value){
			$deviceArray[$deviceList[$key]['server_device_id']] = $value;
			$deviceArray[$deviceList[$key]['server_device_id']]['last'] = array();
		}

		$watchEle = $serverModel->selectWatch($watch_id);
		$last = jdecode($watchEle['last_watch_data']);
		if($last) foreach ($last as $key => $value) $deviceArray[$value['device_id']]['last'] = $value;
		// print_r($last);

		$sql = '';
		$time = date('Y-m-d H:i:s');
		foreach ($deviceArray as $key => $value) {
			if(empty($value['last'])){
				$sql .= " INSERT INTO {$table} (id, in_total, out_total, device_id, time) VALUES (uuid(), '{$value['inOctets']}', '{$value['outOctets']}', '{$key}', '{$time}'); ";
			}else{
				$delta = time() - $value['last']['time'];
				$in_speed = (int)(gmp_intval(gmp_add($value['inOctets'], "-{$value['last']['inOctets']}")) / $delta);
				$out_speed = (int)(gmp_intval(gmp_add($value['outOctets'], "-{$value['last']['outOctets']}")) / $delta);
				$sql .= " INSERT INTO {$table} (id, in_total, out_total, in_speed, out_speed, device_id, time) VALUES (uuid(), '{$value['inOctets']}', '{$value['outOctets']}', '{$in_speed}', '{$out_speed}', '{$key}', '{$time}'); ";
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
			$dataArray[$serverModel->device_hash($watch['server_id'], $item['server_hardware_id'], $value['descr'].$value['physAddress'])] = $value;
		}

		break;

	case 'cpu_log':
		$result = $snmp->cpu();
		if(!$result) json(false, 'snmp error!');

		$snmpDevice = array();
		foreach ($result as $key => $value) {
			$snmpDevice[$serverModel->device_hash($watch['server_id'], $item['server_hardware_id'], $key)] = $value;
		}
		$snmpDevice = array_intersect_key($snmpDevice, $deviceList);
		foreach ($snmpDevice as $key => $value){
			$snmpDevice[$key] = array();
			$snmpDevice[$key]['load'] = $value;
			$snmpDevice[$key]['device_id'] = $deviceList[$key]['server_device_id'];
		}
		
		$sql = "INSERT INTO {$table} (id, used, device_id, time) VALUES ";
		$sqlArray = array();
		$time = date('Y-m-d H:i:s');
		foreach ($snmpDevice as $key => $value) {
			$sqlArray[] = "(uuid(), '{$value['load']}', '{$value['device_id']}', '{$time}')";
		}
		$sql .= implode(',', $sqlArray);
		$dataArray = $snmpDevice;

		break;

	case 'memory_log':
		$result = $snmp->memory_total();
		if(!$result) json(false, 'snmp error!');
		$total = (int)$result;
		$result = $snmp->process();
		if(!$result) json(false, 'snmp error!');
		$used_memory = 0;
		foreach ($result as $key => $value) $used_memory += (int)$value['memory'];
		$time = date('Y-m-d H:i:s');
		$sql = " INSERT INTO {$table} (id, total_amount, used_amount, time) VALUES (uuid(), '{$total}', '{$used_memory}',  '{$time}'); ";
		$dataArray = array(
			'total' => $total,
			'used_memory' => $used_memory
		);

		break;

	case 'processcount_log':
		$result = $snmp->process();
		if(!$result) json(false, 'snmp error!');
		$count = count($result);
		$time = date('Y-m-d H:i:s');
		$sql = " INSERT INTO {$table} (id, amount, time) VALUES (uuid(), '{$count}',  '{$time}'); ";
		$dataArray = array(
			'count' => $count
		);
		break;

	default:
		json(false,'undefined mode!');
		break;
}

$db->query($sql, 'exec');
$update = array('last_watch_time' => time(), 'last_watch_data' => jencode($dataArray));
$db->update('server_watch', $update, "server_watch_id = '{$watch_id}'");
json(true, 'snmp catch finish!');

?>