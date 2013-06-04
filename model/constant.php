<?php

/*new*/

class constant extends model{

	public function log_data($site_id, $time_unit, $start_time, $stop_time, $node = -1){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$typeArray = array(
			'year' => '%Y',
			'month' => '%Y-%m',
			'day' => '%Y-%m-%d'
		);
		if($node >= 0) $node = " AND constant_node_id = '{$node}' ";
		else $node = '';
		$sql = "SELECT count(id) AS log_count,date_format(time,'{$typeArray[$time_unit]}') AS group_time FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' {$node} GROUP BY group_time ORDER BY group_time ASC";
		$result_total = $this->db()->query($sql, 'array');
		$sql = "SELECT count(id) AS log_count,date_format(time,'{$typeArray[$time_unit]}') AS group_time FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' AND status = '200' {$node} GROUP BY group_time ORDER BY group_time ASC";
		$result_avail = $this->db()->query($sql, 'array');
		$result = array();
		foreach ($result_total as $key => $value) {
			$result[$value['group_time']] = $value;
			$result[$value['group_time']]['avail_count'] = 0;
		}
		foreach ($result_avail as $key => $value) $result[$value['group_time']]['avail_count'] = $value['log_count'];
		foreach ($result as $key => $value) {
			$result[$key]['available'] = round($value['avail_count'] / $value['log_count'] * 100, 2);
		}
		return $result;
	}

	public function log_work_time($site_id, $start_time, $stop_time, $period, $node = 0){		//work_time
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$node = " AND constant_node_id = '{$node}' ";
		$sql = "SELECT count(id) FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' {$node}";
		$result = $this->db()->query($sql, 'row');
		return $result['count(id)'] * $period;
	}

	public function log_fault_time($site_id, $start_time, $stop_time, $period, $node = 0){		//可用time
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$node = " AND constant_node_id = '{$node}' ";
		$sql = "SELECT count(id) FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' AND status != '200' {$node}";
		$result = $this->db()->query($sql, 'row');
		return $result['count(id)'] * $period;	
	}

	public function table_fault_time($site_id, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$sql = "SELECT sum(keep_time) FROM constant_fault WHERE time >= '{$start_time}' AND time <= '{$stop_time}' AND site_id = '{$site_id}'";
		$result = $this->db()->query($sql, 'row');
		if(empty($result['sum(keep_time)'])) return 0;
		return $result['sum(keep_time)'];	
	}

	// public function available_time($site_id, $start_time, $stop_time){
		
	// }

	public function available($site_id, $start_time, $stop_time, $node = -1){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		if($node >= 0) $node = " AND constant_node_id = '{$node}' ";
		else $node = '';
		$sql = "SELECT count(id) FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' AND status = '200' {$node}";
		$result = $this->db()->query($sql, 'row');
		$avail_count = $result['count(id)'];

		$sql = "SELECT count(id) FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' {$node}";
		$result = $this->db()->query($sql, 'row');
		$total_count = $result['count(id)'];
		if($total_count == 0) return 0;
		return round($avail_count / $total_count * 100, 2);
	}

	public function get_last($site_id, $node = 0){
		$node = " constant_node_id = '{$node}' ";
		$sql = "SELECT * FROM mosite_{$site_id}.constant_log WHERE {$node} ORDER BY time DESC LIMIT 1";
		return $this->db()->query($sql, 'row');
	}

	public function node(){
		$sql = "SELECT constant_node_id,name FROM constant_node";
		return $this->db()->query($sql, 'array');
	}

	public function fault($site_id, $start_time, $stop_time, $start, $limit){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		if(is_array($site_id)){
			$site_id = implode(',', $site_id);
			$site = " site_id in ({$site_id})";
		}else{
			$site = " site_id = '{$site_id}'";
		}
		$sql = "SELECT * FROM constant_fault WHERE {$site} AND time >= '{$start_time}' AND time <= '{$stop_time}' ORDER BY time DESC LIMIT {$start},{$limit}";
		$result['list'] = $this->db()->query($sql, 'array');
		$sql = "SELECT count(id) FROM constant_fault WHERE site_id = '{$site_id}' AND time >= '{$start_time}' AND time <= '{$stop_time}'";
		$dbResult = $this->db()->query($sql, 'row');
		$result['total'] = $dbResult['count(id)'];
		return $result;
	}

	public function faultCount($site_id, $start_time, $stop_time){
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$sql = "SELECT count(id) FROM constant_fault WHERE site_id = '{$site_id}' AND time >= '{$start_time}' AND time <= '{$stop_time}'";
		$dbResult = $this->db()->query($sql, 'row');
		$result = $dbResult['count(id)'];
		return $result;	
	}


}
?>