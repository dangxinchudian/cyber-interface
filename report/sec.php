<font face="heihei" size="30">行为监测</font><br/><br/>

<font face="heihei" size="20">1. 监测概述</font><br/><br/>
本项监测通过部署在服务器端Agent收集分析在<?php echo date('Y年m月d日', $start_time); ?>至<?php echo date('Y年m月d日', $stop_time); ?>期间<?php echo $title; ?>(<?php echo $url; ?>)的行为信息，总共被<?php echo $siteReport['visits']; ?>个访客访问<?php echo $siteReport['hits']; ?>次。所有访问中恶意访问为<?php echo $siteReport['attack_total']; ?>次，占总访问量的<?php echo $siteReport['percent']; ?>%。<br/><br/>

<font face="heihei" size="20">2. 正常行为汇总</font><br/><br/>


<table>
	<tr>
<!-- 		<td width="40"></td> -->
		<td width="500">
			<table border="1">
				<tr>
					<th width="300" align="center" style="background-color:#9999CC;">日期</th>
					<th width="100" align="center" style="background-color:#9999CC;">访问量</th>
					<th width="100" align="center" style="background-color:#9999CC;">访客量</th>
				</tr>
				<?php if(!empty($daily)){ ?>
	 				<?php foreach ($daily as $key => $value) { ?>
						<tr>
							<td width="300" align="center" >
								<?php echo date('Y年m月d日', strtotime($value['day'])); ?>
							</td>
							<td width="100" align="center" ><?php echo $value['hits']; ?></td>
							<td width="100" align="center" ><?php echo $value['visits']; ?></td>
						</tr>
					<?php } ?>
				<?php }else{ ?>
					<tr>
						<td width="500" align="center" >没有访问</td>
					</tr>
				<?php } ?>
			</table>
		</td>
<!-- 		<td width="40"></td> -->
	</tr>
</table>
<br/><br/>

<font face="heihei" size="20">3. 攻击行为汇总</font><br/><br/>
攻击类型统计<br/>
<table>
	<tr>
<!-- 		<td width="40"></td> -->
		<td width="500">
			<table border="1">
				<tr>
					<th width="300" align="center" style="background-color:#9999CC;">攻击类型</th>
					<th width="100" align="center" style="background-color:#9999CC;">次数</th>
					<th width="100" align="center" style="background-color:#9999CC;">百分比</th>
				</tr>
				<?php if(!empty($attackMode)){ ?>
	 				<?php foreach ($attackMode as $key => $value) { ?>
						<tr>
							<td width="300" align="center" >
								<?php 
									if($value['attack_type'] == '-') echo '其他';
									else echo $value['attack_type']; 
								?>
							</td>
							<td width="100" align="center" ><?php echo $value['count']; ?></td>
							<td width="100" align="center" >
								<?php $per = round($value['count'] / $attackTotal * 100, 2); ?>
								<?php echo $per; ?>%
							</td>
						</tr>
					<?php } ?>
				<?php }else{ ?>
					<tr>
						<td width="500" align="center" >没有攻击</td>
					</tr>
				<?php } ?>
			</table>
		</td>
<!-- 		<td width="40"></td> -->
	</tr>
</table>
<br/><br/>
攻击来源IP前十<br/>
<table>
	<tr>
<!-- 		<td width="40"></td> -->
		<td width="500">
			<table border="1">
				<tr>
					<th width="100" align="center" style="background-color:#9999CC;">攻击IP</th>
					<th width="100" align="center" style="background-color:#9999CC;">国家</th>
					<th width="100" align="center" style="background-color:#9999CC;">省份</th>
					<th width="100" align="center" style="background-color:#9999CC;">城市</th>
					<th width="100" align="center" style="background-color:#9999CC;">百分比</th>
				</tr>
				<?php if(!empty($attackIp['list'])){ ?>
	  				<?php foreach ($attackIp['list'] as $key => $value) { ?>
						<tr>
							<td width="100" align="center" ><?php echo $value['client_ip']; ?></td>
							<td width="100" align="center" >
								<?php 
									if($value['zh_country'] == 'CN') echo '中国';
									else echo '其他';
								?>
							</td>
							<td width="100" align="center" >
								<?php 
									if($value['zh_region'] == 'None') echo '未知';
									else echo $value['zh_region'];
								?>
							</td>
							<td width="100" align="center" >
								<?php 
									if($value['zh_city'] == 'None') echo '未知';
									else echo $value['zh_city'];
								?>
							</td>
							<td width="100" align="center" >
								<?php $per = round($value['count'] / $attackIp['count_total'] * 100, 2); ?>
								<?php echo $per; ?>%
							</td>
						</tr>
					<?php } ?>
				<?php }else{ ?>
					<tr>
						<td width="500" align="center" >没有攻击</td>
					</tr>
				<?php } ?>
			</table>
		</td>
<!-- 		<td width="40"></td> -->
	</tr>
</table>

