<?php

//router('site-remove',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'site_id格式错误');
	$destroy = filter('destroy', '/^false|true$/', 'destroy格式错误');
	/*$site_id = 1;
	$destroy = 'true';*/

	$siteModel = model('site');
	$info = $siteModel->get($site_id);
	if(empty($info)) json(false, '该站点不存在');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '你没有权限操作该站点');
	if($info['remove'] == 2) json(false, '该站点已销毁');
	if($destroy == 'true'){
		$result = $siteModel->remove($site_id, true);
	}else{
		if($info['remove'] == 1) json(false, '该站点已删除');
		$result = $siteModel->remove($site_id, false);
	}

	if($result) json(true, '删除成功');
	json(true, '未进行删除');

//});

?>