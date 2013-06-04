<?php

date_default_timezone_set('PRC');

require('../database.php');
$db = new database;
$db->exception(false);

require('../common.php');       //common function

$url = 'http://demo.secon.me/interface/server-snmp-catch:';
for(;;){
	$time = time();
	$sql = "SELECT server_watch.server_watch_id FROM monitor.server_watch,monitor.server WHERE server.server_id = server_watch.server_id AND server_watch.last_watch_time + server.period < $time AND server_watch.remove = '0' LIMIT 100";
	$result = $db->query($sql, 'array');
	$urls = array();
	foreach ($result as $key => $value) {
		$urls[] = array(
			'url' => $url.$value['server_watch_id'],
			'watch_id' => $value['server_watch_id']
		);
	}
	$do = 0;
	$undo = 0;
	$local = rolling_curl($urls, function($data, $info, $self){
		global $do;
		global $undo;
		$info = json_decode($data, true);
		if($info['result']){
			$do++;
			//echo "watch_id:{$self['watch_id']} [{$info['data']}] \n";
		}else{
			$undo++;
			echo "watch_id:{$self['watch_id']} [{$info['msg']}] \n";
			if(!$info) echo "{$data}\n";
		}
	}, true);
	echo date('Y-m-d H:i:s')." 成功[{$do}] 失败[{$undo}]\n";
	sleep(20);
}

?>
