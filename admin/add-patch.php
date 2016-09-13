<?php

/*******************************************************************************
*
*	ADD PATCH
*
*******************************************************************************/

include("config.php");

$admin->adminBouncer();

$cat = new Category();
$patch = new Patch();

$page_title = " &rsaquo; Add a Patch";

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

// Add Patch
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$catId = cleanUp($_POST['catId']);
	$displayId = cleanUp($_POST['displayId']);
	$member_name = cleanUp($_POST['member_name']);
	$member_num = cleanUp($_POST['member_num']);
	$member_email = cleanUp($_POST['member_email']);
	$patch_url = cleanUp($_POST['patch_url']);
	$patch_desc = cleanUp($_POST['patch_desc']);
		
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
		
	$date_received = date("Y-m-d H:i:s", strtotime($year."-".$month."-".$day." ".$hour.":".$minute.":00") - ($admin->time_offset * 60 * 60));
	
	// Store $_FILES values in variables
	$file_name = $_FILES['patch']['name'];
	$file_size = $_FILES['patch']['size'];
	$file_type = $_FILES['patch']['type'];
	$file_temp = $_FILES['patch']['tmp_name'];
	$file_error = $_FILES['patch']['error'];
	
	$path = $_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/";
	
	// Validate Form Data
	if (!$cat->getCatById($catId)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>The category you selected does not exist. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}

	elseif (($cat->catId == 2 && $member_name == "") || ($cat->catId == 2 && $member_num == "") || ($cat->catId == 2 && $member_email == "") || ($cat->catId == 2 && ($patch_url == "" || $patch_url == "http://")) || $patch_desc == "") {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>You forgot to complete all the required fields. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	elseif ($cat->catId == 2 && !validEmail($member_email)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>The email address you entered is invalid. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	elseif ($cat->catId == 2 && (!is_numeric($member_num) || $member_num < 1 || $member_num > 350)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>The member number you entered is invalid. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	elseif (!is_dir($path.$cat->folder_name)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>The directory <code>$path$cat->folder_name</code> does not exist. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$dir_not_exist = true;
	
	elseif (!is_writable($path.$cat->folder_name)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>The directory <code>$path$cat->folder_name</code> is not writable. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$dir_not_writable = true;
		
	elseif ($file_error == 1) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>The uploaded file exceeds the upload_max_filesize directive in php.ini. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$upload_max_filesize = true;
	
	elseif ($file_error == 2) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Your image exceeds the maximum file size allowed (15kb). Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$form_file_size = true;
	
	elseif ($file_error == 3) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Your image was only partially uploaded. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$partial_upload = true;
	
	elseif ($file_error == 4) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>No image was uploaded. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$no_upload = true;
		
	elseif (!is_uploaded_file($file_temp)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Possible upload attack: filename <code>$file_temp</code>. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$file_attack = true;
		
	elseif (!$info = @getimagesize($file_temp)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Only valid images are allowed. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$invalid_img = true;
		
	elseif (($cat->catId == 1 || $cat->catId == 2) && ($info[0] != 40 || $info[1] != 40)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>The dimensions of the image you submitted are <strong>$info[0]x$info[1]</strong>.  Only 40x40 images are allowed. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$wrong_dim = true;
		
	elseif ($info[2] != 1 && $info[2] != 3) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Only .gif or .png images allowed. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		//$wrong_type = true;
		
	else {
		// Patch Filename
		if ($cat->catId == 2)
			$patch_stored = get_patch_filename($member_name, $member_num, $info[2]);
		else {
			if ($info[2] == 3)
				$ext = ".png";
			else
				$ext = ".gif";
			
			$patch_stored = cleanFileName($file_name).$ext;
		}
		
		$upload_path = $path.$cat->folder_name."/".$patch_stored;
		
		if (file_exists($upload_path) && $_POST['patch_replace'] != 1) {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>An image with the filename <code>$patch_stored</code> already exists. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
			//$file_exists = true;
		
		elseif (!move_uploaded_file($file_temp, $upload_path)) {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>Your image could not be uploaded. Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
			//$upload_failed = true;
		
		else {	
			// Add Patch
			$patch->addPatchAdmin($cat->catId, $displayId, $member_name, $member_num, $member_email, $patch_url, $patch_stored, $patch_desc, $date_received);
			
			// Re-Sort Patches
			sortPatchesAdd($patch->displayId, $patch->catId, $patch->patchId);
			
			// Success!
			$page_title = " &rsaquo; Patch Added";
			include("admin_header.php");
			echo "<h1>Patch Added</h1>\n";
			echo "<p>The following patch was successfully added to your quilt:</p>\n";
			echo "<p>".($patch_url != "" && $patch_url != "http://" ? "<a href=\"$patch_url\" title=\"$patch_desc\">" : "")."<img src=\"/$admin->patch_dir/$cat->folder_name/$patch_stored\" alt=\"$patch_desc\" width=\"$info[0]\" height=\"$info[1]\" />".($patch_url != "" && $patch_url != "http://" ? "</a>" : "")."</p>\n";
			echo "<ul>\n";
			echo "\t<li>Category: <a href=\"existing.php?catId=$cat->catId\" title=\"View patches in $cat->cat_name\">$cat->cat_name</a></li>\n";
			
			if ($member_name != "" && $member_num != "")
				echo "\t<li>Member: <a href=\"$patch_url\" title=\"$member_name\">$member_name</a> #<a href=\"http://theqbee.net/members.php\" title=\"Qbee Member List\">$member_num</a></li>\n";
			
			echo "\t<li>Patch Path: <code>".$admin->patch_dir."/".$cat->folder_name."/".$patch_stored."</code></li>\n";
			echo "\t<li>".$info[0] . "x" . $info[1] . " pixels</li>\n";
			echo "\t<li>".$file_size . " bytes</li>\n";
			echo "\t<li>Date: ".$patch->getPatchDate()." ".$patch->getPatchTime()."</li>\n";
			echo "</ul>\n";
			echo "<p><a href=\"existing.php\" title=\"Existing Patches\">&laquo; Return to Existing Patches</a> or <a href=\"add-patch.php\" title=\"Add a Patch\">Add Another Patch &raquo;</a></p>\n";
			include("admin_footer.php");
			exit;
		}
	}
}

include("admin_header.php");
?>

<h1>Add a Patch</h1>
<p>Use <em>Display Id</em> to choose the order in which you wish your patch to appear on your quilt.  However, you need to set your <a href="categories.php" title="Manage Categories">category</a> to <em>Sort Patches by Display Id</em>.</p>

<form enctype="multipart/form-data" action="" method="post">
<fieldset class="kinda-long">
	<legend>Add a Member Patch</legend>
	<input type="hidden" name="catId" value="2" />
	<p>All fields are required in order to add a <em>member</em> patch to your quilt. The member patch you upload will automatically be renamed to <?= $admin->patch_naming ?>.gif or <?= $admin->patch_naming ?>.png (You can change these settings in your <a href="profile.php" title="Edit Admin Profile">profile</a>.)</p>
	
	<ol>
		<li>
			<label for="displayId">Display ID</label>
			<input name="displayId" id="displayId" size="3" class="auto" />
			<p class="note">If you leave blank, the script will decide an appropriate display id.</p>
		</li>
		<li>
			<label for="member_name">Member Name</label>
			<input name="member_name" id="member_name" />
		</li>
		<li>
			<label for="member_num">Member #</label>
			<input name="member_num" id="member_num" size="3" class="auto"  />
		</li>
		<li>
			<label for="member_email">Member Email</label>
			<input name="member_email" id="member_email"  />
		</li>
		<li>
			<label for="patch_url">Member Website</label>
			<input name="patch_url" id="patch_url" value="http://"  />
		</li>
		<li>
			<label for="patch_desc">Description</label>
			<input name="patch_desc" id="patch_desc" onfocus="getDescriptionValue();" />
			<p class="note">Appears as the patch's <code>alt</code> and <code>title</code> attributes.</p>
		</li>
		<li>
			<label for="date_received">Date Received</label>
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
		<li>
			<label for="patch">Upload Patch</label>
			<input type="hidden" name="MAX_FILE_SIZE" value="15000" />
			<input type="file" name="patch" id="patch" class="auto" />
		</li>
		<li>
			<label for="patch_replace">Replace Existing File?</label>
			<input type="checkbox" name="patch_replace" id="patch_replace" value="1" class="auto" />
		</li>
	</ol>
</fieldset>

<p><input type="submit" value="Add Patch" class="button-kinda-long" /></p>
</form>

<form enctype="multipart/form-data" action="" method="post">
<fieldset class="kinda-long">
	<legend id="add-other-patch">Add Other Patch</legend>
	<ol>
<?php catDropdown("", true); ?>
		<li>
			<label for="displayId_other">Display ID</label>
			<input name="displayId" id="displayId_other" size="3" class="auto" />
			<p class="note">If you leave blank, the script will decide an appropriate display id.</p>
		</li>
		<li>
			<label for="patch_url_other">Website</label>
			<input name="patch_url" id="patch_url_other" value="http://"  />
			<p class="note">Optional; Your patch will be linked to this website.</p>
		</li>
		<li>
			<label for="patch_desc_other">Description</label>
			<input name="patch_desc" id="patch_desc_other" />
			<p class="note">Appears as the patch's <code>alt</code> and <code>title</code> attributes.</p>
		</li>
		<li>
			<label for="date_received">Date Received</label>
<?php monthDropdown($month, "_other"); ?>
<?php dayDropdown($day, "_other"); ?>
			<input name="year" id="year_other" value="<?= $year; ?>" size="4" class="auto" />
			@
<?php hourDropdown($hour, "_other"); ?>
			:
<?php minuteDropdown($minute, "_other"); ?>
			<select name="ampm" id="ampm_other" class="auto">
				<option value="am"<?= ($ampm == "am" ? " selected" : "") ?>>am</option>
				<option value="pm"<?= ($ampm == "pm" ? " selected" : "") ?>>pm</option>
			</select>
		</li>
		<li>
			<label for="patch_other">Upload Patch</label>
			<input type="hidden" name="MAX_FILE_SIZE" value="15000" />
			<input type="file" name="patch" id="patch_other" class="auto" />
		</li>
		<li>
			<label for="patch_replace_other">Replace Existing File?</label>
			<input type="checkbox" name="patch_replace" id="patch_replace_other" value="1" class="auto" />
		</li>
	</ol>
</fieldset>

<p><input type="submit" value="Add Patch" class="button-kinda-long" /></p>
</form>

<?
include("admin_footer.php");
?>
