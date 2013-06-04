<?php

/*new*/

class aws extends model{

	public function summary($site_id, $start_time, $stop_time){
		$table = $this->checkTable("molog_{$site_id}", 'daily');
		if(!$table) return array();
		$start_time = date('Ymd', $start_time);
		$stop_time = date('Ymd', $stop_time);
		$sql = "SELECT SUM(visits) AS visits,SUM(hits) AS hits,SUM(bandwidth) AS bandwidth FROM molog_{$site_id}.daily WHERE daily.day >= {$start_time} AND daily.day <= {$stop_time}";
		return $this->db()->query($sql, 'row');		
	}

	public function general($month, $site_id){
		$table = $this->checkTable("molog_{$site_id}", 'general');
		if(!$table) return array();
		$sql = "SELECT * FROM molog_{$site_id}.general WHERE general.year_month = '{$month}'";
		return $this->db()->query($sql, 'row');
	}

	public function daily($site_id, $start_time, $stop_time){
		$table = $this->checkTable("molog_{$site_id}", 'daily');
		if(!$table) return array();
		$start_time = date('Ymd', $start_time);
		$stop_time = date('Ymd', $stop_time);
		$sql = "SELECT * FROM molog_{$site_id}.daily WHERE daily.day >= {$start_time} AND daily.day <= {$stop_time}";
		return $this->db()->query($sql, 'array');
	}

	public function pages($site_id, $start, $limit){
		$table = $this->checkTable("molog_{$site_id}", 'pages');
		if(!$table) return array('list' => array(), 'total' => 0);
		$sql = "SELECT * FROM molog_{$site_id}.pages ORDER BY pages DESC LIMIT {$start},{$limit}";
		$result['list'] = $this->db()->query($sql, 'array');
		$sql = "SELECT count(id) FROM molog_{$site_id}.pages ";
		$dbResult = $this->db()->query($sql, 'row');
		$result['total'] = $dbResult['count(id)'];
		return $result;
	}

	public function robot($site_id, $start, $limit){
		$table = $this->checkTable("molog_{$site_id}", 'robot');
		if(!$table) return array('list' => array(), 'total' => 0);
		$sql = "SELECT * FROM molog_{$site_id}.robot ORDER BY hits DESC LIMIT {$start},{$limit}";
		$result['list'] = $this->db()->query($sql, 'array');
		$sql = "SELECT count(id) FROM molog_{$site_id}.robot ";
		$dbResult = $this->db()->query($sql, 'row');
		$result['total'] = $dbResult['count(id)'];
		return $result;
	}

	public function browser($site_id, $month){
		$table = $this->checkTable("molog_{$site_id}", 'browser');
		if(!$table) return array();
		$sql = "SELECT * FROM molog_{$site_id}.browser ORDER BY hits DESC";
		return $this->db()->query($sql, 'array');
	}

	public function referer($site_id, $start, $limit){
		$table = $this->checkTable("molog_{$site_id}", 'pageref');
		if(!$table) return array('list' => array(), 'total' => 0);
		$sql = "SELECT * FROM molog_{$site_id}.pageref ORDER BY hits DESC LIMIT {$start},{$limit}";
		$result['list'] = $this->db()->query($sql, 'array');
		$sql = "SELECT count(id) FROM molog_{$site_id}.pageref ";
		$dbResult = $this->db()->query($sql, 'row');
		$result['total'] = $dbResult['count(id)'];
		return $result;
	}

	public function error404($site_id, $start, $limit){
		$table = $this->checkTable("molog_{$site_id}", 'errors404');
		if(!$table) return array('list' => array(), 'total' => 0);
		$sql = "SELECT * FROM molog_{$site_id}.errors404 ORDER BY hits DESC LIMIT {$start},{$limit}";
		$result['list'] = $this->db()->query($sql, 'array');
		$sql = "SELECT count(id) FROM molog_{$site_id}.errors404 ";
		$dbResult = $this->db()->query($sql, 'row');
		$result['total'] = $dbResult['count(id)'];
		return $result;
	}

	public function visitor($site_id, $start, $limit){
		$table = $this->checkTable("molog_{$site_id}", 'visitors');
		if(!$table) return array('list' => array(), 'total' => 0);
		$sql = "SELECT * FROM molog_{$site_id}.visitors ORDER BY hits DESC LIMIT {$start},{$limit}";
		$result['list'] = $this->db()->query($sql, 'array');
		$sql = "SELECT count(id) FROM molog_{$site_id}.visitors ";
		$dbResult = $this->db()->query($sql, 'row');
		$result['total'] = $dbResult['count(id)'];
		return $result;
	}

	public function location($site_id){
		$table = $this->checkTable("molog_{$site_id}", 'visitors');
		if(!$table) return array();
		$sql = "SELECT country_code, country_desc, sum(pages) as total_pages, sum(hits) as total_hits, sum(bandwidth) as total_bandwidth FROM molog_{$site_id}.visitors GROUP BY country_code ORDER BY total_hits DESC";
		return $this->db()->query($sql, 'array');
	}

	public function locationZh($site_id){
		$table = $this->checkTable("molog_{$site_id}", 'visitors');
		if(!$table) return array();
		$sql = "SELECT region, sum(pages) as total_pages, sum(hits) as total_hits, sum(bandwidth) as total_bandwidth FROM molog_{$site_id}.visitors WHERE country_code = 'CN' GROUP BY region ORDER BY total_hits DESC";
		return $this->db()->query($sql, 'array');
	}
	
	public function checkTable($database, $table){
		$sql = "SELECT TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='{$database}' and TABLE_NAME='{$table}'";
		$result = $this->db()->query($sql, 'row');
		if(empty($result)) return false;
		return true;
	}
}

?>