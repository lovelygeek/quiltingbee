<?php

class Blog {
	var $id;
	var $title;
	var $content;
	var $post_date;
	var $edit_date;
	
	function Blog() {}
	
	function getBlogById($id) {
		$query = "SELECT * FROM ".TABLE_PREFIX."blog WHERE id = '".escape($id)."' LIMIT 1";
		$result = mysql_query($query);
		
		if (mysql_num_rows($result) < 1)
			return false;
			
		$row = mysql_fetch_array($result);
		
		$this->id = $row['id'];
		$this->title = $row['title'];
		$this->content = $row['content'];
		$this->post_date = $row['post_date'];
		$this->edit_date = $row['edit_date'];
		
		return true;
	}
	
	function isEdited() {
		if ($this->edit_date == "0000-00-00 00:00:00")
			return false;
		return true;
	}
	
	function getPostDate() {
		global $admin;
		return date($admin->date_format, strtotime($this->post_date) + ($admin->time_offset * 60 * 60));
	}
	
	function getPostTime() {
		global $admin;
		return date($admin->time_format, strtotime($this->post_date) + ($admin->time_offset * 60 * 60));
	}
	
	function getEditDate() {
		global $admin;
		return date($admin->date_format, strtotime($this->edit_date) + ($admin->time_offset * 60 * 60));
	}
	
	function getEditTime() {
		global $admin;
		return date($admin->time_format, strtotime($this->edit_date) + ($admin->time_offset * 60 * 60));
	}
	
	function getDatePart($part) {
		global $admin;
		return date($part, strtotime($this->post_date) + ($admin->time_offset * 60 * 60));
	}
	
	function getEditDatePart($part) {
		global $admin;
		return date($part, strtotime($this->edit_date) + ($admin->time_offset * 60 * 60));
	}
	
	function addBlog($title, $content, $post_date) {
		$query = "INSERT INTO ".TABLE_PREFIX."blog SET title = '".escape($title)."', content = '".escape($content)."', post_date = '".escape($post_date)."'";
		mysql_query($query) or die("Error in Blog->addBlog():<br />".mysql_error());
		
		$this->getBlogById(mysql_insert_id());
	}
	
	function editBlog($title, $content, $post_date) {
		$query = "UPDATE ".TABLE_PREFIX."blog SET title = '".escape($title)."', content = '".escape($content)."', post_date = '".escape($post_date)."' WHERE id = '$this->id'";
		mysql_query($query) or die("Error in Blog->editBlog():<br />".mysql_error());
		
		$this->getBlogById($this->id);
	}
	
	function deleteBlog() {
		$query = "DELETE FROM ".TABLE_PREFIX."blog WHERE id = '$this->id'";
		mysql_query($query) or die("Error in Blog->deleteBlog():<br />".mysql_error());
	}
}

?>
