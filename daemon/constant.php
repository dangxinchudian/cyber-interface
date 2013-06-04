<?php

date_default_timezone_set('PRC');

require('../database.php');
$db = new database;
$db->exception(false);

require('../common.php');       //common function
require('../phpmailer/class.phpmailer.php');
/*for(;;){

}*/
$callback = function($data, $info, $self){
	global $db;
	$database = "mosite_{$self['site_id']}";
	$time = date('Y-m-d H:i:s');
	if($self['node_id'] != 0){
		$remote = json_decode($data, true);
		if(!$remote) return false;
		$time = $remote['time'];
		$info = array_merge($info, $remote);
	}
	$sql = "INSERT INTO {$database}.constant_log 
	(
		id, 
		starttransfer_time, 
		pretransfer_time, 
		total_time, 
		namelookup_time, 
		connect_time, 
		redirect_time, 
		status,
		constant_node_id, 
		time
	) VALUES (
		uuid(), 
		'{$info['starttransfer_time']}', 
		'{$info['pretransfer_time']}',
		'{$info['total_time']}',
		'{$info['namelookup_time']}',
		'{$info['connect_time']}',
		'{$info['redirect_time']}',
		'{$info['http_code']}',
		'{$self['node_id']}',
		'{$time}'
	)";
	$db->query($sql, 'exec');
	$time = time();

	if($self['node_id'] != 0){
		$sql = "UPDATE monitor.site SET last_watch_time = '{$time}' WHERE site.site_id = '{$self['site_id']}'"; 
	}else{
		//距离上次访问时间超过2倍的间隔时间就算中间停止过，重新计算，否则进行粘滞计算(time()-last_watch_time)
		$middle_time = $time - $self['last_watch_time'];
		if($middle_time > 2 * $self['period']) $middle_time = 0;

		$sql = "UPDATE monitor.site SET last_watch_time = '{$time}',keep_watch_time = keep_watch_time + {$middle_time} WHERE site.site_id = '{$self['site_id']}'";
	}
	$db->query($sql, 'exec');
	//echo "{$self['site_id']} : {$info['url']}   {$info['total_time']}\n";


	if($self['node_id'] == 0){
		// ----------------------------------------------------------
		// -----------------------fault-----------------------------
		// ----------------------------------------------------------

		//检查未闭合的故障
		$sql = "SELECT * FROM constant_fault WHERE site_id = '{$self['site_id']}' AND status = 'unslove' ORDER BY time DESC";
		$result = $db->query($sql, 'array');
		$fault = array();
		if(!empty($result)) $fault = array_shift($result);

		//正常情况下最多只会有一个unslove
		if(count($result) > 0){     //如果出现多个。除了第一个其他全部闭合
			$fault_array = array();
			foreach ($result as $key => $value) $fault_array[] = $value['id'];
			$fault_where = implode(',', $fault_array);
			$updateArray = array('status' => 'slove');
			$db->update('constant_fault', $updateArray, "id in ({$fault_where})");
			echo '闭合修复\n';
		}

		//$fault_id
		if($info['http_code'] != 200){      //开启故障，持续故障
		if(empty($fault)){      //开启故障

			//再次检查
			for($i = 0; $i < 2; $i++){
			$result = httpHeader($self['url'], $self['port']);
			if($result['code'] == 200) return;      //若为200直接退出
			}

			$insertArray = array(
			'time' => date('Y-m-d H:i:s'),
			'keep_time' => $self['period'],
			'user_id' => $self['user_id'],
			'site_id' => $self['site_id'],
			'http_code' => $info['http_code']
			);

			//print_r($info);
			$result = $db->insert('constant_fault', $insertArray);

		}else{      //持续故障时间累加
			$sql = "UPDATE constant_fault SET keep_time = keep_time + {$self['period']} WHERE id = '{$fault['id']}'";
			$db->query($sql, 'exec');
		}
		}else{   //闭合故障
			if(!empty($fault)){
				$updateArray = array('status' => 'slove');
				$db->update('constant_fault', $updateArray, "id = '{$fault['id']}'");
			}
		}

		// ----------------------------------------------------------
		// -----------------------alarm-----------------------------
		// ----------------------------------------------------------
		alarm($self['site_id'], $info['http_code']);
    }
};


function alarm($site_id, $http_code){
	global $db;
	//检查是否存在告警规则
	$sql = "SELECT site.domain,alarm_rule.min_limit,alarm_rule.keep_time,alarm_rule.cool_down_time,alarm_rule.notice_limit,user.day_mail_max,user.day_mobile_max,user.user_id,user.mail,user.notice_mail,user.notice_mobile,user.mobile  FROM alarm_rule,user,site WHERE alarm_rule.site_id = '{$site_id}' AND alarm_rule.remove = 0 AND alarm_rule.type = 'constant' AND user.user_id = alarm_rule.user_id AND site.site_id = alarm_rule.site_id";
	$rule = $db->query($sql, 'row');
	if(empty($rule)) return false;
	if($rule['min_limit'] == 0) return false;

	//检查是否触发告警规则
	$stop_time = date('Y-m-d H:i:s');
	$start_time = date('Y-m-d H:i:s', time() - $rule['keep_time']);
	$sql = "SELECT count(id) FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}' AND status = '200'";
	$http200 = $db->query($sql, 'row');
	$sql = "SELECT count(id) FROM mosite_{$site_id}.constant_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}'";
	$httptotal = $db->query($sql, 'row');

	if($httptotal['count(id)'] == 0) return false;
	$available = $http200['count(id)'] / $httptotal['count(id)'] * 100;
	$alarm = true;
	if($available >= $rule['min_limit']) $alarm = false;

	//检查最近的notice_limit条告警
	$sql = "SELECT time,status FROM alarm WHERE type = 'constant' AND site_id = '{$site_id}' ORDER BY time DESC LIMIT 0,{$rule['notice_limit']}";
	$recent = $db->query($sql, 'array');
	$warning = 0;
	foreach ($recent as $value) if($value['status'] == 'warning') $warning++;

	if(!$alarm){
		//没有告警需要闭合或者告警已经闭合
		if(count($recent) > 0 && $recent[0]['status'] == 'normal') return false;
		if(count($recent) == 0) return false;
		else{       //发送正常告警
			if($http_code != 200) return false;

			if($rule['notice_mail'] == 1) send_mail($rule['mail'], "{$rule['domain']}恢复正常", "恢复正常");
			if($rule['notice_mobile'] == 1 && !empty($rule['mobile'])){
				send_sms($rule['mobile'], "{$rule['domain']}恢复正常");
			}
			// send_mail($rule['mail'], "{$rule['domain']}恢复正常", "{$rule['domain']}恢复正常");

			$time = date('Y-m-d H:i:s');
			$sql = "INSERT INTO alarm
			(
				id, 
				site_id, 
				user_id, 
				time, 
				type, 
				msg, 
				status,
				mobile,
				mail
			) VALUES (
				uuid(), 
				'$site_id', 
				'{$rule['user_id']}',
				'{$time}',
				'constant',
				'恢复正常',
				'normal',
				'{$rule['notice_mobile']}',
				'{$rule['notice_mail']}'
			)";
			$db->query($sql, 'exec'); 
			return;
		}
	}

	//告警已达单次上限
	if($warning == $rule['notice_limit']) return false;
	//var_dump($warning);

	//检查冷却时间
	if(count($recent) > 0 && $recent[0]['status'] == 'warning'){
		if($recent[0]['time'] + $rule['cool_down_time'] > time()) return false; //未冷却
	}

	//不能被200触发
	if($http_code == 200) return false;

	//检查今天发送条数
	$stop_time = date('Y-m-d H:i:s');
	$start_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d 0:0:0')));
	$sql = "SELECT count(time) FROM alarm WHERE user_id = '{$rule['user_id']}' AND time >= '{$start_time}' AND time <= '{$stop_time}' AND status = 'warning'";
	$day = $db->query($sql, 'row');
	if($rule['day_mail_max'] != 0 && $day['count(time)'] >= $rule['day_mail_max']){		//不发送告警
		//return false;
	}else{
		if($rule['notice_mail'] == 1) send_mail($rule['mail'], "{$rule['domain']}出现异常", "出现异常");
	}

	if($rule['day_mobile_max'] != 0 && $day['count(time)'] >= $rule['day_mobile_max']){		//不发送告警
		//return false;
	}else{
		//  发送异常告警
		if($rule['notice_mobile'] == 1 && !empty($rule['mobile'])){
			send_sms($rule['mobile'], "{$rule['domain']}出现异常");
		}
	}

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
			status,
			mobile,
			mail
		) VALUES (
			uuid(), 
			'$site_id', 
			'{$rule['user_id']}',
			'{$time}',
			'constant',
			'{$http_code}',
			'warning',
			'{$rule['notice_mobile']}',
			'{$rule['notice_mail']}'
		)";
	$db->query($sql, 'exec');   
}

for(;;){
    $time = time();
    //node-list
    $sql = "SELECT constant_node_id,url,name FROM monitor.constant_node";
    $node = $db->query($sql, 'array');

    //url-list
    $sql = "SELECT user_id,site_id,domain,path,port,last_watch_time,period FROM monitor.site WHERE last_watch_time + period < $time  AND remove = '0' AND constant_status = '1'";
    $result = $db->query($sql, 'array');

    $urls = array();
    foreach ($result as $key => $value) {
        $urls[$value['domain']] = array(
            'url' =>"http://{$value['domain']}{$value['path']}", 
            'port' => $value['port'],
            'site_id' => $value['site_id'],
            'node_id' => 0,
            'last_watch_time' => $value['last_watch_time'],
            'period' => $value['period'],
            'user_id' => $value['user_id']
        );
    }
    $local = rolling_curl($urls, $callback, false);

    foreach ($node as $nodekey => $nodevalue) {
        $urls = array();
        foreach ($result as $key => $value) {
            $encode_url = urlencode("http://{$value['domain']}{$value['path']}");
            $urls[$value['domain']] = array(
                'url' =>"{$nodevalue['url']}?url={$encode_url}&port={$value['port']}", 
                'port' => 80,
                'site_id' => $value['site_id'],
                'node_id' => $nodevalue['constant_node_id'],
                'last_watch_time' => $value['last_watch_time'],
                'period' => $value['period'],
                'user_id' => $value['user_id']
            );
        }
        $node[$nodekey]['count'] = rolling_curl($urls, $callback, true);
    }

    //$count = rolling_curl($urls, $callback, false);
    $print = date('Y-m-d H:i:s')." 本地[{$local}]";
    foreach($node as $key => $value) $print .= " {$value['name']}[{$value['count']}]";

    echo "{$print}\n";
    sleep(20);
}

//print_r($a);


?>
