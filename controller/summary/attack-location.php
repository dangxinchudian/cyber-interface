<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$start_time = time() - 60 * 60 * 24;
	$stop_time = time();
	
	$siteModel = model('site');
	$attackModel = model('attack');
	$siteList = $siteModel->getUser($user_id);
	foreach ($siteList as $key => $value) $sites[] = $value['site_id'];

	$result = $attackModel->locationZh($sites, $start_time, $stop_time);

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
		if(!isset($return[$value['zh_region']])){
			$return[$value['zh_region']] = array(
				'id' => $regionArray[$value['zh_region']],
				'value' => $value['count'],
				'title' => $value['zh_region']
			);
		}else{
			$return[$value['zh_region']]['value'] = $return[$value['zh_region']]['value'] + $value['count'];
		}
	}

	json(true, array_values($return));


?>