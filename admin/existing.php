<?php

include("config.php");

$admin->adminBouncer();

$cat = new Category();
$new_cat  = new Category();
$patch = new Patch();

$path = $_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/";

$page_title = " &rsaquo; Existing Patches";

// Upload Patch
if ($_GET['action'] == "upload" && $cat->getCatById(cleanUp($_POST['catId']))) {
	// Store $_FILES values in variables
	$file_name = $_FILES['upload_patch']['name'];
	$file_size = $_FILES['upload_patch']['size'];
	$file_type = $_FILES['upload_patch']['type'];
	$file_temp = $_FILES['upload_patch']['tmp_name'];
	$file_error = $_FILES['upload_patch']['error'];
	
	$upload_path = $path.$cat->folder_name."/";
	
	if (!is_dir($upload_path)) {
		$page_title .= " &rsaquo; Upload Failed";
		include("admin_header.php");
		echo "<h1>Upload Failed</h1>\n";
		echo "<p>The directory <code>".$upload_path."</code> does not exist. Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	if (!is_writable($upload_path)) {
		$page_title .= " &rsaquo; Upload Failed";
		include("admin_header.php");
		echo "<h1>Upload Failed</h1>\n";
		echo "<p>The directory <code>".$upload_path."</code> is not writable. Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	switch ($file_error)
	{
		case 1:
			$page_title .= " &rsaquo; Upload Failed";
			include("admin_header.php");
			echo "<h1>Upload Failed</h1>\n";
			echo "<p>The uploaded file exceeds the upload_max_filesize directive in php.ini. Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		case 2:
			$page_title .= " &rsaquo; Upload Failed";
			include("admin_header.php");
			echo "<h1>Upload Failed</h1>\n";
			echo "<p>Your image exceeds the maximum file size allowed (15kb). Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		case 3:
			$page_title .= " &rsaquo; Upload Failed";
			include("admin_header.php");
			echo "<h1>Upload Failed</h1>\n";
			echo "<p>Your image was only partially uploaded. Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		case 4:
			$page_title .= " &rsaquo; Upload Failed";
			include("admin_header.php");
			echo "<h1>Upload Failed</h1>\n";
			echo "<p>No image was uploaded. Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
	}
	
	// Make sure the file was uploaded via HTTP POST
	if (!is_uploaded_file($file_temp)) {
		$page_title .= " &rsaquo; Upload Failed";
		include("admin_header.php");
		echo "<h1>Upload Failed</h1>\n";
		echo "<p>Possible upload attack: filename <code>$file_temp</code>.  Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	// Make sure it is a valid image file
	if (!$info = @getimagesize($file_temp)) {
		$page_title .= " &rsaquo; Upload Failed";
		include("admin_header.php");
		echo "<h1>Upload Failed</h1>\n";
		echo "<p>Only valid images are allowed.  Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	// Check the dimensions
	if (($cat->catId == 1 || $cat->catId == 2) && ($info[0] != 40 || $info[1] != 40)) {
		$page_title .= " &rsaquo; Upload Failed";
		include("admin_header.php");
		echo "<h1>Upload Failed</h1>\n";
		echo "<p>The dimensions of the image you submitted are <strong>".$info[0]."x".$info[1]."</strong>.  Only 40x40 images are allowed.  Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>";
		include("admin_footer.php");
		exit;
	}
	
	// Check the type
	if ($info[2] != 1 && $info[2] != 3) {
		$page_title .= " &rsaquo; Upload Failed";
		include("admin_header.php");
		echo "<h1>Upload Failed</h1>\n";
		echo "<p>Only .gif or .png images are allowed. Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	// Check if the file exists
	if (file_exists($upload_path.$file_name) && $_POST['patch_replace'] != 1) {
		$page_title .= " &rsaquo; Upload Failed";
		include("admin_header.php");
		echo "<h1>Upload Failed</h1>\n";
		echo "<p>An image with this filename (<code>$file_name</code>) already exists. Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	// Upload Image
	if (!move_uploaded_file($file_temp, $upload_path.$file_name)) {
		$page_title .= " &rsaquo; Upload Failed";
		include("admin_header.php");
		echo "<h1>Upload Failed</h1>\n";
		echo "<p>Your image could not be uploaded. Please <a href=\"javascript:history.back(1)\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	// Success!
	$page_title .= " &rsaquo; Upload Success";
	include("admin_header.php");
	echo "<h1>Upload Success!</h1>\n";
	echo "<p>The following image was successfully uploaded:</p>\n";
	echo "<p><img src=\"/$admin->patch_dir/$cat->folder_name/$file_name\" alt=\"$file_name\" width=\"$info[0]\" height=\"$info[1]\" /></p>\n";
	echo "<ul>\n";
	echo "\t<li>Filename: <code>".$file_name."</code></li>\n";
	echo "\t<li>".$info[0] . "x" . $info[1] . " pixels</li>\n";
	echo "\t<li>".$file_size . " bytes</li>\n";
	echo "</ul>\n";
	
	if ($_POST['isPending'] == 1)
		echo "<p><a href=\"pending.php\" title=\"Pending Trades\">&laquo; Return to Pending Trades</a></p>\n";
	else
		echo "<p><a href=\"existing.php\" title=\"Existing Patches\">&laquo; Return to Existing Patches</a></p>\n";
	
	include("admin_footer.php");
	exit;
}

// Delete Patch
if ($_GET['action'] == "delete_confirm" && $patch->getPatchById(cleanUp($_GET['patchId']))) {
	$page_title .= " &rsaquo; Confirm Deletion";
	include("admin_header.php");
	echo "<h1>Confirm Deletion</h1>\n";
	echo "<p>Are you sure you want to delete the patch <code>$patch->patch_stored</code>?</p>\n";
	echo "<form action=\"?action=delete&amp;patchId=$patch->patchId\" method=\"post\">\n";
	echo "<fieldset>\n";
	echo "<input name=\"delete_confirm\" type=\"submit\" value=\"Yes\" />\n";
	echo "<input name=\"delete_confirm\" type=\"submit\" value=\"No\" />\n";
	echo "</fieldset>\n";
	echo "</form>\n";
	include("admin_footer.php");
	exit;
}

if ($_GET['action'] == "delete" && $patch->getPatchById(cleanUp($_GET['patchId']))) {
	if ($_POST['delete_confirm'] != "Yes") {
		header("Location: existing.php");
		exit;
	}
	
	$patch_deleted = false;
	$patch->deletePatch();
	$patch_deleted = true;
}

// Edit Patch
if ($_GET['action'] == "edit" && $patch->getPatchById(cleanUp($_GET['patchId']))) {
	$cat->getCatById($patch->catId);
	
	// Flag
	$success = false;
	
	$page_title = " &rsaquo; Edit Patch";
	
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$catId = cleanUp($_POST['catId']);
		
		if (!$new_cat->getCatById($catId))
			$catId = $patch->catId;
		
		$member_name = cleanUp($_POST['member_name']);
		$member_num = cleanUp($_POST['member_num']);
		$member_email = cleanUp($_POST['member_email']);
		$patch_url = cleanUp($_POST['patch_url']);
		$patch_desc = cleanUp($_POST['patch_desc']);
		
		if ($_POST['isApproved'] != 1 && $catId == 2)
			$isApproved = 0;
		else
			$isApproved = 1;
			
		$displayId = $_POST['displayId'];
		
		if (!is_numeric($displayId) || $displayId < 1)
			$displayId = $patch->displayId;
		
		// Create Date - yyyy-mm-dd 00:00:00
		$month = $_POST['month'];
		
		if ($month < 1 || $month > 12)
			$month = substr($patch->date_received, 5, 2);
		
		$day = $_POST['day'];
		
		if ($day < 1 || $day > 31)
			$day = substr($patch->date_received, 8, 2);
		
		$year = $_POST['year'];
		
		if ($year < 2000 || $year > date("Y"))
			$year = substr($patch->date_received, 0, 4);
		
		$hour = $_POST['hour'];
		
		if ($hour < 0 || $hour > 23)
			$hour = substr($patch->date_received, 11, 2);
		
		$minute = $_POST['minute'];
		
		if ($minute < 0 || $minute > 59)
			$minute = substr($patch->date_received, 14, 2);
			
		$ampm = $_POST['ampm'];
		
		if ($ampm != "pm")
			$ampm = "am";
			
		if ($ampm == "pm" && $hour < 12)
			$hour += 12;
			
		if ($ampm == "am" && $hour == 12)
			$hour = "00";
			
		$date_received = date("Y-m-d H:i:s", strtotime($year."-".$month."-".$day." ".$hour.":".$minute.":00") - ($admin->time_offset * 60 * 60));
		
		// Validate Form Data
		if (($catId == 2 && $member_name == "") || ($catId == 2 && $member_num == "") || ($catId == 2 && $member_email == "") || ($catId == 2 && ($patch_url == "" || $patch_url == "http://")) || $patch_desc == "" || $_POST['patch_stored'] == "") {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>You forgot to complete all the required fields.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
			
		elseif ($catId == 2 && !validEmail($member_email)) {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>The email address you entered is invalid.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
			
		elseif ($catId == 2 && (!is_numeric($member_num) || $member_num < 1 || $member_num > 350)) {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>The member number you entered is invalid.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
			
		// Check to see if current patch exists
		elseif (!$info = @getimagesize($path.$cat->folder_name."/".$patch->patch_stored)) {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>You need to upload the patch <code>$patch->patch_stored</code> to the directory <code>/$admin->patch_dir/$cat->folder_name</code>.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
		
		// Make sure patch is .gif or .png
		elseif ($info[2] != 1 && $info[2] != 3) {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>Only .gif or .png patches allowed.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
			
		else {
			// Patch Filename
			if ($catId == 2)
				$patch_stored = get_patch_filename($member_name, $member_num, $info[2]);
			else {
				if ($info[2] == 3)
					$ext = ".png";
				else
					$ext = ".gif";
				
				$patch_stored = cleanFolderName($_POST['patch_stored']).$ext;
			}
				
			// Check if patch needs to be renamed
			$new_cat->getCatById($catId);
			
			$old_path = $path.$cat->folder_name."/".$patch->patch_stored;
			$new_path = $path.$new_cat->folder_name."/".$patch_stored;
			
			if (strcmp($old_path, $new_path) != 0) {
				if (file_exists($new_path)) {
					$page_title .= " &rsaquo; Error";
					include("admin_header.php");
					echo "<h1>Error</h1>\n";
					echo "<p>An image with the filename <code>$patch_stored</code> already exists.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
					include("admin_footer.php");
					exit;
				}
				
				elseif (!rename($old_path, $new_path)) {
					$page_title .= " &rsaquo; Error";
					include("admin_header.php");
					echo "<h1>Error</h1>\n";
					echo "<p>Your patch could not be renamed to <code>$patch_stored</code>.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
					include("admin_footer.php");
					exit;
				}
			}
					
			// Re-Sort Patches
			sortPatches($displayId, $catId);
			
			// Edit Patch
			$patch->editPatch($catId, $displayId, $member_name, $member_num, $member_email, $patch_url, $patch_stored, $patch_desc, $date_received, $isApproved);
			$success = true;
		}
	}
	
	include("admin_header.php");
	
	// Patch Info
	$info = $patch->patchImageExist();
		
	if ($info)
		$extension = $info['extension'];
	else
		$extension = strtolower(substr($patch->patch_stored, -4));
	
	if ($info)
		$filename = $info['filename'];
	else
		$filename = basename($patch->patch_stored, $extension);
?>

<h1>Edit Patch</h1>
<?php if ($success) : ?>
<p class="success">Patch updated successfully.</p>
<?php endif; ?>
<p>Use <em>Display Id</em> to choose the order in which you wish your patch to appear on your quilt.  However, you need to set your <a href="categories.php" title="Manage Categories">category</a> to <em>Sort Patches by Display Id</em>.</p>

<form action="" method="post">
<fieldset>
	<ol>
<?php if ($patch->catId != 2) catDropdown($patch->catId, true); ?>
		<li>
			<label for="displayId">Display ID</label>
			<input name="displayId" id="displayId" value="<?= $patch->displayId ?>" size="3" class="auto" />
		</li>
<?php if ($patch->catId == 2) : ?>
		<li>
			<label for="member_name">Member Name</label>
			<input name="member_name" id="member_name" value="<?= $patch->member_name ?>"  />
		</li>
		<li>
			<label for="member_num">Member #</label>
			<input name="member_num" id="member_num" value="<?= ($patch->member_num < 1 ? "" : $patch->member_num) ?>" size="3" class="auto"  />
		</li>
		<li>
			<label for="member_email">Member Email</label>
			<input name="member_email" id="member_email" value="<?= $patch->member_email ?>"  />
		</li>
<?php endif; ?>
		<li>
			<label for="patch_url"><?= ($patch->catId == 2 ? "Member " : "") ?>Website</label>
			<input name="patch_url" id="patch_url" value="<?= ($patch->patch_url == "" ? "http://" : $patch->patch_url) ?>"  />
<?php if ($patch->catId != 2) : ?>
			<p class="note">Optional; Your patch will be linked to this website.</p>
<?php endif; ?>
		</li>
		<li>
<?php if ($info = $patch->patchImageExist()) : ?>
			<p class="note"><img src="<?= $info['rel_path'] ?>" alt="<?= $patch->patch_desc ?>" width="<?= $info['width'] ?>" height="<?= $info['height'] ?>" /></p>
<?php elseif ($cat->catId == 1 && $patch->patchImageExist_url()) : /* Required Patch linked with patch_img_url */ ?>
			<p class="note"><img src="<?= $patch->patch_img_url ?>" alt="<?= $patch->patch_desc ?>" /></p>
			<p class="note">This patch is currently linked with a <acronym title="Uniform Resource Locator">URL</acronym>.<br />Please save as <strong><?= $patch->patch_stored ?></strong> then upload to the directory <code><?= "/".$admin->patch_dir."/".$cat->folder_name ?></code>.</p>
<?php else : ?>
			<p class="error">You need to upload the patch <code><?= $patch->patch_stored ?></code> to the directory <code><?= "/".$admin->patch_dir."/".$cat->folder_name ?></code>.</p>
<?php endif; ?>
			<label for="patch_stored">Patch Filename</label>
			<input name="patch_stored" id="patch_stored" value="<?= $filename ?>"<?= ($cat->catId == 2 ? " readonly" : "") ?> />
			<?= $extension ?>
<?php if ($cat->catId == 2) : ?>
			<p class="note">Patch filename updated automatically.</p>
<?php endif; ?>
		</li>
		<li>
			<label for="patch_desc">Description</label>
			<input name="patch_desc" id="patch_desc" value="<?= $patch->patch_desc ?>" />
			<p class="note">Appears as the patch's <code>alt</code> and <code>title</code> attributes.</p>
		</li>
		<li>
			<label for="date_received">Date Received</label>
<?php monthDropdown($patch->getDatePart("m")); ?>
<?php dayDropdown($patch->getDatePart("d")); ?>
			<input name="year" id="year" value="<?= $patch->getDatePart("Y"); ?>" size="4" class="auto" />
			@
<?php hourDropdown($patch->getDatePart("H")); ?>
			:
<?php minuteDropdown($patch->getDatePart("i")); ?>
			<select name="ampm" id="ampm" class="auto">
				<option value="am"<?= ($patch->getDatePart("G") < 12 ? " selected" : "") ?>>am</option>
				<option value="pm"<?= ($patch->getDatePart("G") >= 12 ? " selected" : "") ?>>pm</option>
			</select>
		</li>
<?php if ($patch->catId == 2) : ?>
		<li>
			<label for="isApproved">Is Approved?</label>
			<input type="checkbox" name="isApproved" id="isApproved" value="1" class="auto"<?= ($patch->isApproved ? " checked" : "") ?> />
		</li>
<?php endif; ?>
	</ol>
</fieldset>

<p><input type="submit" value="Edit Patch" class="button" /></p>
</form>

<?php
	include("admin_footer.php");
	exit;
}

// List Existing Patches
$perpage = 20;

if (empty($_POST['search_by']))
	$search_by = cleanUp($_GET['search_by']);
else
	$search_by = cleanUp($_POST['search_by']);
	
if (empty($_POST['search_for']))
	$search_for = cleanUp($_GET['search_for']);
else
	$search_for = cleanUp($_POST['search_for']);
	
if (empty($_POST['catId']))
	$catId = cleanUp($_GET['catId']);
else
	$catId = cleanUp($_POST['catId']);

include("admin_header.php");

$patches = getPatches($patchCount, 1, $search_by, $search_for, $catId, $_GET['sort_by'], $_GET['sort_how']);
?>

<div id="patch-upload" style="width: 367px; float: left;">
	<h1>Upload a Patch</h1>
	<form enctype="multipart/form-data" action="?action=upload" method="post">
	<fieldset style="margin-bottom: 0;">
		<ol>
<?php catDropdown(); ?>
			<li>
				<label for="upload_patch">Patch</label>
				<input type="hidden" name="MAX_FILE_SIZE" value="15000" />
				<input type="file" name="upload_patch" id="upload_patch" class="auto" />
			</li>
			<li>
				<label for="patch_replace">Replace Existing?</label>
				<input type="checkbox" name="patch_replace" id="patch_replace" value="1" class="auto" />
			</li>
		</ol>
	</fieldset>
	<p><input type="submit" value="Upload" class="button" /></p>
	</form>
</div>

<div id="patch-search" style="width: 368px; float: right;">
	<h1>Search Patches</h1>
	
	<form action="" method="post">
	<fieldset style="margin-bottom: 0;">
		<input type="hidden" name="catId" value="<?= $catId ?>" />
		<ol>
			<li>
				<label for="search_for">Search For</label>
				<input name="search_for" id="search_for" value="<?= $search_for ?>" />
			</li>
			<li>
				<label for="search_by">Search By</label>
				<select name="search_by" id="search_by">
					<option value="member_name"<?= ($search_by == "member_name" ? " selected" : "") ?>>Member Name</option>
					<option value="member_num"<?= ($search_by == "member_num" ? " selected" : "") ?>>Member #</option>
					<option value="member_email"<?= ($search_by == "member_email" ? " selected" : "") ?>>Member Email</option>
					<option value="patch_url"<?= ($search_by == "patch_url" ? " selected" : "") ?>>URL</option>
					<option value="patch_desc"<?= ($search_by == "patch_desc" ? " selected" : "") ?>>Description</option>
					<option value="patch_stored"<?= ($search_by == "patch_stored" ? " selected" : "") ?>>Filename</option>
				</select>
			</li>
		</ol>
	</fieldset>
	<p><input type="submit" value="Search" class="button" /></p>
	</form>
</div>

<h1 style="clear: both;">Existing Patches (<a href="add-patch.php" title="Add a Patch">Add</a>)</h1>

<form action="existing.php" method="post">
<fieldset style="margin-bottom: 0;">
	<ol>
<?php catDropdownSearch($catId); ?>
	</ol>
</fieldset>
<p style="margin-bottom: 40px;"><input type="submit" value="Browse" class="button" /></p>
</form>

<?php if ($patchCount > 0) : ?>
<?php pagination($patchCount); ?>

<table cellpadding="5" cellspacing = "1" width="100%">
	<thead>
		<tr>
			<th scope="col">
				ID
			</th>
			<th scope="col">
				Display
				<a href="?<?= ($_GET['p'] != "" ? "p=".$_GET['p']."&amp;" : "") ?><?= ($catId != "" ? "catId=$catId&amp;" : "") ?><?= ($search_by != "" ? "search_by=$search_by&amp;" : "") ?><?= ($search_for != "" ? "search_for=$search_for&amp;" : "") ?>sort_by=displayId&amp;sort_how=asc" title="Sort by Display ID - Ascending"><img alt="" src="img/up<?= ($_GET['sort_by'] == "displayId" && $_GET['sort_how'] == "asc" ? "-selected" : "") ?>.gif" width="7" height="8" /></a>
				<a href="?<?= ($_GET['p'] != "" ? "p=".$_GET['p']."&amp;" : "") ?><?= ($catId != "" ? "catId=$catId&amp;" : "") ?><?= ($search_by != "" ? "search_by=$search_by&amp;" : "") ?><?= ($search_for != "" ? "search_for=$search_for&amp;" : "") ?>sort_by=displayId&amp;sort_how=desc" title="Sort by Display ID - Descending"><img alt="" src="img/down<?= ($_GET['sort_by'] == "displayId" && $_GET['sort_how'] == "desc" ? "-selected" : "") ?>.gif" width="7" height="8" /></a>
			</th>
			<th scope="col">Category</th>
			<th scope="col">
				Member
				<a href="?<?= ($_GET['p'] != "" ? "p=".$_GET['p']."&amp;" : "") ?><?= ($catId != "" ? "catId=$catId&amp;" : "") ?><?= ($search_by != "" ? "search_by=$search_by&amp;" : "") ?><?= ($search_for != "" ? "search_for=$search_for&amp;" : "") ?>sort_by=member_name&amp;sort_how=asc" title="Sort by Member Name - Ascending"><img alt="" src="img/up<?= ($_GET['sort_by'] == "member_name" && $_GET['sort_how'] == "asc" ? "-selected" : "") ?>.gif" width="7" height="8" /></a>
				<a href="?<?= ($_GET['p'] != "" ? "p=".$_GET['p']."&amp;" : "") ?><?= ($catId != "" ? "catId=$catId&amp;" : "") ?><?= ($search_by != "" ? "search_by=$search_by&amp;" : "") ?><?= ($search_for != "" ? "search_for=$search_for&amp;" : "") ?>sort_by=member_name&amp;sort_how=desc" title="Sort by Member Name - Descending"><img alt="" src="img/down<?= ($_GET['sort_by'] == "member_name" && $_GET['sort_how'] == "desc" ? "-selected" : "") ?>.gif" width="7" height="8" /></a>
			</th>
			<th scope="col">
				Member #
				<a href="?<?= ($_GET['p'] != "" ? "p=".$_GET['p']."&amp;" : "") ?><?= ($catId != "" ? "catId=$catId&amp;" : "") ?><?= ($search_by != "" ? "search_by=$search_by&amp;" : "") ?><?= ($search_for != "" ? "search_for=$search_for&amp;" : "") ?>sort_by=member_num&amp;sort_how=asc" title="Sort by Member # - Ascending"><img alt="" src="img/up<?= ($_GET['sort_by'] == "member_num" && $_GET['sort_how'] == "asc" ? "-selected" : "") ?>.gif" width="7" height="8" /></a>
				<a href="?<?= ($_GET['p'] != "" ? "p=".$_GET['p']."&amp;" : "") ?><?= ($catId != "" ? "catId=$catId&amp;" : "") ?><?= ($search_by != "" ? "search_by=$search_by&amp;" : "") ?><?= ($search_for != "" ? "search_for=$search_for&amp;" : "") ?>sort_by=member_num&amp;sort_how=desc" title="Sort by Member # - Descending"><img alt="" src="img/down<?= ($_GET['sort_by'] == "member_num" && $_GET['sort_how'] == "desc" ? "-selected" : "") ?>.gif" width="7" height="8" /></a>
			</th>
			<th scope="col">Patch</th>
			<th scope="col">
				Date
				<a href="?<?= ($_GET['p'] != "" ? "p=".$_GET['p']."&amp;" : "") ?><?= ($catId != "" ? "catId=$catId&amp;" : "") ?><?= ($search_by != "" ? "search_by=$search_by&amp;" : "") ?><?= ($search_for != "" ? "search_for=$search_for&amp;" : "") ?>sort_by=date_received&amp;sort_how=asc" title="Sort by Date - Ascending"><img alt="" src="img/up<?= ($_GET['sort_by'] == "date_received" && $_GET['sort_how'] == "asc" ? "-selected" : "") ?>.gif" width="7" height="8" /></a>
				<a href="?<?= ($_GET['p'] != "" ? "p=".$_GET['p']."&amp;" : "") ?><?= ($catId != "" ? "catId=$catId&amp;" : "") ?><?= ($search_by != "" ? "search_by=$search_by&amp;" : "") ?><?= ($search_for != "" ? "search_for=$search_for&amp;" : "") ?>sort_by=date_received&amp;sort_how=desc" title="Sort by Date - Descending"><img alt="" src="img/down<?= ($_GET['sort_by'] == "date_received" && $_GET['sort_how'] == "desc" ? "-selected" : "") ?>.gif" width="7" height="8" /></a>
			</th>
			<th scope="col" colspan="2">Action</th>
		</tr>
	</thead>
	
	<tbody>
<?php

$i = 0;
foreach($patches as $row) {
	$patch->getPatchById($row['patchId']);
	$cat->getCatById($patch->catId);
	
	echo "\t\t<tr".($i % 2 == 0 ? ' class="alt"' : "").">\n";
	echo "\t\t\t<th scope=\"row\">$patch->patchId</th>\n";
	echo "\t\t\t<td>$patch->displayId</td>\n";
	echo "\t\t\t<td><a href=\"?catId=$cat->catId\" title=\"View patches in $cat->cat_name\">$cat->cat_name</a></td>\n";
	echo "\t\t\t<td>".($patch->member_name != "" ? "<a href=\"$patch->patch_url\" title=\"$patch->member_name's Website\" target=\"_blank\">$patch->member_name</a> ".($patch->member_email != "" ? "(<a href=\"mailto:$patch->member_email\" title=\"Email $patch->member_name\">Email</a>)" : "") : "N/A")."</td>\n";
	echo "\t\t\t<td>".(is_numeric($patch->member_num) && $patch->member_num > 0 ? "<a href=\"http://theqbee.net/members.php\" title=\"Qbee Member List\" target=\"_blank\">$patch->member_num</a>" : "N/A")."</td>\n";
	echo "\t\t\t<td>";
	
	if ($patch->patchImageExist() || $patch->patchImageExist_url()) {
		$info = $patch->patchImageExist();
		echo "<img src=\"".($info ? $info['rel_path'] : $patch->patch_img_url)."\" alt=\"$patch->patch_desc\" title=\"$patch->patch_desc\" />";
	}
	
	else
		echo "Patch not found";
	
	echo "</td>\n";
	echo "\t\t\t<td>".$patch->getPatchDate()."<br />".$patch->getPatchTime()."</td>\n";
	echo "\t\t\t<td><a href=\"?action=edit&amp;patchId=$patch->patchId\" title=\"Edit Patch\">Edit</a></td>\n";
	echo "\t\t\t<td><a href=\"?action=delete_confirm&amp;patchId=$patch->patchId\" title=\"Delete Patch\" class=\"delete\">Delete</a></td>\n";
	echo "\t\t</tr>\n";
	$i++;
}

?>
	</tbody>
</table>

<?php pagination($patchCount); ?>
<?php else : ?>
<p>No patches found.</p>
<?php endif; ?>

<?php
include("admin_footer.php");
?>
