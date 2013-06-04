<?php

$user = model('user');
$user_id = $user->sessionCheck(function(){
	json(false, '未登录');
});
$admin = $user->adminCheck();

// $site_id = filter('site_id', '/^[0-9]{1,9}$/', 'site_id格式错误');
// $start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
// $stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

$site_id = 3;
$start_time = time() - 3600 *24 *50;
$stop_time = time();

$siteModel = model('site');
$info = $siteModel->get($site_id);
if(empty($info)) json(false, '站点不存在');
if($info['remove'] > 0) json(false, '站点已经被移除');
if(!$admin) if($info['user_id'] != $user_id) json(false, '你没有权限操作该站点');


$constantModel = model('constant');
$attackModel = model('attack');
$awsModel = model('aws');

// ---cover-----
ob_start();
$title = "{$info['custom_name']}";
$url = "{$info['domain']}";
require('./report/cover.php');

$cover = ob_get_contents();
ob_end_clean();
//------------------

// ---attack-----
ob_start();

$http = $awsModel->summary($site_id, $start_time, $stop_time);
$siteReport['hits'] = (isset($http['hits'])) ? $http['hits'] : 0;
$siteReport['visits'] = (isset($http['visits'])) ? $http['visits'] : 0;
$siteReport['bandwidth'] = (isset($http['bandwidth'])) ? $http['bandwidth'] : 0;
$siteReport['attack_ip'] = $attackModel->ip_count($site_id, $start_time, $stop_time);
$siteReport['attack_total'] = $attackModel->total_count($site_id, $start_time, $stop_time);
$siteReport['percent'] = 0;
if($siteReport['hits'] != 0) $siteReport['percent'] = round($siteReport['attack_total'] /  $siteReport['hits'] * 100, 2);
if($siteReport['percent'] > 100) $siteReport['percent'] = 100;

$attackMode = $attackModel->mode($site_id, $start_time, $stop_time);
$attackTotal = 0;
foreach ($attackMode as $key => $value) $attackTotal += (int)$value['count'];
$attackIp = $attackModel->ip($site_id, $start_time, $stop_time, 0, 10);
$daily = $awsModel->daily($site_id, $start_time, $stop_time);


require('./report/sec.php');

$attack = ob_get_contents();
ob_end_clean();
//------------------


// ---constant-----
ob_start();
$node = $constantModel->node();
foreach ($node as $key => $value) {
	$node[$key]['keep_watch_time'] = $constantModel->log_work_time($site_id, $start_time, $stop_time, $info['period'], $value['constant_node_id']);
	$node[$key]['fault_time'] = $constantModel->log_fault_time($site_id, $start_time, $stop_time, $info['period'], $value['constant_node_id']);
	$node[$key]['available'] = 100 - round($node[$key]['fault_time'] / $node[$key]['keep_watch_time'] * 100, 2);
}
$fault_time = $constantModel->table_fault_time($site_id, $start_time, $stop_time);

$fault_list = $constantModel->fault($site_id, $start_time, $stop_time, 0, 99999);
foreach ($fault_list['list'] as $key => $value) {
	$fault_list['list'][$key]['end_time'] = 0;
	if($value['status'] == 'slove'){
		$fault_list['list'][$key]['end_time'] = date('Y-m-d H:i:s', strtotime($value['time']) + $value['keep_time']);
	}
	$fault_list['list'][$key]['msg'] = errorHeader($value['http_code']);
}

require('./report/constant.php');

$constant = ob_get_contents();
ob_end_clean();
//------------------

// require_once('tcpdf_include.php');
require_once('./tcpdf/tcpdf.php');

class MYPDF extends TCPDF {

    // //Page header
    // public function Header() {
    //     // Logo
    //     $image_file = K_PATH_IMAGES.'logo_example.jpg';
    //     $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    //     // Set font
    //     $this->SetFont('helvetica', 'B', 20);
    //     // Title
    //     $this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    // }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('song', 'I', 10);
        // Page number
        $page = $this->getAliasNumPage();
        // $page = (int)$page;
        $this->Cell(0, 10, "第{$page}页", 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}


// create new PDF document
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
// $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator('TCPDF');
$pdf->SetAuthor('SECON');
$pdf->SetTitle('安全监测平台');


$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set margins
$pdf->SetMargins(15, 15, 15);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 25);

// set some language-dependent strings (optional)
if (file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

$pdf->SetFont('song', '', 13);

$pdf->AddPage();

$pdf->writeHTML($cover, true, false, true, false, '');

// $pdf->lastPage();

$pdf->setPrintHeader(true);

$pdf->SetHeaderData('', 0, '', '网站安全监测报告');
$pdf->setHeaderFont(Array('song', '', 10));

$pdf->AddPage();

$pdf->setPrintFooter(true);
$pdf->setFooterFont(Array('song', '', 10));
$pdf->SetFooterMargin(10);

$pdf->writeHTML($attack, true, false, true, false, '');

$pdf->AddPage();

$pdf->writeHTML($constant, true, false, true, false, '');


$file_name = date('Y年m月d日报告', $start_time);

//Close and output PDF document
$pdf->Output($file_name, 'I');

