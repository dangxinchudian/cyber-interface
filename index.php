<?php
/*Twwy's art---安全监测平台*/

date_default_timezone_set('PRC');

preg_match('/\/interface\/(.+)$/', $_SERVER['REQUEST_URI'], $match);
//preg_match('/\/data\/(.+)$/', $_SERVER['REQUEST_URI'], $match);
$uri = (empty($match)) ? 'default' : $match[1];

/*数据库*/
require('./database.php');
$db = new database;

/*路由*/
$router = Array();
function router($path, $func){
	global $router;
	$router[$path] = $func;
}

/*视图*/
/*function view($page, $data = Array(), $onlyBody = false){
	foreach ($data as $key => $value) $$key = $value;
	if($onlyBody) return require("./view/{$page}");
	require("./view/header.html");
	require("./view/{$page}");
	require("./view/footer.html");
}*/

/*会话*/
session_start();

/*JSON格式*/
function json($result, $value){
	if($result) exit(json_encode(array('result' => true, 'data' => $value)));
	exit(json_encode(array('result' => false, 'msg' => $value)));
}

/*POST过滤器*/	//符合rule返回字符串，否则触发callback，optional为真则返回null
function filter($name, $rule, $callback, $optional = false){
	if($optional !== false){
		if(isset($_POST[$name])){
			if(preg_match($rule, $post = trim($_POST[$name]))) return $post;
			else{
				if(is_object($callback)) return $callback();
				else json(false, $callback);			
			}
		}elseif($optional === true) return null;
		else return $optional;
	}else{
		if(isset($_POST[$name]) && preg_match($rule, $post = trim($_POST[$name]))) return $post;
		else{
			if(is_object($callback)) return $callback();
			else json(false, $callback);			
		} 
	}

}

/*模型*/
class model{
	function db(){
		global $db;
		return $db;
	}
}//model中转db类
function model($value){
	require("./model/{$value}.php");
	return new $value;
}

/*扩展函数*/
require('common.php');
require('phpmailer/class.phpmailer.php');

/*================路由表<开始>========================*/

router('user-login', function(){ require('./controller/user/login.php'); });
router('user-reg',function(){ require('./controller/user/reg.php'); });
router('user-mail',function(){ require('./controller/user/mail.php'); });
router('user-mail-verify',function(){ require('./controller/user/mail-verify.php'); });
router('user-reset',function(){ require('./controller/user/reset.php'); });
router('user-reset-verify',function(){ require('./controller/user/reset-verify.php'); });
router('user-get',function(){ require('./controller/user/get.php'); });
router('user-mobile',function(){ require('./controller/user/mobile.php'); });
router('user-mobile-verify',function(){ require('./controller/user/mobile-verify.php'); });

router('notice-max',function(){ require('./controller/notice/max.php'); });
router('notice-type',function(){ require('./controller/notice/type.php'); });

router('summary-attack-detail',function(){ require('./controller/summary/attack-detail.php'); });
router('summary-data',function(){ require('./controller/summary/data.php'); });
router('summary-attack-mode',function(){ require('./controller/summary/attack-mode.php'); });
router('summary-attack-location',function(){ require('./controller/summary/attack-location.php'); });
router('summary-constant-fault',function(){ require('./controller/summary/constant-fault.php'); });
router('summary-report',function(){ require('./controller/summary/report.php'); });

router('site-add',function(){ require('./controller/site/add.php'); });
router('site-remove',function(){ require('./controller/site/remove.php'); });
router('site-modify',function(){ require('./controller/site/modify.php'); });
router('site-server',function(){ require('./controller/site/server.php'); });
// router('site-report',function(){ require('./controller/site/report.php'); });

router('export-pdf',function(){ require('./export/pdf.php'); });

router('site-constant-list',function(){ require('./controller/site/constant-list.php'); });
router('site-constant-active',function(){ require('./controller/site/constant-active.php'); });
router('site-constant-node',function(){ require('./controller/site/constant-node.php'); });
router('site-constant-detail',function(){ require('./controller/site/constant-detail.php'); });
router('site-constant-get',function(){ require('./controller/site/constant-get.php'); });
router('site-constant-fault',function(){ require('./controller/site/constant-fault.php'); });

router('site-action-list',function(){ require('./controller/aws/list.php'); });
router('site-aws-action-pages',function(){ require('./controller/aws/pages.php'); });
//router('site-aws-action-monthly',function(){ require('./controller/aws/monthly.php'); });
router('site-aws-action-daily',function(){ require('./controller/aws/daily.php'); });
router('site-aws-action-daily-data',function(){ require('./controller/aws/daily-data.php'); });
router('site-aws-action-robot',function(){ require('./controller/aws/robot.php'); });
router('site-aws-action-referer',function(){ require('./controller/aws/referer.php'); });
router('site-aws-action-error404',function(){ require('./controller/aws/error404.php'); });
router('site-aws-action-visitor',function(){ require('./controller/aws/visitor.php'); });
router('site-aws-action-location',function(){ require('./controller/aws/location.php'); });
router('site-aws-action-location-zh',function(){ require('./controller/aws/location-zh.php'); });
router('site-aws-action-browser',function(){ require('./controller/aws/browser.php'); });
router('site-aws-action-get',function(){ require('./controller/aws/get.php'); });

router('site-attack-get',function(){ require('./controller/attack/get.php'); });
router('site-attack-ip',function(){ require('./controller/attack/ip.php'); });
router('site-attack-location-zh',function(){ require('./controller/attack/location-zh.php'); });
router('site-attack-mode',function(){ require('./controller/attack/mode.php'); });
router('site-attack-detail',function(){ require('./controller/attack/detail.php'); });
router('site-attack-location-zh-data',function(){ require('./controller/attack/location-zh-data.php'); });
router('site-attack-mode-data',function(){ require('./controller/attack/mode-data.php'); });

router('alarm-rule-add',function(){ require('./controller/alarm/rule-add.php'); });
router('alarm-rule-list',function(){ require('./controller/alarm/rule-list.php'); });
router('alarm-rule-remove',function(){ require('./controller/alarm/rule-remove.php'); });
router('alarm-rule-modify',function(){ require('./controller/alarm/rule-modify.php'); });
router('alarm-list',function(){ require('./controller/alarm/list.php'); });
router('alarm-get',function(){ require('./controller/alarm/get.php'); });

router('server-add',function(){ require('./controller/server/add.php'); });
router('server-snmp-set',function(){ require('./controller/server/snmp-set.php'); });
router('server-snmp-check',function(){ require('./controller/server/snmp-check.php'); });
router('server-list',function(){ require('./controller/server/list.php'); });
router('server-modify',function(){ require('./controller/server/modify.php'); });
router('server-remove',function(){ require('./controller/server/remove.php'); });
router('server-item-init',function(){ require('./controller/server/item-init.php'); });
router('server-item-list',function(){ require('./controller/server/item-list.php'); });
router('server-get',function(){ require('./controller/server/get.php'); });
router('server-watch-get',function(){ require('./controller/server/watch-get.php'); });
router('server-watch-detail',function(){ require('./controller/server/watch-detail.php'); });
router('server-snmp-catch:([0-9]{1,9})',function($matches){ require('./controller/server/snmp-catch.php'); });

router('test',function(){
	echo '<form method="POST" action="./user-login"><input name="mail" value="zje2008@qq.com"/><input name="pass" value="b123456"/><input type="submit"/></form>';
});

router('test2',function(){
	echo '<form method="POST" action="./site-add"><input name="domain" value="www.hdu.edu.cn"/><input name="name" value="hdu"/><input type="submit"/></form>';
});

router('test3', function(){

require('./fpdf/fpdf.php');  
  
$pdf=new FPDF();  
$pdf->AddPage();  
$pdf->SetFont('Arial','B',16);  
$pdf->Cell(40,10,'Hello World!测试');  
$pdf->Output();  

});

router('test4', function(){
	$fault = model('alarm');
	$fault->triggerConstant(5, 404);
});

/*================路由表<结束>========================*/


/*路由遍历*/
foreach ($router as $key => $value){
	if(preg_match('/^'.$key.'$/', $uri, $matches)) exit($value($matches));
}

/*not found*/
echo 'Page not fonud';

?>
