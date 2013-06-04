<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$watch_id = filter('watch_id', '/^[0-9]{1,9}$/', 'watch_id格式错误', true);
	$server_id = filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误', true);
	$item_id = filter('item_id', '/^[0-9]{1,9}$/', 'item_id格式错误', true);
	
	$device_id = filter('device_id', '/^[0-9]{1,9}$/', 'device_id格式错误', 0);
	$time_unit = filter('time_unit', '/^day|month|year$/', '时间单位错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	// $watch_id = 6;
	// $time_unit = 'day';
	// $start_time = time() - 3600*24*5;
	// $stop_time = time();
	// $device_id = 16;

	$serverModel = model('server');
	if($watch_id != null) $watch = $serverModel->selectWatch($watch_id);
	else $watch = $serverModel->selectWatch($server_id, $item_id);

	if(empty($watch)) json(false, '监控不存在');
	if($watch['remove'] > 0) json(false, '监控已经被移除');
	if(!$admin) if($watch['user_id'] != $user_id) json(false, '不允许操作他人监控');

	$item = $serverModel->item($watch['server_item_id']);

	$result = $serverModel->log_data($watch['server_id'], $item['table_name'], $device_id, $time_unit, $start_time, $stop_time);

	$data = array();
	//date&data complete
	if($time_unit == 'day'){
		for($i = 0 ; $i<= ($stop_time - $start_time) / (3600*24); $i++){
			$data[date('Y-m-d', $start_time + 3600 * 24 * $i)] = 0;
		}
	}elseif($time_unit == 'month'){
		for($i = 0 ; $i<= ($stop_time - $start_time) / (3600*24*30); $i++){
			$data[date('Y-m', $start_time + 3600 * 24 * $i * 30)] = 0;		
		}
	}elseif($time_unit == 'year'){
		for($i = 0 ; $i<= ($stop_time - $start_time) / (3600*24*365); $i++){
			$data[date('Y', $start_time + 3600 * 24 * $i * 365)] = 0;
		}			
	}

	//print_r($item['table_name']);

	$ele = array();
	switch ($item['table_name']) {
		case 'processcount_log':
			$info = array('max_process' => $data, 'min_process' => $data, 'avg_process' => $data);
			foreach ($result as $key => $value){
				$info['max_process'][$value['group_time']] = $value['max_amount'];
				$info['min_process'][$value['group_time']] = $value['min_amount'];
				$info['avg_process'][$value['group_time']] = $value['avg_amount'];
			}

			$values = array();
			foreach ($info as $subkey => $subvalue) $values[$subkey] = array_values($subvalue);
			$values['device_id'] = 0;
			$ele[] = $values;
			
			break;
		
		case 'disk_log':
			$device = array();
			foreach ($result as $key => $value) $device[$value['device_id']][] = $value;
			foreach ($device as $key => $value) {
				$info = array('max_per' => $data, 'min_per' => $data, 'avg_per' => $data, 'max_used' => $data, 'min_used' => $data, 'avg_used' => $data, 'total_amount'=> $data);
				foreach ($value as $subvalue){
					$info['max_used'][$subvalue['group_time']] = $subvalue['max_amount'];
					$info['min_used'][$subvalue['group_time']] = $subvalue['min_amount'];
					$info['avg_used'][$subvalue['group_time']] = $subvalue['avg_amount'];
					$info['total_amount'][$subvalue['group_time']] = $subvalue['total_amount'];
					$info['max_per'][$subvalue['group_time']] = ($subvalue['total_amount'] != 0) ? round($subvalue['max_amount'] / $subvalue['total_amount'], 6) : 0;
					$info['min_per'][$subvalue['group_time']] = ($subvalue['total_amount'] != 0) ? round($subvalue['min_amount'] / $subvalue['total_amount'], 6) : 0;
					$info['avg_per'][$subvalue['group_time']] = ($subvalue['total_amount'] != 0) ? round($subvalue['avg_amount'] / $subvalue['total_amount'], 6) : 0;
				}
				
				$values = array();
				foreach ($info as $subkey => $subvalue) $values[$subkey] = array_values($subvalue);
				$values['device_id'] = $key;
				$ele[] = $values;

			}
			break;

		case 'network_log':
			$device = array();
			foreach ($result as $key => $value) $device[$value['device_id']][] = $value;
			foreach ($device as $key => $value) {
				$info = array('max_in_speed' => $data, 'min_in_speed' => $data, 'avg_in_speed' => $data, 'max_out_speed' => $data, 'min_out_speed' => $data, 'avg_out_speed' => $data);
				foreach ($value as $subvalue){
					$info['max_in_speed'][$subvalue['group_time']] = $subvalue['max_in_speed'];
					$info['min_in_speed'][$subvalue['group_time']] = $subvalue['min_in_speed'];
					$info['avg_in_speed'][$subvalue['group_time']] = $subvalue['avg_in_speed'];
					$info['max_out_speed'][$subvalue['group_time']] = $subvalue['max_out_speed'];
					$info['min_out_speed'][$subvalue['group_time']] = $subvalue['min_out_speed'];
					$info['avg_out_speed'][$subvalue['group_time']] = $subvalue['avg_out_speed'];
				}
				$values = array();
				foreach ($info as $subkey => $subvalue) $values[$subkey] = array_values($subvalue);
				$values['device_id'] = $key;
				$ele[] = $values;
			}
			break;


		case 'memory_log':
			$info = array('max_used' => $data, 'min_used' => $data, 'avg_used' => $data, 'total_amount'=> $data, 'max_per'=> $data, 'min_per'=> $data, 'avg_per'=>$data);
			foreach ($result as $key => $value){
				$info['max_used'][$value['group_time']] = $value['max_used'];
				$info['min_used'][$value['group_time']] = $value['min_used'];
				$info['avg_used'][$value['group_time']] = $value['avg_used'];
				$info['total_amount'][$value['group_time']] = $value['total_amount'];
				$info['max_per'][$value['group_time']] = ($value['total_amount'] != 0) ? round($value['max_used'] / $value['total_amount'], 6) * 100: 0;
				$info['min_per'][$value['group_time']] = ($value['total_amount'] != 0) ? round($value['min_used'] / $value['total_amount'], 6) * 100: 0;
				$info['avg_per'][$value['group_time']] = ($value['total_amount'] != 0) ? round($value['avg_used'] / $value['total_amount'], 6) * 100: 0;
			}

			$values = array();
			foreach ($info as $subkey => $subvalue) $values[$subkey] = array_values($subvalue);
			$values['device_id'] = 0;
			$ele[] = $values;
			break;

		case 'cpu_log':
			$device = array();
			foreach ($result as $key => $value) $device[$value['device_id']][] = $value;
			foreach ($device as $key => $value) {
				$info = array('max_per' => $data, 'min_per' => $data, 'avg_per' => $data);
				foreach ($value as $subvalue){
					$info['max_per'][$subvalue['group_time']] = $subvalue['max_per'];
					$info['min_per'][$subvalue['group_time']] = $subvalue['min_per'];
					$info['avg_per'][$subvalue['group_time']] = $subvalue['avg_per'];
				}
				$values = array();
				foreach ($info as $subkey => $subvalue) $values[$subkey] = array_values($subvalue);
				$values['device_id'] = $key;
				$ele[] = $values;
			}
			break;

		default:
			json(false, 'undefined item');
			break;
	}

	$return = array(
		'time' => array_keys($data),
		'data' => $ele
	);
	//print_r($result);
	json(true, $return);

?>