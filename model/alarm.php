<?php
/*new*/
class alarm extends model{

	public function addRule($user_id, $site_id, $type, $max_limit = 0, $min_limit = 0, $keep_time = 300, $cool_down_time = 600, $notice_limit = 3){

		$insertArray = array(
			'user_id' => $user_id, 
			'site_id' => $site_id, 
			'type' => $type,
			'max_limit' => $max_limit,
			'min_limit' => $min_limit,
			'keep_time' => $keep_time,
			'cool_down_time' => $cool_down_time,
			'notice_limit' => $notice_limit
		);
		$result = $this->db()->insert('monitor.alarm_rule', $insertArray);
		if($result == 0) return false;
		return $this->db()->insertId();
	}

	public function selectRule($site_id, $type  = false){
		if($type === false){
			$sql = "SELECT * FROM alarm_rule WHERE site_id = '{$site_id}' AND remove = '0'";
			return $this->db()->query($sql, 'array');
		}else{
			$sql = "SELECT * FROM alarm_rule WHERE site_id = '{$site_id}' AND type = '{$type}' AND remove = '0'";
			return $this->db()->query($sql, 'row');
		}
	}

	public function getRule($rule_id){
		$sql = "SELECT * FROM alarm_rule WHERE alarm_rule_id = '{$rule_id}'";
		return $this->db()->query($sql, 'row');		
	}

	public function updateRule($rule_id, $updateArray){
		return $this->db()->update('alarm_rule', $updateArray, "alarm_rule_id = '{$rule_id}'");
	}

	public function alarmList($site_id, $id = 'site_id',$start_time, $stop_time, $start, $limit, $type = false){
		$f = array();
		$start_time = date('Y-m-d H:i:s', $start_time);
		$stop_time = date('Y-m-d H:i:s', $stop_time);
		$f[] = " time >= '{$start_time}' ";
		$f[] = " time <= '{$stop_time}' ";
		if($type !== false) $f[] = " type = '{$type}' ";
		if($id == 'site_id'){
			if($site_id != 0) $f[] = " site_id = '{$site_id}' ";
		}elseif($id == 'user_id'){
			$f[] = " user_id = '{$site_id}' ";
		}
		$f = implode(' AND ', $f);
		$sql = "SELECT * FROM alarm WHERE {$f} ORDER BY time DESC LIMIT {$start},{$limit}";
		// echo $sql;
		$result['list'] = $this->db()->query($sql, 'array');
		$sql = "SELECT count(id) FROM alarm WHERE {$f}";
		$dbResult = $this->db()->query($sql, 'row');
		$result['total'] = $dbResult['count(id)'];
		return $result;
	}

	public function triggerConstant($site_id, $http_code){
		//检查是否存在告警规则
		$sql = "SELECT site.domain,alarm_rule.min_limit,alarm_rule.keep_time,alarm_rule.cool_down_time,alarm_rule.notice_limit,user.day_notice_max,user.user_id,user.mail  FROM alarm_rule,user,site WHERE alarm_rule.site_id = '{$site_id}' AND alarm_rule.remove = 0 AND alarm_rule.type = 'constant' AND user.user_id = alarm_rule.user_id AND site.site_id = alarm_rule.site_id";
		$rule = $this->db()->query($sql, 'row');
		if(empty($rule)) return false;
		if($rule['min_limit'] == 0) return false;
		
		//检查是否触发告警规则
		$stop_time = date('Y-m-d H:i:s');
		$start_time = date('Y-m-d H:i:s', time() - $rule['keep_time']);
		$sql = "SELECT count(id) FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' AND status = '200'";
		$http200 = $this->db()->query($sql, 'row');
		$sql = "SELECT count(id) FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}'";
		$httptotal = $this->db()->query($sql, 'row');

		if($httptotal['count(id)'] == 0) return false;
		$available = $http200['count(id)'] / $httptotal['count(id)'] * 100;
		$alarm = true;
		if($available >= $rule['min_limit']) $alarm = false;
		echo $available;

		//检查最近的notice_limit条告警
		$sql = "SELECT time,status FROM alarm WHERE type = 'constant' AND site_id = '{$site_id}' ORDER BY time DESC LIMIT 0,{$rule['notice_limit']}";
		$recent = $this->db()->query($sql, 'array');
		$warning = 0;
		foreach ($recent as $value) if($value['status'] == 'warning') $warning++;

		if(!$alarm){
			//没有告警需要闭合或者告警已经闭合
			if(count($recent) > 0 && $recent[0]['status'] == 'normal') return false;
			if(count($recent) == 0) return false;
			else{		//发送正常告警
				if($http_code != 200) return false;
				send_mail($rule['mail'], "{$rule['domain']}恢复正常", "{$rule['domain']}恢复正常");

				$time = date('Y-m-d H:i:s');
				$sql = "INSERT INTO alarm
				(
					id, 
					site_id, 
					user_id, 
					time, 
					type, 
					msg, 
					status
				) VALUES (
					uuid(), 
					'$site_id', 
					'{$rule['user_id']}',
					'{$time}',
					'constant',
					'恢复正常',
					'normal'
				)";
				$this->db()->query($sql, 'exec');	
				return;
			}
		}

		//告警已达单次上限
		if($warning == $rule['notice_limit']) return false;

		//检查冷却时间
		if(count($recent) > 0 && $recent[0]['status'] == 'warning'){
			if($recent[0]['time'] + $rule['cool_down_time'] > time()) return false;	//未冷却
		}


		//检查今天发送条数
		$stop_time = date('Y-m-d H:i:s');
		$start_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d 0:0:0')));
		$sql = "SELECT count(time) FROM alarm WHERE user_id = '{$rule['user_id']}' AND time >= '{$start_time}' AND time <= '{$stop_time}' AND status = 'warning'";
		$day = $this->db()->query($sql, 'row');
		if($rule['day_notice_max']!= 0 && $day['count(time)'] >= $rule['day_notice_max']) return false;

		//	发送异常告警
		if($http_code == 200) return false;
		send_mail($rule['mail'], "{$rule['domain']}出现异常", "出现异常");
		$http_code = $http_code.' '.errorHeader($http_code);

		$time = date('Y-m-d H:i:s');
		$sql = "INSERT INTO alarm
		(
			id, 
			site_id, 
			user_id, 
			time, 
			type, 
			msg, 
			status
		) VALUES (
			uuid(), 
			'$site_id', 
			'{$rule['user_id']}',
			'{$time}',
			'constant',
			'{$http_code}',
			'warning'
		)";
		$this->db()->query($sql, 'exec');	
	}

	public function get($id){
		$sql = "SELECT * FROM alarm WHERE id = '{$id}'";
		return $this->db()->query($sql, 'row');
	}
}
?>