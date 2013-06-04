<?php
/*new*/

class user extends model{

	public function sessionCheck($callback = false){
		if(empty($_SESSION['user_id'])){
			if($callback) return $callback();
			else exit(header('Location: ./'));
		}else return $_SESSION['user_id'];
	}

	public function adminCheck(){
		if(isset($_SESSION['admin']) && $_SESSION['admin'] > 0) return true;
		return false;
	}

	public function creat($mail, $pass){
		$salt = random('str', 27);
		$passwd = $this->passEncode($pass, $salt);
		$insertArray = array(
			'mail' => $mail, 
			'usalt' => $salt, 
			'passwd' => $passwd,
			'creat_time' => time()
		);
		$result = $this->db()->insert('user', $insertArray);
		if($result == 0) return false;
		return $this->db()->insertId();
	}

	public function setPass($user_id, $pass){
		$salt = random('str', 27);
		$passwd = $this->passEncode($pass, $salt);
		$updateArray = array(
			'usalt' => $salt,
			'passwd' => $passwd
		);
		$this->update($user_id, $updateArray);
	}

	public function passEncode($pass, $salt){
		return md5($salt.'?'.$salt.'='.$pass);
	}

	public function get($value, $type = 'user_id'){
		$whereArray = array(
			'user_id' => " user_id = '{$value}' ",
			'mail' => " mail = '{$value}' "
		);
		$sql = "SELECT * FROM user WHERE {$whereArray[$type]}";
		return $this->db()->query($sql, 'row');
	}

	public function login($user_id){
		$_SESSION['user_id'] = $user_id;
		$updateArray = array('last_login_time' => time());
		$result = $this->update($user_id, $updateArray);
		if($result > 0) return true;
		return false;
	}

	public function update($user_id, $updateArray){
		return $this->db()->update('user', $updateArray, "user_id = '{$user_id}'");
	}

	public function mailCodeCreat($user_id){
		$updateArray = array(
			'code_mail' => md5(random('str', '20')),
			'code_mail_time' => time() + 60 * 60		//有效时间为一个小时
		);
		$this->update($user_id, $updateArray);
		return $updateArray['code_mail'];
	}

	public function mobileCodeCreat($user_id){
		$updateArray = array(
			'code_mobile' => random('num', 6),
			'code_mobile_time' => time() + 60 * 10		//有效时间为10分钟
		);
		$this->update($user_id, $updateArray);
		return $updateArray['code_mobile'];		
	}

	public function resetCodeCreat($user_id){
		$updateArray = array(
			'code_reset' => md5(random('str', '20')),
			'code_reset_time' => time() + 60 * 60		//有效时间为一个小时
		);
		$this->update($user_id, $updateArray);
		return $updateArray['code_reset'];
	}

}
?>