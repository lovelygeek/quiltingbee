<?php

class Category {
	var $catId;
	var $cat_name;
	var $cat_desc;
	var $folder_name;
	var $use_fillers;
	var $filler_url;
	var $filler_alt;
	var $use_alt;
	var $alt_url;
	var $alt_alt;
	var $sort_by;
	var $sort_how;
	var $perline;
	var $perpage;
	
	function Category() {}
	
	function getCatById($id) {
		$query = "SELECT * FROM ".TABLE_PREFIX."categories WHERE catId = '".escape($id)."' LIMIT 1";
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) < 1)
			return false;
			
		$row = mysql_fetch_array($result);
		
		$this->catId = $row['catId'];
		$this->cat_name = $row['cat_name'];
		$this->cat_desc = $row['cat_desc'];
		$this->folder_name = $row['folder_name'];
		
		if ($row['use_fillers'] == 1)
			$this->use_fillers = true;
		else
			$this->use_fillers = false;
			
		$this->filler_url = $row['filler_url'];
		$this->filler_alt = $row['filler_alt'];
		
		if ($row['use_alt'] == 1)
			$this->use_alt = true;
		else
			$this->use_alt = false;
			
		$this->alt_url = $row['alt_url'];
		$this->alt_alt = $row['alt_alt'];
		$this->sort_by = $row['sort_by'];
		$this->sort_how = $row['sort_how'];
		$this->perline = $row['perline'];
		$this->perpage = $row['perpage'];
		
		return true;
	}
	
	function patchCount() {
		$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE catId = '$this->catId' AND isApproved = '1'";
		$result = mysql_query($query);
		
		return mysql_num_rows($result);
	}
	
	function addCat($cat_name, $cat_desc, $folder_name, $use_fillers, $filler_url, $filler_alt, $use_alt, $alt_url, $alt_alt, $sort_by, $sort_how, $perline, $perpage) {
		$query = "INSERT INTO ".TABLE_PREFIX."categories SET cat_name = '".escape($cat_name)."', cat_desc = '".escape($cat_desc)."', folder_name = '".escape($folder_name)."', use_fillers = '".escape($use_fillers)."', filler_url = '".escape($filler_url)."', filler_alt = '".escape($filler_alt)."', use_alt = '".escape($use_alt)."', alt_url = '".escape($alt_url)."', alt_alt = '".escape($alt_alt)."', sort_by = '".escape($sort_by)."', sort_how = '".escape($sort_how)."', perline = '".escape($perline)."', perpage = '".escape($perpage)."'";
		mysql_query($query) or die("Error in Cat->addCat():<br />".mysql_error());
		return mysql_insert_id();
	}
	
	function editCat($cat_name, $cat_desc, $folder_name, $use_fillers, $filler_url, $filler_alt, $use_alt, $alt_url, $alt_alt, $sort_by, $sort_how, $perline, $perpage) {
		$query = "UPDATE ".TABLE_PREFIX."categories SET cat_name = '".escape($cat_name)."', cat_desc = '".escape($cat_desc)."', folder_name = '".escape($folder_name)."', use_fillers = '".escape($use_fillers)."', filler_url = '".escape($filler_url)."', filler_alt = '".escape($filler_alt)."', use_alt = '".escape($use_alt)."', alt_url = '".escape($alt_url)."', alt_alt = '".escape($alt_alt)."', sort_by = '".escape($sort_by)."', sort_how = '".escape($sort_how)."', perline = '".escape($perline)."', perpage = '".escape($perpage)."' WHERE catId = '$this->catId'";
		mysql_query($query) or die("Error in Cat->editCat():<br />".mysql_error());
		$this->getCatById($this->catId);
	}
	
	function deleteCat($delete_folder = false) {
		global $admin;
			
		// Path to directory
		$dir = $_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/".$this->folder_name;
		
		// Open a known directory, and proceed to read its contents
		if (is_dir($dir) && is_writable($dir)) {
			$dh = opendir($dir);
			
			while (($file = readdir($dh)) !== false) {
				if (filetype($dir."/".$file) == "file") {
					$patch = new Patch();
					
					if ($patch->getPatchByFilename($file, $this->catId))
						$patch->deletePatch();
						
					elseif ($delete_folder)
						unlink($dir."/".$file);
				}
			}
			
			closedir($dh);
			
			if ($delete_folder)
				rmdir($dir);
		}
		
		// Get rid of stray patches
		$query = "DELETE FROM ".TABLE_PREFIX."patches WHERE catId = '$this->catId'";
		mysql_query($query) or die("Error in Cat->deleteCat():<br />".mysql_error());
		
		// Delete cat from db
		$query = "DELETE FROM ".TABLE_PREFIX."categories WHERE catId = '$this->catId'";
		mysql_query($query) or die("Error in Cat->deleteCat():<br />".mysql_error());
	}
}

?>
