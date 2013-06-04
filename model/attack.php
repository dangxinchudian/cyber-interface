<?php

class attack extends model{

	public function severity($site_id, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$table = "mosite_{$site_id}.attack_log";
		$sql = "SELECT count(*),severity FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' GROUP BY severity";
		return $this->db()->query($sql, 'array');
	}

	public function ip_count($site_id, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$table = "mosite_{$site_id}.attack_log";
		$sql = "SELECT COUNT(DISTINCT client_ip) as count FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' ";
		$dbResult = $this->db()->query($sql, 'row');
		$result = $dbResult['count'];
		return $result;
	}

	public function total_count($site_id, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$table = "mosite_{$site_id}.attack_log";
		$sql = "SELECT COUNT(client_ip) as count FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' ";
		$dbResult = $this->db()->query($sql, 'row');
		$result = $dbResult['count'];
		return $result;
	}

	public function ip($site_id, $start_time, $stop_time, $start, $limit){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$table = "mosite_{$site_id}.attack_log";
		$sql = "SELECT count(*) AS count,client_ip,time,zh_region,zh_country,zh_city FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' GROUP BY client_ip ORDER BY count DESC LIMIT {$start},{$limit}";
		$result['list'] = $this->db()->query($sql, 'array');
		//$sql = "SELECT COUNT(1) FROM (SELECT client_ip FROM {$table} GROUP BY client_ip) AS g";
		$sql = "SELECT COUNT(DISTINCT client_ip) as count FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' ";
		$dbResult = $this->db()->query($sql, 'row');
		$result['total'] = $dbResult['count'];
		$sql = "SELECT COUNT(*) as count FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' ";
		$dbResult = $this->db()->query($sql, 'row');
		$result['count_total'] = $dbResult['count'];
		return $result;
	}

	public function locationZh($site_id, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		if(is_array($site_id)){
			$tableArray[] = array();
			$sqlList = array();
			foreach ($site_id as $key => $value){
				$table = "mosite_{$value}.attack_log";
				$sqlList[] = "SELECT count(*) AS count,zh_region FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' GROUP BY zh_region";
			}
			$sql = implode(' UNION ALL ', $sqlList);
			return $this->db()->query($sql, 'array');
		}else{
			$table = "mosite_{$site_id}.attack_log";
			$sql = "SELECT count(*) AS count,zh_region FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' GROUP BY zh_region";
			return $this->db()->query($sql, 'array');
		}	
	}

	public function mode($site_id, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		if(is_array($site_id)){
			$tableArray[] = array();
			$sqlList = array();
			foreach ($site_id as $key => $value){
				$table = "mosite_{$value}.attack_log";
				$sqlList[] = "SELECT count(*) AS count ,attack_type FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' GROUP BY attack_type";
			}
			$sql = implode(' UNION ALL ', $sqlList);
			return $this->db()->query($sql, 'array');
		}else{
			$table = "mosite_{$site_id}.attack_log";
			$sql = "SELECT count(*) AS count ,attack_type FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' GROUP BY attack_type ORDER BY count DESC";
			return $this->db()->query($sql, 'array');
		}
	}

	public function detail($site_id, $start_time, $stop_time, $start, $limit, $severityArray = array()){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		if(is_array($site_id)){
			$tableArray[] = array();
			$sqlList = array();
			$sqlCount = array();
			foreach ($site_id as $key => $value){
				if(!empty($severityArray)) $severity = " AND severity in ('".implode("','", $severityArray)."')";
				else $severity = '';
				$table = "mosite_{$value}.attack_log";
				$sqlList[] = "SELECT *,{$value} AS site_id FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' {$severity}";
				$sqlCount[] = "SELECT COUNT(client_ip) as count FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' {$severity}";
			}
			$sql = implode(' UNION ALL ', $sqlList);
			$sql .= " ORDER BY time DESC LIMIT {$start},{$limit}";
			$result['list'] = $this->db()->query($sql, 'array');
			$sql = implode(' UNION ALL ', $sqlCount);
			$count = $this->db()->query($sql, 'array');
			$result['total'] = 0;
			foreach ($count as $key => $value) $result['total'] = $result['total'] + $value['count'];
			return $result;
			
		}else{
			$table = "mosite_{$site_id}.attack_log";
			if(!empty($severityArray)) $severity = " AND severity in ('".implode("','", $severityArray)."')";
			else $severity = '';
			$sql = "SELECT * FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' {$severity} ORDER BY time DESC LIMIT {$start},{$limit}";
			$result['list'] = $this->db()->query($sql, 'array');
			$sql = "SELECT COUNT(client_ip) as count FROM {$table} WHERE time > '{$start_time}' AND time <= '{$stop_time}' {$severity}";
			$dbResult = $this->db()->query($sql, 'row');
			$result['total'] = $dbResult['count'];
			return $result;
		}
	}

	public function daily($site_id, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$type = '%Y%m%d';
		$sql = "SELECT count(client_ip) AS count,date_format(time,'{$type}') AS group_time FROM mosite_{$site_id}.attack_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' GROUP BY group_time ORDER BY group_time ASC";
		return $this->db()->query($sql, 'array');	
	}

	public function hour($site_id, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		if(is_array($site_id)){
			$type = '%Y%m%d %H';
			$tableArray[] = array();
			$sqlList = array();
			foreach ($site_id as $key => $value){
				$table = "mosite_{$value}.attack_log";
				$sqlList[] = "SELECT count(client_ip) AS count,date_format(time,'{$type}') AS group_time FROM $table WHERE time >= '{$start_time}' AND time <= '{$stop_time}' GROUP BY group_time";
			}
			$sql = implode(' UNION ALL ', $sqlList);
			return $this->db()->query($sql, 'array');
		}else{
			$type = '%Y%m%d %H';
			$sql = "SELECT count(client_ip) AS count,date_format(time,'{$type}') AS group_time FROM mosite_{$site_id}.attack_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' GROUP BY group_time ORDER BY group_time ASC";
			return $this->db()->query($sql, 'array');
		}
	}
}


?>