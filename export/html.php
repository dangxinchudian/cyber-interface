<?php

$user = model('user');
$user_id = $user->sessionCheck(function(){
	json(false, '未登录');
});
$admin = $user->adminCheck();

$site_id = filter('site_id', '/^[0-9\,]{1,500}$/', 'site_id格式错误');
$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

// $site_id = '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19';
// $start_time = time() - 3600 *24 *50;
// $stop_time = time();

$siteModel = model('site');
$constantModel = model('constant');
$awsModel = model('aws');
$attackModel = model('attack');

$site_list = explode(',', $site_id);
$site = array();
foreach ($site_list as $key => $value) {
	$site_id = (int)$value;
	$info = $siteModel->get($site_id);
	if(empty($info)) continue;//json(false, '站点不存在');
	if($info['remove'] > 0) continue;//json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) continue; //json(false, '你没有权限操作该站点');

	$http = $awsModel->summary($site_id, $start_time, $stop_time);
	$info['hits'] = (isset($http['hits'])) ? $http['hits'] : 0;
	$info['visits'] = (isset($http['visits'])) ? $http['visits'] : 0;
	$info['bandwidth'] = (isset($http['bandwidth'])) ? $http['bandwidth'] : 0;
	$info['attack_ip'] = $attackModel->ip_count($site_id, $start_time, $stop_time);
	$info['attack_total'] = $attackModel->total_count($site_id, $start_time, $stop_time);
	$info['percent'] = 0;
	if($info['hits'] != 0) $info['percent'] = round($info['attack_total'] / $info['hits'] * 100, 2);
	if($info['percent'] > 100) $info['percent'] = 100;

	$site[$site_id] = $info;
}


foreach ($site as $key => $value) {
	$site[$key]['info']['fault_time'] = $constantModel->table_fault_time($value['site_id'], $start_time, $stop_time);

	if($value['creat_time'] > $start_time) $start_time = $value['creat_time'];
	$wholeTime = $stop_time - $start_time;
	if($wholeTime <= 0){
		$available = 0;
	}else{
		if($site[$key]['info']['fault_time'] > $wholeTime) $site[$key]['info']['fault_time'] = $wholeTime;
		$available = round(($wholeTime - $site[$key]['info']['fault_time']) / $wholeTime, 4) * 100;
	}
	$site[$key]['info']['wholeTime'] = $wholeTime;
	$site[$key]['info']['available'] = $available;
	$site[$key]['info']['keey_day'] = (int)(($stop_time-$start_time)/(24*3600));
	$site[$key]['info']['work'] = $constantModel->nowFault($value['site_id']);

}

$summary = array('work' => 0, 'unwork' => 0, 'hits' => 0, 'attack_total' => 0, 'percent' => 0);
$summary['total'] = count($site);
foreach ($site as $key => $value) {
	if($value['info']['work']) $summary['work']++;
	else  $summary['unwork']++;
	$summary['hits'] += $value['hits'];
	$summary['attack_total'] += $value['attack_total'];
}
if($summary['hits'] != 0) $summary['percent'] = round($summary['attack_total'] / $summary['hits'] * 100, 2);
if($summary['percent'] > 100) $summary['percent'] = 100;

$fault = $constantModel->fault($site_list, $start_time, $stop_time, 0, 99999);
foreach ($fault['list'] as $key => $value) {
	$fault['list'][$key]['end_time'] = '尚未恢复';
	if($value['status'] == 'slove'){
		$fault['list'][$key]['end_time'] = date('Y-m-d H:i:s', strtotime($value['time']) + $value['keep_time']);
	}
	$fault['list'][$key]['msg'] = errorHeader($value['http_code']);
}

// print_r($fault);
// print_r($site);

require('./report/html.php');
// print_r($site);


?>