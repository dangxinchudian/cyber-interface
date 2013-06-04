<?php

class database {
	// private $dbHost = '115.28.34.205';
	private $dbHost = '121.199.31.76';
	private $dbName = 'monitor';
	private $dbUser = 'root';
	private $dbPass = 'zooboa.com';
	private $dbObj = false;

	function __construct(){
		//$this->dbObj = new PDO("mysql:host={$this->dbHost};dbname={$this->dbName};charset=UTF-8", $this->dbUser, $this->dbPass);
		$this->dbObj = new PDO("mysql:host={$this->dbHost};dbname={$this->dbName};charset=UTF8", $this->dbUser, $this->dbPass, Array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES'UTF8';"));
		$this->dbObj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// $this->dbObj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		// $this->dbObj->setAttribute(PDO::ATTR_TIMEOUT, 5);
	}

	public function exception($error){
		if($error) $this->dbObj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		else $this->dbObj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	}

	public function query($sql, $type = 'array'){
		//$sql = $this->dbObj->quote($sql);
		switch($type){
			case 'array':
				$dbObj = $this->dbObj->query($sql);
				if(!$dbObj) return false;
				$result = $dbObj->fetchAll(PDO::FETCH_ASSOC);
				break;
			case 'row':
				$dbObj = $this->dbObj->query($sql);
				if(!$dbObj) return false;
				$result = $dbObj->fetch(PDO::FETCH_ASSOC);
				break;
			case 'exec':
				$result = $this->dbObj->exec($sql);
				break;
		}
		if($result) return $result;
		else return Array();
	}

	public function insert($table, $insertArray){   //单引号问题
		if(empty($insertArray)) return false;
		$columns = array_keys($insertArray);
		$values = array_values($insertArray);
		unset($insertArray);
		foreach($values as $key => $value){
			if(is_bool($value)){
				$value = $value ? 'true' : 'false';
			}
			$values[$key] = $this->dbObj->quote($value);
		}
		foreach($columns as $key => $value){
			$columns[$key] = $table.'.'.$value;
		}
		$columns = implode(',', $columns);
		$values = implode(',', $values);
		$query = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
		return $this->dbObj->exec($query);
	}

	public function update($table, $updateArray, $where){
		if(empty($updateArray) || empty($where)) return false;
		$updates = Array();
		foreach ($updateArray as $key => $value){
			if(is_bool($value)){
				$value = $value ? 'true' : 'false';
			}
			if($value != NULL){
				$updates[] = $key.'='.$this->dbObj->quote($value);
			}else{
				$updates[] = $key.'= NULL';
			}
		}
		unset($updateArray);
		$updates = implode(',', $updates);
		$query = "UPDATE {$table} SET {$updates} WHERE {$where}";
		return $this->dbObj->exec($query);
	}

	public function del($table, $where){
		$query = "DELETE FROM $table WHERE {$where}";
		//echo $query;
		return $this->dbObj->exec($query);
	}

	public function insertId(){
		return $this->dbObj->lastInsertId(); 
	}

	public function beginTransaction(){
		return $this->dbObj->beginTransaction();
	}

	public function commit(){
		return $this->dbObj->commit();
	}

	public function rollBack(){
		return $this->dbObj->rollBack();
	}

	public function checkSchema($schema){
		$sql = "SELECT * FROM information_schema.TABLES WHERE table_schema='{$schema}'";
		$result = $this->dbObj->query($sql,'row');
		if(empty($result)) return false;
		return true;
	}

}

?>
