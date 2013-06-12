<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>综合报表</title>
	<!-- <script src="./assets/js/jquery-1.8.3.min.js"></script>  -->
	<style>
		table{
			width:100%;
			border:1px solid #000;
			text-align: center;
		}
	</style>
</head>
<body>
	<h1 style="text-align:center;display:block;">
	网站安全监测报告
	</h1>
	<h3 style="text-align:center;display:block;">
		时间 <?php echo date('Y年m月d日', $start_time); ?> 至 <?php echo date('Y年m月d日', $stop_time); ?>
	</h3>
	<br/>
	<h2 style="text-align:center;display:block;">1.概况</h2>
	<br/>
	<h2 style="text-align:center;display:block;">2.网站详细</h2>
	<table border="0">
		<tr style="background:#EEE;">
			<th>站点名称</th>
			<th>站点地址</th>
			<th>总访问次数</th>
			<th>总访客数</th>
			<th>恶意访问数</th>
			<th>恶意访问占比</th>
			<th>故障时间</th>
			<th>可用率</th>
		</tr>
		<?php foreach ($site as $key => $value) { ?>
			<tr>
				<td><?php echo $value['custom_name']; ?></td>
				<td><?php echo $value['domain']; ?></td>
				<td><?php echo $value['hits']; ?></td>
				<td><?php echo $value['visits']; ?></td>
				<td><?php echo $value['attack_total']; ?></td>
				<td><?php echo $value['percent']; ?></td>
				<td><?php echo $value['info']['fault_time']; ?>秒</td>
				<td><?php echo $value['info']['available']; ?>%</td>
			</tr>	
		<?php } ?>
<!-- 		<tr>
			<td colspan="4" style="background:#EEE;">统计</td>
		</tr>
		<tr>
			<td colspan="2">总故障时间</td>
			<td colspan="2">平均可用率</td>
		</tr>
		<tr>
			<td colspan="2">1032秒</td>
			<td colspan="2">99.5%</td>
		</tr>	 -->
	</table>
	<br/>
	<h2 style="text-align:center;display:block;">3.攻击统计</h2>
	<br/>
	<h2 style="text-align:center;display:block;">4.故障历史</h2>
</body>
</html>
