<style>
	.center{
		text-align: center;
	}
	.cover-box{
		border: 1px solid #000;
		width: 100px;
	}
</style>
<br/>
<br/>
<br/>
<br/>
<div class="center title">
	<font face="heihei" size="40">网站安全监测报告</font>
</div>
<br/>
<br/>
<br/>
<br/>
<table>
	<tr>
		<td width="80"></td>
		<td width="330">
			<div class="cover-box">
				<font size="5"><br/></font>
				<table>
					<tr>
						<td width="10"></td>
						<td width="310" style="white-space:nowrap;">
							<font face="heihei">监测名称:</font>
							<?php echo $title; ?><br/>
							<font face="heihei">监测目标:</font>
							<?php echo $url; ?><br/>
							<font face="heihei">监测内容:</font>
							<!-- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
							√ 行为监测<br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							√ 通断性监测<br/>
							<!-- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							√ 性能监测<br/> -->
							<font face="heihei">监测日期:</font>
							<?php echo date('Y年m月d日', $start_time); ?>至<?php echo date('Y年m月d日', $stop_time); ?>
						</td>
						<td width="10"></td>
					</tr>
				</table>
				<font size="5"><br/></font>
			</div>
		</td>
		<td width="80"></td>
	</tr>
</table>

