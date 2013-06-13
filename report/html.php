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
	<table border="0">
		<tr style="background:#EEE;">
			<th>全部监控站点</th>
			<th>正常站点</th>
			<th>故障站点</th>
		</tr>
		<tr>
			<td><?php echo $summary['total']; ?></td>
			<td><?php echo $summary['work']; ?></td>
			<td><?php echo $summary['unwork']; ?></td>
		</tr>
		<tr style="background:#EEE;">
			<th>访问总量</th>
			<th>攻击总量</th>
			<th>攻击占比</th>
		</tr>
		<tr>
			<td><?php echo $summary['hits']; ?></td>
			<td><?php echo $summary['attack_total']; ?></td>
			<td><?php echo $summary['percent']; ?>%</td>
		</tr>
	</table>
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
	</table>
	<br/>
<!-- 	<h2 style="text-align:center;display:block;">3.攻击统计</h2>
	<br/> -->
	<h2 style="text-align:center;display:block;">3.故障历史</h2>
	<table border="0">
		<tr style="background:#EEE;">
			<th>站点</th>
			<th>开始时间</th>
			<th>恢复时间</th>
			<th>故障持续时间</th>
			<th>故障原因</th>
		</tr>
		<?php foreach ($fault['list'] as $key => $value) { ?>
			<tr>
				<td><?php echo $site[$value['site_id']]['custom_name']; ?></td>
				<td><?php echo $value['time']; ?></td>
				<td><?php echo $value['end_time']; ?></td>
				<td><?php echo $value['keep_time']; ?>秒</td>
				<td><?php echo $value['msg']; ?></td>
			</tr>	
		<?php } ?>
	</table>
</body>
</html>
