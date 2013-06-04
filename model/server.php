<?php
/*new*/
class server extends model{

	public function add($ip, $user_id, $custom_name = '', $period = 60){
		$insertArray = array(
			'ip' => $ip, 
			'user_id' => $user_id,
			'creat_time' => time(),
			'custom_name' => $custom_name,
			'period' => $period,
			'snmp_token' => jencode('public')
		);
		$result = $this->db()->insert('server', $insertArray);
		if($result == 0) return false;
		$id = $this->db()->insertId();

		$sql = "CREATE DATABASE `moserver_{$id}` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		$this->db()->query($sql, 'exec');
		
		return $id;
	}

	public function update($server_id, $updateArray){
		return $this->db()->update('server', $updateArray, "server_id = '{$server_id}'");
	}

	public function get($value, $type = 'server_id'){
		if(!($value == 0 && $type == 'user_id')){
			$whereArray = array(
				'server_id' => " server_id = '{$value}' ",
				'ip' => " ip = '{$value}' AND remove = 0 ",
				'user_id' => " user_id = '{$value}' AND remove = 0"
			);
			$where = $whereArray[$type];
		}else $where = ' remove = 0 ';
		$sql = "SELECT * FROM server WHERE {$where} ORDER BY creat_time ASC LIMIT 1";
		return $this->db()->query($sql, 'row');

		
		// $whereArray = array(
		// 	'server_id' => " server_id = '{$value}' ",
		// 	'ip' => " ip = '{$value}' AND remove = 0 ",
		// 	'user_id' => " user_id = '{$value}' AND remove = 0"
		// );
		// $sql = "SELECT * FROM server WHERE {$whereArray[$type]} ORDER BY creat_time ASC LIMIT 1";
		// return $this->db()->query($sql, 'row');
	}

	public function remove($server_id, $destroy = false){
		if($destroy){
			$sql = "DROP DATABASE `moserver_{$server_id}`;";
			//$sql .= "DROP DATABASE `mosite_{$site_id}`;";
			$this->db()->query($sql, 'exec');
			$updateArray = array('remove' => 2);
			$result = $this->update($server_id, $updateArray);

			//remove watch
			$updateArray = array('remove' => 1);
			$this->db()->update('server_watch', $updateArray, "server_id = '{$server_id}'");

			//remove device
			$this->db()->update('server_device', $updateArray, "server_id = '{$server_id}'");

			//remove the site link
			$this->db()->update('site', array('server_id' => 0), "server_id = '{$server_id}'");

			return true;
		}else{
			$updateArray = array('remove' => 1);
			$result = $this->update($server_id, $updateArray);
			if($result > 0) return true;
		}
		return false;
		//$this->db()->checkSchema($schema);
	}

	public function log_data($server_id, $table_name, $device_id = 0, $time_unit, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$typeArray = array(
			'year' => '%Y',
			'month' => '%Y-%m',
			'day' => '%Y-%m-%d'
		);
		if($device_id != 0) $device = " AND device_id = '{$device_id}' ";
		else $device = '';
		$tableArray = array(
			'processcount_log' => "SELECT max(amount) AS max_amount,min(amount) AS min_amount,avg(amount) AS avg_amount,date_format(time,'{$typeArray[$time_unit]}') AS group_time FROM moserver_{$server_id}.processcount_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' GROUP BY group_time ORDER BY group_time ASC",

			'network_log' => "SELECT device_id,max(in_speed) AS max_in_speed,min(in_speed) AS min_in_speed,avg(in_speed) AS avg_in_speed,max(out_speed) AS max_out_speed,min(out_speed) AS min_out_speed,avg(out_speed) AS avg_out_speed,date_format(time,'{$typeArray[$time_unit]}') AS group_time FROM moserver_{$server_id}.network_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' {$device} GROUP BY group_time,device_id",

			'cpu_log' => "SELECT device_id,max(used) AS max_per,min(used) AS min_per,avg(used) AS avg_per,date_format(time,'{$typeArray[$time_unit]}') AS group_time FROM moserver_{$server_id}.cpu_log WHERE time >= '{$start_time}' {$device} AND time <= '{$stop_time}' GROUP BY group_time,device_id",

			'memory_log' => "SELECT total_amount,max(used_amount) AS max_used,min(used_amount) AS min_used,avg(used_amount) AS avg_used,date_format(time,'{$typeArray[$time_unit]}') AS group_time FROM moserver_{$server_id}.memory_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' GROUP BY group_time ORDER BY group_time ASC",

			'disk_log' => "SELECT device_id,total_amount,max(used_amount) AS max_amount,min(used_amount) AS min_amount,avg(used_amount) AS avg_amount,date_format(time,'{$typeArray[$time_unit]}') AS group_time FROM moserver_{$server_id}.disk_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' {$device} GROUP BY group_time,device_id"

		);
		if(!isset($tableArray[$table_name])) return false;
		return $this->db()->query($tableArray[$table_name], 'array');		
	}

	public function serverList($user_id, $start, $limit, $remove = 0){		//1:remove,0:normal,-1:all
		$f = array();
		if($remove >= 0) $f[] = " remove = '{$remove}' ";
		if($user_id > 0) $f[] = " user_id = '{$user_id}' ";
		$f = implode(' AND ', $f);
		// else $remove = '';
		$sql = "SELECT * FROM server WHERE {$f} LIMIT {$start},{$limit}";
		return $this->db()->query($sql, 'array');
	}

	public function serverCount($user_id, $remove = 0){
		$f = array();
		if($remove >= 0) $f[] = " remove = '{$remove}' ";
		if($user_id > 0) $f[] = " user_id = '{$user_id}' ";
		$f = implode(' AND ', $f);
		// if($remove >= 0) $remove = ' AND remove = \'{$remove}\'';
		// else $remove = '';
		$sql = "SELECT count(server_id) FROM server WHERE {$f} ";
		$result = $this->db()->query($sql, 'row');
		return $result['count(server_id)'];
	}

	public function setDevice($user_id, $server_id, $hardware_id, $deviceArray){		//设备数组-注册设备
		$this->db()->update('server_device', array('remove' => 1), " server_id = '{$server_id}' AND server_hardware_id = '{$hardware_id}' ");		//remove all
		//$result = $this->db()->query($sql, 'array');
		foreach ($deviceArray as $key => $value) {
			$sql = "SELECT * FROM server_device WHERE hash = '{$value['hash']}' AND server_id = '{$server_id}' AND server_hardware_id = '{$hardware_id}'";
			$result = $this->db()->query($sql, 'row');
			if(empty($result)){
				$insertArray = array(
					'server_id' => $server_id,
					'user_id' => $user_id,
					'server_hardware_id' => $hardware_id,
					'hash' => $value['hash'],
					'value' => $value['name']
				);
				$this->db()->insert('server_device', $insertArray);
			}else{
				$this->db()->update('server_device', array('remove' => 0), " server_id = '{$server_id}' AND server_hardware_id = '{$hardware_id}' AND hash = '{$value['hash']}'");	
			}
		}
	}

	// public function getDevice($value, $type = 'device_id'){
	// 	if($type == 'device_id'){
	// 		$sql = "SELECT * FROM server_device WHERE server_device_id = '{$value}'";
	// 		return $this->db()->query($sql, 'row');
	// 	}else{
	// 		$sql = "SELECT * FROM server_device WHERE server_hardware_id = '{$value}' AND remove = 0";
	// 		return $this->db()->query($sql, 'array');
	// 	}
	// }

	public function getDevice($server_id, $hardware_id = false){
		if($hardware_id === false){
			$sql = "SELECT * FROM server_device WHERE server_device_id = '{$server_id}'";
			return $this->db()->query($sql, 'row');
		}else{
			$sql = "SELECT * FROM server_device WHERE server_hardware_id = '{$hardware_id}' AND remove = 0 AND server_id = '{$server_id}'";
			return $this->db()->query($sql, 'array');
		}
	}	

	public function item($item_id = false){
		if($item_id){
			$sql = "SELECT * FROM server_item WHERE server_item_id = '{$item_id}' AND remove = 0";
			return $this->db()->query($sql, 'row');
		}else{
			$sql = "SELECT * FROM server_item WHERE remove = 0";
			return $this->db()->query($sql, 'array');
		}
	}

	public function hardware($hardware_id){
		$sql = "SELECT * FROM server_hardware WHERE server_hardware_id = '{$hardware_id}' ";
		return $this->db()->query($sql, 'row');		
	}

	public function checkTable($database, $table){
		$sql = "SELECT TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='{$database}' and TABLE_NAME='{$table}'";
		$result = $this->db()->query($sql, 'row');
		if(empty($result)) return false;
		return true;
	}

	public function partitionSql(){		//生成分区表的sql语句
		$sql = 'PARTITION BY RANGE (TO_DAYS (time))(';
		$year = date('Y');
		$month = date('m');
		//生成之后6个月的分区
		$date = array();
		$sqlArray = array();
		for ($i = 0; $i < 6; $i++) { 
			if($month + $i > 12){
				$date[] = array(
					'year' => $year + 1,
					'month' => str_pad($month + $i - 12, 2 ,'0', STR_PAD_LEFT),
				);
			}else{
				$date[] = array(
					'year' => $year,
					'month' => str_pad($month + $i, 2 ,'0', STR_PAD_LEFT),
				);
			}
		}
		for ($i = 1; $i < 6; $i++) {
			$p = $i - 1;
			$sqlArray[] = "PARTITION p{$date[$p]['year']}{$date[$p]['month']} VALUES LESS THAN (TO_DAYS('{$date[$i]['year']}-{$date[$i]['month']}-01')) ENGINE = ARCHIVE";
		}
		$sql .= implode(',', $sqlArray);
		$sql .= ')';
		return $sql;
	}

	public function device_hash($server_id, $hardware_id, $name){
		return md5('seconme@'.$server_id.$hardware_id.$name);
	}

	public function addWatch($server_id, $item_id, $tablename, $user_id){
		$table = $this->checkTable("moserver_{$server_id}", $tablename);
		if(!$table){
			$sql = "SELECT * FROM server_item_field WHERE server_item_id = '{$item_id}'";
			$fields = $this->db()->query($sql, 'array');
			$fieldsql = array();
			foreach ($fields as $key => $value) {
				if(!empty($value['var_length'])) $length = "({$value['var_length']})";
				else $length = '';
				$fsql = "`{$value['var_name']}` {$value['var_type']}{$length} {$value['var_signed']} {$value['var_null']}";
				if(!empty($value['var_default'])) $fsql .= " DEFAULT '{$value['var_default']}' ";
				if(!empty($value['var_comment'])) $fsql .= " COMMENT '{$value['var_comment']}' ";
				$fieldsql[] = $fsql;
			}
			$tablesql = "USE moserver_{$server_id}; CREATE TABLE IF NOT EXISTS `{$tablename}` ( ";
			$tablesql .= implode(',', $fieldsql);
			$tablesql .= ') ENGINE=ARCHIVE DEFAULT CHARSET=utf8 ';
			$tablesql .= $this->partitionSql();
			$this->db()->query($tablesql, 'exec');
			$this->db()->query("USE monitor;", 'exec');
		}
		$sql = "SELECT * FROM server_watch WHERE server_item_id = '{$item_id}' AND server_id = '{$server_id}' AND remove = 0";
		$watch = $this->db()->query($sql, 'row');
		if(empty($watch)){
			$insertArray = array(
				'server_id' => $server_id,
				'user_id' => $user_id,
				'server_item_id' => $item_id,
				'creat_time' => time(),
				'update_time' => time()
			);
			$this->db()->insert('server_watch', $insertArray);
			return $this->db()->insertId();			
		}else{
			$updateArray = array(
				'update_time' => time()
			);
			$result = $this->db()->update('server_watch', $updateArray, " server_watch_id = '{$watch['server_watch_id']}' ");
			if($result > 0) return $watch['server_watch_id'];
			else return false;
		}
	}

	public function selectWatch($server_id, $item_id = false){
		if($item_id === false){
			$sql = "SELECT * FROM server_watch WHERE server_watch_id = '{$server_id}'";
		}else{
			$sql = "SELECT * FROM server_watch WHERE server_item_id = '{$item_id}' AND server_id = '{$server_id}' AND remove = 0";
		}
		return $this->db()->query($sql, 'row');		
	}

	public function listWatch($server_id){
		$sql = "SELECT * FROM server_watch WHERE server_id = '{$server_id}' AND remove = 0";
		return $this->db()->query($sql, 'array');	
	}

	// public function lastWatch($watch_id){
	// 	// $tableResult = $this->checkTable("moserver_{$server_id}", $table);
	// 	// if(!$tableResult) return array();
	// 	// $start_time = date('Y-m-d H:i:s', strtotime('-2 month'));
	// 	// $stop_time = date('Y-m-d H:i:s');
	// 	// if(empty($device_id)){
	// 	// 	$sql = "SELECT * FROM moserver_{$server_id}.{$table} WHERE time > '{$start_time}' AND time < '{$stop_time}' ORDER BY time DESC LIMIT 0,1";
	// 	// 	return $this->db()->query($sql, 'row');	
	// 	// }else{
	// 	// 	$result = array();
	// 	// 	foreach ($device_id as $key => $value) {
	// 	// 		$sql = "SELECT * FROM moserver_{$server_id}.{$table} WHERE time > '{$start_time}' AND time < '{$stop_time}' AND device_id = '{$value}' ORDER BY time DESC LIMIT 0,1";
	// 	// 		//echo $sql;
	// 	// 		$row = $this->db()->query($sql, 'row');
	// 	// 		if(!empty($row)) $result[] = $row;
	// 	// 	}
	// 	// 	return $result;
	// 	// }
	// }
	/*public function itemSql($item){
		$array = array(
			'cpu' => "CREATE TABLE IF NOT EXISTS `cpu_log` ( `id` char(36) NOT NULL, `used` tinyint(3) unsigned NOT NULL COMMENT '使用百分比', `device_id` int(10) unsigned NOT NULL COMMENT '设备ID', `time` datetime NOT NULL );",
			'memory' => "CREATE TABLE IF NOT EXISTS `memory_log` ( `id` char(36) NOT NULL, `used_amount` int(10) unsigned NOT NULL COMMENT '使用量', `total_amount` int(10) unsigned NOT NULL COMMENT '总量', `device_id` int(10) unsigned NOT NULL COMMENT '设备ID', `time` datetime NOT NULL );",
			'processcount' => "CREATE TABLE IF NOT EXISTS `processcount_log` ( `id` char(36) NOT NULL, `amount` int(10) unsigned NOT NULL COMMENT '数量', `time` datetime NOT NULL );",
			'disk' => "CREATE TABLE IF NOT EXISTS `disk_log` ( `id` char(36) NOT NULL, `used_amount` int(10) unsigned NOT NULL COMMENT '使用量', `total_amount` int(10) unsigned NOT NULL COMMENT '总量', `device_id` int(10) unsigned NOT NULL COMMENT '设备ID', `time` datetime NOT NULL );",
			'network' => "CREATE TABLE IF NOT EXISTS `network_log` ( `id` char(36) NOT NULL, `in` int(10) unsigned NOT NULL COMMENT '流入流量', `out` int(10) unsigned NOT NULL COMMENT '流出流量', `device_id` int(10) unsigned NOT NULL COMMENT '设备ID', `time` datetime NOT NULL );",
		);
		if(!isset($array[$item])) return false;
		return $array[$item];
	}*/


}
?>