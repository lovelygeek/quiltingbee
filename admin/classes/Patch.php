<?php

class Patch {
	var $patchId;
	var $catId;
	var $displayId;
	var $member_name;
	var $member_num;
	var $member_email;
	var $patch_url;
	var $patch_img_url;
	var $patch_stored;
	var $patch_desc;
	var $date_received;
	var $isApproved;
	
	function Patch() {}
	
	function getPatchById($id) {
		$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE patchId = '".escape($id)."' LIMIT 1";
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) < 1)
			return false;
			
		$row = mysql_fetch_array($result);
		
		$this->patchId = $row['patchId'];
		$this->catId = $row['catId'];
		$this->displayId = $row['displayId'];
		$this->member_name = $row['member_name'];
		$this->member_num = $row['member_num'];
		$this->member_email = $row['member_email'];
		$this->patch_url = $row['patch_url'];
		$this->patch_img_url = $row['patch_img_url'];
		$this->patch_stored = $row['patch_stored'];
		$this->patch_desc = $row['patch_desc'];
		$this->date_received = $row['date_received'];
		
		if ($row['isApproved'] == 1)
			$this->isApproved = true;
		else
			$this->isApproved = false;
		
		return true;
	}
	
	function getPatchByFilename($patch_stored, $catId) {
		$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE catId = '".escape($catId)."' AND patch_stored = '".escape($patch_stored)."'";
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) < 1)
			return false;
			
		$row = mysql_fetch_array($result);
		$this->getPatchById($row['patchId']);
		return true;
	}
	
	function getPatchByMemberNum($member_num) {
		$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE catId = '2' AND member_num = '".escape($member_num)."'";
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) < 1)
			return false;
			
		$row = mysql_fetch_array($result);
		$this->getPatchById($row['patchId']);
		return true;
	}
	
	function getDatePart($part) {
		global $admin;
		return date($part, strtotime($this->date_received) + ($admin->time_offset * 60 * 60));
	}
	
	function getPatchDate() {
		global $admin;
		return date($admin->date_format, strtotime($this->date_received) + ($admin->time_offset * 60 * 60));
	}
	
	function getPatchTime() {
		global $admin;
		return date($admin->time_format, strtotime($this->date_received) + ($admin->time_offset * 60 * 60));
	}
	
	function patchImageExist() {
		global $admin;
		
		$cat = new Category();
		$cat->getCatById($this->catId);
		
		$abs_path = $_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/".$cat->folder_name."/".$this->patch_stored;
		
		if (!$info = @getimagesize($abs_path))
			return false;
		
		// Extension
		if ($info[2] == 3)
			$ext = ".png";
		else
			$ext = ".gif";
			
		// Filename
		$filename = rtrim(basename($this->patch_stored, $ext), ".");
		
		// Return Array: width [0], height [1], extension [2], filename [3], abs path [4], rel path [5]
		return array("width" => $info[0], "height" => $info[1], "extension" => $ext, "filename" => $filename, "abs_path" => $abs_path, "rel_path" => "/".$admin->patch_dir."/".$cat->folder_name."/".$this->patch_stored);
	}
	
	function patchImageExist_url() {
		if (strtolower(substr($this->patch_img_url, -4)) == ".gif" || strtolower(substr($this->patch_img_url, -4)) == ".png")
			return true;
		return false;
	}
	
	function editPatch($catId, $displayId, $member_name, $member_num, $member_email, $patch_url, $patch_stored, $patch_desc, $date_received, $isApproved) {
		$query = "UPDATE ".TABLE_PREFIX."patches SET 
				catId = '".escape($catId)."',
				displayId = '".escape($displayId)."',
				member_name = '".escape($member_name)."',
				member_num = '".escape($member_num)."',
				member_email = '".escape($member_email)."',
				patch_url = '".escape($patch_url)."',
				patch_stored = '".escape($patch_stored)."',
				patch_desc = '".escape($patch_desc)."',
				date_received = '".escape($date_received)."',
				isApproved = '".escape($isApproved)."'
				WHERE patchId = '$this->patchId'";
		mysql_query($query) or die("Error in Patch->editPatch():<br />".mysql_error());
		$this->getPatchById($this->patchId);
	}
	
	function addPatchAdmin($catId, $displayId, $member_name, $member_num, $member_email, $patch_url, $patch_stored, $patch_desc, $date_received) {
		$query = "INSERT INTO ".TABLE_PREFIX."patches SET 
				catId = '".escape($catId)."',
				displayId = '".escape($displayId)."',
				member_name = '".escape($member_name)."',
				member_num = '".escape($member_num)."',
				member_email = '".escape($member_email)."',
				patch_url = '".escape($patch_url)."',
				patch_stored = '".escape($patch_stored)."',
				patch_desc = '".escape($patch_desc)."',
				date_received = '".escape($date_received)."',
				isApproved = '1'";
		mysql_query($query) or die("Error in Patch->addPatchAdmin():<br />".mysql_error());
		
		$patchId = mysql_insert_id();
		
		if (!is_numeric($displayId) || $displayId < 1)
			mysql_query("UPDATE ".TABLE_PREFIX."patches SET displayId = '".escape(getDisplayId($catId))."' WHERE patchId = '$patchId'") or die("Error in Patch->addPatchAdmin():<br />".mysql_error());
		
		$this->getPatchById($patchId);
	}
	
	function addPendingTrade($member_name, $member_num, $member_email, $patch_url, $patch_img_url, $patch_stored, $patch_desc) {
		$query = "INSERT INTO ".TABLE_PREFIX."patches SET
				catId = '2',
				member_name = '".escape($member_name)."',
				member_num = '".escape($member_num)."',
				member_email = '".escape($member_email)."',
				patch_url = '".escape($patch_url)."',
				patch_img_url = '".escape($patch_img_url)."',
				patch_stored = '".escape($patch_stored)."',
				patch_desc = '".escape($patch_desc)."',
				date_received = '".TODAY."',
				isApproved = '0'";
		mysql_query($query) or die("Error in Patch->addPendingTrade():<br />".mysql_error());
		
		$patchId = mysql_insert_id();
		
		// Update display id
		mysql_query("UPDATE ".TABLE_PREFIX."patches SET displayId = '".escape(getDisplayId(2))."' WHERE patchId = '$patchId'") or die("Error in Patch->addPendingTrade():<br />".mysql_error());
		
		$this->getPatchById($patchId);
	}
	
	function approvePendingTrade() {
		$query = "UPDATE ".TABLE_PREFIX."patches SET isApproved = '1', date_received = '".TODAY."' WHERE patchId = '$this->patchId'";
		mysql_query($query) or die("Error in Patch->approvePendingTrade():<br />".mysql_error());
	}
	
	function deletePatch() {
		global $admin;
		$cat = new Category();
		
		$query = "DELETE FROM ".TABLE_PREFIX."patches WHERE patchId = '$this->patchId'";
		mysql_query($query) or die("Error in Patch->deletePatch():<br />".mysql_error());
		
		$cat->getCatById($this->catId);
		$path = $_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/".$cat->folder_name."/".$this->patch_stored;
		
		// Delete file
		if (@getimagesize($path))
			unlink($path);
	}
}

?>
