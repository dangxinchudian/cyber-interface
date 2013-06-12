<?php

$user = model('user');
$user_id = $user->sessionCheck(function(){
	json(false, '未登录');
});
$admin = $user->adminCheck();

// $site_id = filter('site_id', '/^[0-9\,]{1,500}$/', 'site_id格式错误');
// $start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
// $stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

$site_id = '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19';
$start_time = time() - 3600 *24 *50;
$stop_time = time();

$siteModel = model('site');
$site_list = explode(',', $site_id);
$site = array();
foreach ($site_list as $key => $value) {
	$site_id = (int)$value;
	$info = $siteModel->get($site_id);
	if(empty($info)) continue;//json(false, '站点不存在');
	if($info['remove'] > 0) continue;//json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) continue; //json(false, '你没有权限操作该站点');
	$site[$site_id] = $info;
}

print_r($site);


?>