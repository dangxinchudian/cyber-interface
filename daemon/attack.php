<?php

date_default_timezone_set('PRC');

require('../database.php');
$db = new database;
$db->exception(false);

require('../common.php');       //common function
require('../phpmailer/class.phpmailer.php');

function check($site_id){

	global $db;
	//检查是否存在告警规则
	$sql = "SELECT site.domain,alarm_rule.max_limit,alarm_rule.keep_time,alarm_rule.cool_down_time,alarm_rule.notice_limit,user.day_mail_max,user.day_mobile_max,user.user_id,user.mail,user.notice_mail,user.notice_mobile,user.mobile  FROM alarm_rule,user,site WHERE alarm_rule.site_id = '{$site_id}' AND alarm_rule.remove = 0 AND alarm_rule.type = 'attack' AND user.user_id = alarm_rule.user_id AND site.site_id = alarm_rule.site_id";
	$rule = $db->query($sql, 'row');

	if(empty($rule)) return false;
	if($rule['max_limit'] == 0) return false;

	//检查是否触发告警规则
	$stop_time = date('Y-m-d H:i:s');
	$start_time = date('Y-m-d H:i:s', time() - $rule['keep_time']);
	$sql = "SELECT count(client_ip) as count FROM mosite_{$site_id}.attack_log WHERE time >= '{$start_time}' AND time <= '{$stop_time}'";
	$result = $db->query($sql, 'row');

	$attack_count = $result['count'];
	$alarm = true;
	if($attack_count < $rule['max_limit']) $alarm = false;

	//检查最近的notice_limit条告警
	$sql = "SELECT time,status FROM alarm WHERE type = 'attack' AND site_id = '{$site_id}' ORDER BY time DESC LIMIT 0,{$rule['notice_limit']}";
	$recent = $db->query($sql, 'array');
	$warning = 0;
	foreach ($recent as $value) if($value['status'] == 'warning') $warning++;

	if(!$alarm){
		//没有告警需要闭合或者告警已经闭合
		if(count($recent) > 0 && $recent[0]['status'] == 'normal') return '全部已闭合';
		if(count($recent) == 0) return '不需要闭合';
		else{       //发送正常告警
			if($rule['notice_mail'] == 1) send_mail($rule['mail'], "{$rule['domain']}恢复正常", "恢复正常");
			if($rule['notice_mobile'] == 1 && !empty($rule['mobile'])){
				send_sms($rule['mobile'], "{$rule['domain']}恢复正常");
			}
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
				'attack',
				'恢复正常',
				'normal',
				'{$rule['notice_mobile']}',
				'{$rule['notice_mail']}'
			)";
			$db->query($sql, 'exec'); 
			return '正常告警已发送';
		}
	}

	//告警已达单次上限
	if($warning == $rule['notice_limit']) return '达到上限';

	//检查冷却时间
	if(count($recent) > 0 && $recent[0]['status'] == 'warning'){
		if(strtotime($recent[0]['time']) + $rule['cool_down_time'] > time()) return '未冷却'; //未冷却
	}

	//检查今天发送条数
	$stop_time = date('Y-m-d H:i:s');
	$start_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d 0:0:0')));
	$sql = "SELECT count(time) FROM alarm WHERE user_id = '{$rule['user_id']}' AND time >= '{$start_time}' AND time <= '{$stop_time}' AND status = 'warning'";
	$day = $db->query($sql, 'row');
	if($rule['day_mail_max'] != 0 && $day['count(time)'] >= $rule['day_mail_max']){		//不发送告警
		//return false;
	}else{
		if($rule['notice_mail'] == 1) send_mail($rule['mail'], "{$rule['domain']}正在遭受攻击", "正在遭受攻击");
	}

	if($rule['day_mobile_max'] != 0 && $day['count(time)'] >= $rule['day_mobile_max']){		//不发送告警
		//return false;
	}else{
		//  发送异常告警
		if($rule['notice_mobile'] == 1 && !empty($rule['mobile'])){
			send_sms($rule['mobile'], "{$rule['domain']}正在遭受攻击");
		}
	}

	$msg = '正在遭受攻击';

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
			'attack',
			'{$msg}',
			'warning',
			'{$rule['notice_mobile']}',
			'{$rule['notice_mail']}'
		)";
	$db->query($sql, 'exec');
	return '发送攻击告警';	
}


for(;;){
    $sql = "SELECT site_id,domain,custom_name FROM monitor.site WHERE remove = '0'";
    $result = $db->query($sql, 'array');
    foreach ($result as $key => $value) {
    		$return = check($value['site_id']);
    		echo "site_id:{$value['site_id']} {$value['domain']}[{$value['custom_name']}] {$return}\n";
    }
    sleep(10);
}


?>