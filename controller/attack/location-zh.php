<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	// $site_id = 8;
	// $start_time = time() - 60 * 60 * 24 * 5;
	// $stop_time = time();
	
	$siteModel = model('site');
	if($site_id == 0) $info = $siteModel->get($user_id, 'user_id');
	else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	$attackModel = model('attack');
	$result = $attackModel->locationZh($info['site_id'], $start_time, $stop_time);

	$regionArray = array(
		'香港' => 'CN_HK',
		'澳门' => 'CN_MO',
		'台湾' => 'CN_TW',
		'安徽' => 'CN_01',
		'浙江' => 'CN_02',
		'江西' => 'CN_03',
		'江苏' => 'CN_04',
		'吉林' => 'CN_05',
		'青海' => 'CN_06',
		'福建' => 'CN_07',
		'黑龙江' => 'CN_08',
		'河南' => 'CN_09',
		'河北' => 'CN_10',
		'湖南' => 'CN_11',
		'湖北' => 'CN_12',
		'新疆' => 'CN_13',
		'西藏' => 'CN_14',
		'甘肃' => 'CN_15',
		'广西' => 'CN_16',
		'贵州' => 'CN_18',
		'辽宁' => 'CN_19',
		'内蒙古' => 'CN_20',
		'宁夏' => 'CN_21',
		'北京' => 'CN_22',
		'上海' => 'CN_23',
		'山西' => 'CN_24',
		'山东' => 'CN_25',
		'陕西' => 'CN_26',
		'四川' => 'CN_27',
		'天津' => 'CN_28',
		'云南' => 'CN_29',
		'广东' => 'CN_30',
		'海南' => 'CN_31',
		'重庆' => 'CN_32',
		'None' => 'None',
		'' => 'None'
	);

	// print_r($result);
	$return = array();
	foreach ($result as $key => $value) {
		$return[$key] = array(
			'id' => $regionArray[$value['zh_region']],
			'value' => $value['count'],
			'title' => $value['zh_region']
		);
	}

	json(true, $return);


?>