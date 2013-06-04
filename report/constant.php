<font face="heihei" size="30">通断性监测</font><br/><br/>

<font face="heihei" size="20">1. 监测概述</font><br/><br/>

<?php
	$count = count($node);
	$nodeName = array();
	foreach ($node as $key => $value) $nodeName[] = $value['name'];

	$wholeTime = $stop_time - $start_time;
	if($wholeTime <= 0){
		$available = 0;
	}else{
		$available = round(($wholeTime - $fault_time) / $wholeTime, 4) * 100;
	}

?>

本项监测通过分布在<?php echo implode('、', $nodeName); ?>共<?php echo $count; ?>个不同线路监测节点对<?php echo $title; ?>(<?php echo $url; ?>)进行网站通断性监测(<?php echo date('Y年m月d日', $start_time); ?>至<?php echo date('Y年m月d日', $stop_time); ?>),网站可用率为<?php echo $available; ?>%，故障时间为<?php echo $fault_time; ?>秒。

<br/>
<br/>
<font face="heihei" size="20">2. 监测节点汇总</font><br/><br/>

<table>
	<tr>
		<td width="40"></td>
		<td width="400">
			<table border="1">
				<tr>
					<th width="200" align="center" style="background-color:#9999CC;">节点</th>
					<th width="100" align="center" style="background-color:#9999CC;">可用率</th>
					<th width="100" align="center" style="background-color:#9999CC;">故障时间</th>
				</tr>
				<?php foreach ($node as $key => $value) { ?>
					<tr>
						<td width="200" align="center"><?php echo $value['name']; ?></td>
						<td width="100" align="center"><?php echo $value['available']; ?>%</td>
						<td width="100" align="center"><?php echo $value['fault_time']; ?>秒</td>
					</tr>
				<?php } ?>
			</table>
		</td>
		<td width="40"></td>
	</tr>
</table>
<br/>
<br/>
<span color="#777">注:如果总可用率为100%而部分监测节点的可用率不为100%,一般故障原因为运营商线路问题。</span>
<br/>
<br/>
<font face="heihei" size="20">3. 故障情况汇总</font><br/><br/>
<table>
	<tr>
<!-- 		<td width="40"></td> -->
		<td width="500">
			<table border="1">
				<tr>
					<th width="100" align="center" style="background-color:#9999CC;">开始时间</th>
					<th width="100" align="center" style="background-color:#9999CC;">恢复时间</th>
					<th width="100" align="center" style="background-color:#9999CC;">故障持续时间</th>
					<th width="200" align="center" style="background-color:#9999CC;">故障原因</th>
				</tr>
				<?php if(!empty($fault_list['list'])){ ?>
					<?php foreach ($fault_list['list'] as $key => $value) { ?>
						<tr>
							<td width="100" align="center" ><?php echo $value['time']; ?></td>
							<td width="100" align="center" ><?php echo $value['end_time']; ?></td>
							<td width="100" align="center" ><?php echo $value['keep_time']; ?>秒</td>
							<th width="200" align="center" ><?php echo $value['msg']; ?></th>
						</tr>
					<?php } ?>
				<?php }else{ ?>
					<tr>
						<td width="500" align="center" >没有故障</td>
					</tr>
				<?php } ?>
			</table>
		</td>
<!-- 		<td width="40"></td> -->
	</tr>
</table>


