<?php

include("config.php");

$admin->adminBouncer();

$blog = new Blog();

$perpage = 20;

// Date & Time
$month = gmdate("m", time() + ($admin->time_offset * 60 * 60));
$day = gmdate("d", time() + ($admin->time_offset * 60 * 60));
$year = gmdate("Y", time() + ($admin->time_offset * 60 * 60));
$hour = gmdate("H", time() + ($admin->time_offset * 60 * 60));
$minute = gmdate("i", time() + ($admin->time_offset * 60 * 60));

$ampm_time = gmdate("G", time() + ($admin->time_offset * 60 * 60));

if ($ampm_time < 12)
	$ampm = "am";
else
	$ampm = "pm";
	
$page_title = " &rsaquo; Blog";

// Delete Blog Post
if ($_GET['action'] == "delete_confirm" && $blog->getBlogById(cleanUp($_GET['id']))) {
	$page_title .= " &rsaquo; Confirm Deletion";
	include("admin_header.php");
	echo "<h1>Confirm Deletion</h1>\n";
	echo "<p>Are you sure you want to delete the blog entry, <em>$blog->title</em>?</p>\n";
	echo "<form action=\"?action=delete&amp;id=$blog->id\" method=\"post\">\n";
	echo "<fieldset>\n";
	echo "<input name=\"delete_confirm\" type=\"submit\" value=\"Yes\" />\n";
	echo "<input name=\"delete_confirm\" type=\"submit\" value=\"No\" />\n";
	echo "</fieldset>\n";
	echo "</form>\n";
	include("admin_footer.php");
	exit;
}

if ($_GET['action'] == "delete" && $blog->getBlogById(cleanUp($_GET['id']))) {
	if ($_POST['delete_confirm'] != "Yes") {
		header("Location: blog.php");
		exit;
	}
	
	$deleted = false;
	$blog->deleteBlog();
	$deleted = true;
}

// Edit Blog Post
if (($_GET['action'] == "edit" && $blog->getBlogById(cleanUp($_GET['id']))) || $_GET['action'] == "add") {
	$success = false;
	
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$title = cleanUp($_POST['title']);
		$content = cleanUpCode($_POST['content']);
		
		// Create Date - yyyy-mm-dd 00:00:00
		if ($_POST['month'] >= 1 && $_POST['month'] <= 12)
			$month = $_POST['month'];
		
		if ($_POST['day'] >= 1 && $_POST['day'] <= 31)
			$day = $_POST['day'];
		
		if ($_POST['year'] >= 2000 && $_POST['year'] <= date("Y"))
			$year = $_POST['year'];
		
		if ($_POST['hour'] >= 0 && $_POST['hour'] <= 23)
			$hour = $_POST['hour'];
		
		if ($_POST['minute'] >= 0 && $_POST['minute'] <= 59)
			$minute = $_POST['minute'];
			
		if ($_POST['ampm'] == "am" || $_POST['ampm'] == "pm")
			$ampm = $_POST['ampm'];
			
		if ($ampm == "pm" && $hour < 12)
			$hour += 12;
			
		if ($ampm == "am" && $hour == 12)
			$hour = "00";
			
		$post_date = date("Y-m-d H:i:s", strtotime($year."-".$month."-".$day." ".$hour.":".$minute.":00") - ($admin->time_offset * 60 * 60));
		
		if ($title == "" || $content == "") {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>You forgot to complete all the required fields.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
			
		elseif ($_GET['action'] == "add") {
			// Add Blog Entry
			$blog->addBlog($title, $content, $post_date);
			$success = true;
		}
		
		elseif ($_GET['action'] == "edit") {
			// Edit Blog Entry
			$blog->editBlog($title, $content, $post_date);
			$success = true;
		}
	}
	
	if ($_GET['action'] == "edit") {
		$page_title .= " &rsaquo; Edit Post";
		include("admin_header.php");
?>

<h1>Edit Post</h1>
<?php if ($success) : ?>
<p class="success">Post edited successfully.</p>
<?php endif; ?>
<form action="?action=edit&amp;id=<?= $blog->id ?>" method="post">
<fieldset>
	<ol>
		<li>
			<label for="title">Title</label>
			<input name="title" id="title" value="<?= $blog->title ?>" />
		</li>
		<li id="post-qt">
			<label for="post_content">Content</label>
			<script type="text/javascript">edToolbar();</script>
			<textarea name="content" id="post_content" cols="25" rows="10"><?= stripslashes($blog->content) ?></textarea>
			<script type="text/javascript">var edCanvas = document.getElementById('post_content');</script>
		</li>
		<li>
			<label for="post_date">Post Date</label>
<?php monthDropdown($blog->getDatePart("m")); ?>
<?php dayDropdown($blog->getDatePart("d")); ?>
			<input name="year" id="year" value="<?= $blog->getDatePart("Y") ?>" size="4" class="auto" />
			@
<?php hourDropdown($blog->getDatePart("H")); ?>
			:
<?php minuteDropdown($blog->getDatePart("i")); ?>
			<select name="ampm" id="ampm" class="auto">
				<option value="am"<?= ($blog->getDatePart("G") < 12 ? " selected" : "") ?>>am</option>
				<option value="pm"<?= ($blog->getDatePart("G") >= 12 ? " selected" : "") ?>>pm</option>
			</select>
		</li>
	</ol>
</fieldset>
<p><input type="submit" value="Edit Post" class="button" /></p>
</form>

<?
		include("admin_footer.php");
		exit;
	}
}

include("admin_header.php");
?>

<h1>Existing Posts (<a href="#add-post" title="Add a Post">Add</a>)</h1>
<?php if ($deleted) : ?>
<p class="success">Blog post deleted successfully.</p>
<?php endif; ?>

<?php 
$posts = getPosts($postCount); 

if ($postCount > 0) { 
?>
<table cellpadding="5" cellspacing = "1" width="100%">
	<thead>
		<tr>
			<th scope="col">ID</th>
			<th scope="col">Title</th>
			<th scope="col">Date</th>
			<th scope="col" colspan="2">Action</th>
		</tr>
	</thead>
	<tbody>
<?php
	$i=0;
	foreach ($posts as $row) {
		$blog->getBlogById($row['id']);
		
		echo "\t\t<tr".($i % 2 == 0 ? ' class="alt"' : "").">\n";
		echo "\t\t\t<th scope=\"row\">$blog->id</th>\n";
		echo "\t\t\t<td>$blog->title</td>\n";
		echo "\t\t\t<td>".$blog->getPostDate()." ".$blog->getPostTime()."</td>\n";
		echo "\t\t\t<td><a href=\"?action=edit&amp;id=$blog->id\" title=\"Edit Post\">Edit</a></td>\n";
		echo "\t\t\t<td><a href=\"?action=delete_confirm&amp;id=$blog->id\" title=\"Delete Post\" class=\"delete\">Delete</a></td>\n";
		echo "\t\t</tr>\n";
		
		$i++;
	}
?>
	</tbody>
</table>
<?php
}
else
	echo "<p>No posts found.</p>\n";
?>

<h1 id="add-post">Add a Post</h1>
<?php if ($success) : ?>
<p class="success">Post added successfully.</p>
<?php endif; ?>

<form action="?action=add#add-post" method="post">
<fieldset>
	<ol>
		<li>
			<label for="title">Title</label>
			<input name="title" id="title" />
		</li>
		<li id="post-qt">
			<label for="post_content">Content</label>
			<script type="text/javascript">edToolbar();</script>
			<textarea name="content" id="post_content" cols="25" rows="10"></textarea>
			<script type="text/javascript">var edCanvas = document.getElementById('post_content');</script>
		</li>
		<li>
			<label for="post_date">Post Date</label>
<?php monthDropdown($month); ?>
<?php dayDropdown($day); ?>
			<input name="year" id="year" value="<?= $year; ?>" size="4" class="auto" />
			@
<?php hourDropdown($hour); ?>
			:
<?php minuteDropdown($minute); ?>
			<select name="ampm" id="ampm" class="auto">
				<option value="am"<?= ($ampm == "am" ? " selected" : "") ?>>am</option>
				<option value="pm"<?= ($ampm == "pm" ? " selected" : "") ?>>pm</option>
			</select>
		</li>
	</ol>
</fieldset>
<p><input type="submit" value="Add Post" class="button" /></p>
</form>

<?
include("admin_footer.php");
?>
