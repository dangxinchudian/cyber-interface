<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();

	// $site_id = filter('site_id', '/^[0-9]{1,9}$/', 'site_id格式错误');
	$site_id = 1;

	$siteModel = model('site');
	$info = $siteModel->get($site_id);
	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '你没有权限操作该站点');


	print_r($info);

exit();

	// ---cover-----
	ob_start();
	$title = "{$info['custom_name']}";

	$url = "{$info['domain']}";
	require('./report/cover.php');

	$cover = ob_get_contents();
	ob_end_clean();

	// ---constant-----
	ob_start();
	$title = "{$info['custom_name']}";
	print_r($info);

	$url = "{$info['domain']}";
	require('./report/constant.php');

	$cover = ob_get_contents();
	ob_end_clean();


	require_once('./tcpdf/tcpdf.php');

	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	// $pdf->SetAuthor('Nicola Asuni');
	// $pdf->SetTitle('TCPDF Example 006');
	// $pdf->SetSubject('TCPDF Tutorial');
	// $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

	// // set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

	// // set header and footer fonts
	// $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	// $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// // set default monospaced font
	// $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// // set margins
	// $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	// $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	// $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// // set auto page breaks
	// $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// // set image scale factor
	// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language-dependent strings (optional)
	if (file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}

	// $fontname = $pdf->addTTFfont('./msyh.ttf', 'TrueTypeUnicode', '', 12);
	$pdf->SetFont('msyh', '', 40);

	// add a page
	$pdf->AddPage();

	// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
	// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

	// create some HTML content
	$html = '<h1>HTML Example测试</h1>
	Some special characters: &lt; € &euro; &#8364; &amp; è &egrave; &copy; &gt; \\slash \\\\double-slash \\\\\\triple-slash
	<h2>List</h2>
	List example:
	<ol>
		<li><img src="images/logo_example.png" alt="test alt attribute" width="30" height="30" border="0" /> test image</li>
		<li><b>bold text</b></li>
		<li><i>italic text</i></li>
		<li><u>underlined text</u></li>
		<li><b>b<i>bi<u>biu</u>bi</i>b</b></li>
		<li><a href="http://www.tecnick.com" dir="ltr">link to http://www.tecnick.com</a></li>
		<li>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.<br />Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</li>
		<li>SUBLIST
			<ol>
				<li>row one
					<ul>
						<li>sublist</li>
					</ul>
				</li>
				<li>row two</li>
			</ol>
		</li>
		<li><b>T</b>E<i>S</i><u>T</u> <del>line through</del></li>
		<li><font size="+3">font + 3</font></li>
		<li><small>small text</small> normal <small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal</li>
	</ol>
	<dl>
		<dt>Coffee</dt>
		<dd>Black hot drink</dd>
		<dt>Milk</dt>
		<dd>White cold drink</dd>
	</dl>
	<div style="text-align:center">IMAGES<br />
	<img src="images/logo_example.png" alt="test alt attribute" width="100" height="100" border="0" /><img src="images/tiger.ai" alt="test alt attribute" width="100" height="100" border="0" /><img src="images/logo_example.jpg" alt="test alt attribute" width="100" height="100" border="0" />
	</div>';

	// output the HTML content
	$pdf->writeHTML($html, true, false, true, false, '');


	//Close and output PDF document
	$pdf->Output('example_006.pdf', 'I');




?>