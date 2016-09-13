<?php

class Admin {
	var $username;
	var $password;
	
	var $member_num;
	var $name;
	var $email;
	
	var $html_email;
	var $patch_naming;
	var $date_format;
	var $time_format;
	var $time_offset;
	var $patch_dir;
	
	var $mailHeaders;
	
	function Admin() {}
	
	function getAdmin() {
		// Get Admin Info from Database
		$query = "SELECT * FROM ".TABLE_PREFIX."admin WHERE id = '1' LIMIT 1";
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) < 1)
			return false;
		
		$row = mysql_fetch_array($result);
		
		$this->username = $row['username'];
		$this->password = $row['password'];
		
		$this->member_num = $row['member_num'];
		$this->name = $row['name'];
		$this->email = $row['email'];
		
		if ($row['html_email'] == 0)
			$this->html_email = false;
		else
			$this->html_email = true;
			
		$this->patch_naming = $row['patch_naming'];
		$this->date_format = $row['date_format'];
		$this->time_format = $row['time_format'];
		$this->time_offset = $row['time_offset'];
		$this->patch_dir = $row['patch_dir'];
		
		// Construct Mailheaders
		$this->mailHeaders = "From: $this->name <" . $this->email . ">\r\n";
		$this->mailHeaders .= "Reply-To: $this->name <" . $this->email . ">\r\n";
		$this->mailHeaders .= 'X-Mailer: PHP/' . phpversion();
		
		return true;
	}
	
	function getAdminByUsername($username) {
		$query = "SELECT * FROM ".TABLE_PREFIX."admin WHERE id = '1' AND username = '".escape($username)."'";
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) < 1)
			return false;
			
		$this->getAdmin();
		return true;
	}
	
	function isAdminLogged() {
		$query = "SELECT password FROM ".TABLE_PREFIX."admin WHERE id = '1' AND username = '".escape(cleanUp($_COOKIE['myQa_adminUsername']))."'";
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) < 1)
			return false;
			
		$row = mysql_fetch_array($result);
		
		if (strcmp($_COOKIE['myQa_adminPassword'], md5(SALT.$row['password'])) != 0)
			return false;
		
		$this->getAdmin();
		return true;
	}
	
	function adminBouncer() {
		if ($this->isAdminLogged())
			return;
		
		header("Location: ./index.php?referrer=" . getReferrer());
		exit;
	}
	
	function editProfile($name, $email, $member_num, $html_email, $patch_naming, $date_format, $time_format, $time_offset, $patch_dir) {
		$query = "UPDATE ".TABLE_PREFIX."admin SET name = '".escape($name)."', email = '".escape($email)."', member_num = '".escape($member_num)."', html_email = '".escape($html_email)."', patch_naming = '".escape($patch_naming)."', date_format = '".escape($date_format)."', time_format = '".escape($time_format)."', time_offset = '".escape($time_offset)."', patch_dir = '".escape($patch_dir)."' WHERE id = '1' LIMIT 1";
		mysql_query($query) or die("Error in Admin->editProfile():<br />".mysql_error());
		$this->Admin();
	}
	
	function editLoginInfo($username, $password) {
		$query = "UPDATE ".TABLE_PREFIX."admin SET username = '".escape($username)."', password = '".escape($password)."' WHERE id = '1'";
		mysql_query($query) or die("Error in Admin->editLoginInfo():<br />".mysql_error());
	}
	
	function resetPassword($newpass) {
		$query = "UPDATE ".TABLE_PREFIX."admin SET password = '".escape($newpass)."' WHERE id = '1'";
		mysql_query($query) or die("Error in Admin->resetPassword():<br />".mysql_error());
	}
}

$admin = new Admin();

?>
