<?php

/*******************************************************************************
*
*	CATEGORIES
*	COMPLETE!
*
*******************************************************************************/

include("config.php");

$admin->adminBouncer();

$cat = new Category();

$page_title = " &rsaquo; Categories";

if ($_GET['action'] == "delete_confirm" && $cat->getCatById(cleanUp($_GET['catId'])) && $cat->catId != 1 && $cat->catId != 2) {
	$page_title .= " &rsaquo; Confirm Deletion";
	include("admin_header.php");
	echo "<h1>Confirm Deletion</h1>\n";
	echo "<p>Are you sure you want to delete the category <em>$cat->cat_name</em>?  The folder <code>$cat->folder_name</code> and its contents <strong>WILL BE DELETED</strong>!</p>\n";
	echo "<form action=\"?action=delete&amp;catId=$cat->catId\" method=\"post\">\n";
	echo "<fieldset>\n";
	echo "<input name=\"delete_confirm\" type=\"submit\" value=\"Yes\" />\n";
	echo "<input name=\"delete_confirm\" type=\"submit\" value=\"No\" />\n";
	echo "</fieldset>\n";
	echo "</form>\n";
	include("admin_footer.php");
	exit;
}

if ($_GET['action'] == "delete" && $cat->getCatById(cleanUp($_GET['catId'])) && $cat->catId != 1 && $cat->catId != 2) {
	if ($_POST['delete_confirm'] != "Yes") {
		header("Location: categories.php");
		exit;
	}
	
	$delete_folder = false;
	$deleted = false;
	
	if (!isFolderTaken($cat->folder_name, $cat->catId))
		$delete_folder = true;
	
	$cat->deleteCat($delete_folder);
	$deleted = true;
}

if (($_GET['action'] == "edit" && $cat->getCatById(cleanUp($_GET['catId']))) || $_GET['action'] == "add") {
	// Flag
	$success = false;
	
	// Add & Edit
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$cat_name = cleanUp($_POST['cat_name']);
		$cat_desc = cleanUpCode($_POST['cat_desc']);
		$folder_name = cleanFolderName($_POST['folder_name']);
		
		$use_fillers = $_POST['use_fillers'];
		
		if ($use_fillers != 1)
			$use_fillers = 0;
			
		$filler_url = cleanUp($_POST['filler_url']);
		$filler_alt = cleanUp($_POST['filler_alt']);
		
		$use_alt = $_POST['use_alt'];
		
		if ($use_alt != 1)
			$use_alt = 0;
			
		$alt_url = cleanUp($_POST['alt_url']);
		$alt_alt = cleanUp($_POST['alt_alt']);
		
		$sort_by = $_POST['sort_by'];
		
		if ($sort_by != "displayId" && $sort_by != "member_name" && $sort_by != "member_num")
			$sort_by = "date_received";
		
		$sort_how = $_POST['sort_how'];
		
		if ($sort_how != "desc")
			$sort_how = "asc";
			
		$perline = cleanUp($_POST['perline']);
		
		if (!is_numeric($perline) || $perline < 1)
			$perline = 5;
			
		$perpage = cleanUp($_POST['perpage']);
		
		if (!is_numeric($perpage) || $perpage < 1)
			$perpage = -1;
		
		// Error Checking
		if ($cat_name == "" || $folder_name == "" || ($use_fillers == 1 && $filler_alt == "") || ($use_alt == 1 && $alt_alt == "") || $perline == "" || $perpage == "") {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>You forgot to complete all the required fields.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
			
		/*
		elseif ($use_fillers == 1 && !$info = @getimagesize($filler_url))
			$invalid_filler = true;
			
		elseif ($use_alt == 1 && !$info = @getimagesize($alt_url))
			$invalid_alt = true;
		*/
			
		elseif (!is_writable($_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir)) {
			$page_title .= " &rsaquo; Error";
			include("admin_header.php");
			echo "<h1>Error</h1>\n";
			echo "<p>Your patch directory is not writable.  Update your patch directory in your <a href=\"profile.php\" title=\"Edit Admin Profile\">profile</a>.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
			include("admin_footer.php");
			exit;
		}
		
		elseif ($_GET['action'] == "edit") {
			// No Change - Update Database w/original folder name
			// Create - If doesn't exist, create
			// Rename - If doesn't exist, rename
			// Use Existing - If exists, use
			
			$old_path = $_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/".$cat->folder_name;
			$new_path = $_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/".$folder_name;
			
			if ($_POST['folder_action'] == "create" && is_dir($new_path)) {
				$page_title .= " &rsaquo; Error";
				include("admin_header.php");
				echo "<h1>Error</h1>\n";
				echo "<p>The directory <code>".$_SERVER['DOCUMENT_ROOT']."/$admin->patch_dir/$folder_name</code> already exists.  Please choose another folder name.  <a href=\"javascript:history.back();\" title=\"Go Back\">Go back</a> and try again.</p>\n";
				include("admin_footer.php");
				exit;
			}
			
			if ($_POST['folder_action'] == "create" && !(mkdir($new_path) && chmod($new_path, 0777))) {
				$page_title .= " &rsaquo; Error";
				include("admin_header.php");
				echo "<h1>Error</h1>\n";
				echo "<p>The folder $folder_name could not be created.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
				include("admin_footer.php");
				exit;
			}
			
			if ($_POST['folder_action'] == "rename" && is_dir($new_path)) {
				$page_title .= " &rsaquo; Error";
				include("admin_header.php");
				echo "<h1>Error</h1>\n";
				echo "<p>The directory <code>".$_SERVER['DOCUMENT_ROOT']."/$admin->patch_dir/$folder_name</code> already exists.  Please choose another folder name.  <a href=\"javascript:history.back();\" title=\"Go Back\">Go back</a> and try again.</p>\n";
				include("admin_footer.php");
				exit;
			}
			
			if ($_POST['folder_action'] == "rename" && isFolderTaken($cat->folder_name, $cat->catId)) {
				$page_title .= " &rsaquo; Error";
				include("admin_header.php");
				echo "<h1>Error</h1>\n";
				echo "<p>The current folder, <code>$cat->folder_name</code>, is in use by another category, therefore cannot be renamed.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
				include("admin_footer.php");
				exit;
			}
			
			if ($_POST['folder_action'] == "rename" && isFolderTaken($folder_name, $cat->catId)) {
				$page_title .= " &rsaquo; Error";
				include("admin_header.php");
				echo "<h1>Error</h1>\n";
				echo "<p>The folder <code>$folder_name</code> is currently in use by another category.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
				include("admin_footer.php");
				exit;
			}
			
			if ($_POST['folder_action'] == "rename" && !@rename($old_path, $new_path)) {
				$page_title .= " &rsaquo; Error";
				include("admin_header.php");
				echo "<h1>Error</h1>\n";
				echo "<p>The folder <code>$cat->folder_name</code> could not be renamed to <code>$folder_name</code>.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
				include("admin_footer.php");
				exit;
			}
			
			if ($_POST['folder_action'] == "existing" && !is_dir($new_path)) {
				$page_title .= " &rsaquo; Error";
				include("admin_header.php");
				echo "<h1>Error</h1>\n";
				echo "<p>The directory <code>".$_SERVER['DOCUMENT_ROOT']."/$admin->patch_dir/$folder_name</code> does not exist.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
				include("admin_footer.php");
				exit;
			}
			
			if ($_POST['folder_action'] == "none")
				$folder_name = $cat->folder_name;
			
			$cat->editCat($cat_name, $cat_desc, $folder_name, $use_fillers, $filler_url, $filler_alt, $use_alt, $alt_url, $alt_alt, $sort_by, $sort_how, $perline, $perpage);
			$success = true;
		}
		
		elseif ($_GET['action'] == "add") {
			$path = $_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/".$folder_name;
			
			if ($_POST['use_existing'] == 1 || (!is_dir($path) && mkdir($path) && chmod($path, 0777))) {
				$cat->addCat($cat_name, $cat_desc, $folder_name, $use_fillers, $filler_url, $filler_alt, $use_alt, $alt_url, $alt_alt, $sort_by, $sort_how, $perline, $perpage);
				$success = true;
			}
			else {
				$page_title .= " &rsaquo; Error";
				include("admin_header.php");
				echo "<h1>Error</h1>\n";
				echo "<p>The folder $folder_name could not be created.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
				include("admin_footer.php");
				exit;
			}
		}
	}
	
	if ($_GET['action'] == "edit") {
		$page_title = " &rsaquo; Categories &rsaquo; Edit";
		include("admin_header.php");
?>

<h1>Edit Category</h1>
<?php if ($success) : ?>
<p class="success">Category updated.</p>
<?php endif; ?>
<form action="categories.php?action=edit&amp;catId=<?= $cat->catId ?>" method="post">
<fieldset class="long">
	<ol>
		<li>
			<label for="cat_name">Name</label>
			<input name="cat_name" id="cat_name" value="<?= $cat->cat_name ?>" />
		</li>
		<li id="cat-qt">
			<label for="cat_desc">Description<br />(Optional)</label>
			<script type="text/javascript">edToolbar();</script>
			<textarea name="cat_desc" id="cat_desc" cols="25" rows="10"><?= stripslashes($cat->cat_desc) ?></textarea>
			<script type="text/javascript">var edCanvas = document.getElementById('cat_desc');</script>
		</li>
		<li>
			<label for="folder_name">Folder Name</label>
			<input name="folder_name" id="folder_name" value="<?= $cat->folder_name ?>" />
			<p class="note">Patches in this category are stored in this folder on your server.</p>
			<p class="note">The directory <code><?= $_SERVER['DOCUMENT_ROOT'] ?>/<?= $admin->patch_dir ?>/<?= $cat->folder_name ?></code> <?= (is_writable($_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/".$cat->folder_name) ? "is" : "<strong style=\"color: red;\">is not</strong>") ?> writable.</p>
		</li>
		<li>
			<label for="folder_action">Folder Action</label>
			<select name="folder_action" id="folder_action">
				<option value="none">No Change</option>
				<option value="create">Create New Folder</option>
				<option value="rename">Rename Current Folder</option>
				<option value="existing">Use an Existing Folder</option>
			</select>
		<li>
			<label for="use_fillers">Use Filler Patches?</label>
			<input type="checkbox" name="use_fillers" id="use_fillers" value="1" class="auto"<?= ($cat->use_fillers ? " checked" : "") ?> />
		</li>
		<li>
			<label for="filler_url">Filler Patch <acronym title="Uniform Resource Locator">URL</acronym></label>
			<input name="filler_url" id="filler_url" value="<?= ($cat->filler_url == "" ? "http://" : $cat->filler_url) ?>" />
		</li>
		<li>
			<label for="filler_alt">Filler Patch <code>alt</code> Attribute</label>
			<input name="filler_alt" id="filler_alt" value="<?= $cat->filler_alt ?>" />
		</li>
		<li>
			<label for="use_alt">Use Alternating Patches?</label>
			<input type="checkbox" name="use_alt" id="use_alt" value="1" class="auto"<?= ($cat->use_alt ? " checked" : "") ?> />
		</li>
		<li>
			<label for="alt_url">Alternating Patch <acronym title="Uniform Resource Locator">URL</acronym></label>
			<input name="alt_url" id="alt_url" value="<?= ($cat->alt_url == "" ? "http://" : $cat->alt_url) ?>" />
		</li>
		<li>
			<label for="alt_alt">Alternating Patch <code>alt</code> Attribute</label>
			<input name="alt_alt" id="alt_alt" value="<?= $cat->alt_alt ?>" />
		</li>
		<li>
			<label for="sort_by">Sort Patches By</label>
			<select name="sort_by" id="sort_by">
				<option value="displayId"<?= ($cat->sort_by == "displayId" ? " selected" : "") ?>>Display Id</option>
				<option value="member_name"<?= ($cat->sort_by == "member_name" ? " selected" : "") ?>>Member Name</option>
				<option value="member_num"<?= ($cat->sort_by == "member_num" ? " selected" : "") ?>>Member #</option>
				<option value="date_received"<?= ($cat->sort_by == "date_received" ? " selected" : "") ?>>Date</option>
			</select>
		</li>
		<li>
			<label for="sort_how">Order of Patches</label>
			<select name="sort_how" id="sort_how">
				<option value="asc"<?= ($cat->sort_how == "asc" ? " selected" : "") ?>>Ascending</option>
				<option value="desc"<?= ($cat->sort_how == "desc" ? " selected" : "") ?>>Descending</option>
			</select>
		</li>
		<li>
			<label for="perline"># of Patches Per Row</label>
			<input name="perline" id="perline" class="auto" size="3" value="<?= $cat->perline ?>" />
			<p class="note">This number <em>includes</em> alternating and filler patches.</p>
		</li>
		<li>
			<label for="perpage"># of Patches Per Page</label>
			<input name="perpage" id="perpage" class="auto" size="3" value="<?= $cat->perpage ?>" />
			<p class="note">Like <em>Patches Per Row</em>, this number <em>includes</em> alternating and filler patches.</p>
			<p class="note">If you do not want your quilt to appear on multiple pages, type <em>-1</em> in the textbox above.</p>
		</li>
	</ol>
</fieldset>
<p><input type="submit" value="Edit Category" class="button-long" /></p>
</form>

<?php
		include("admin_footer.php");
		exit;
	}
}

// Get Cats
$cats = getCats($cat_count);

include("admin_header.php");
?>

<h1>Categories (<a href="#add-cat" title="Add a Category">Add</a>)</h1>
<?php if ($deleted) : ?>
<p class="success">Category deleted successfully.</p>
<?php endif; ?>
<p>If you delete a category, its folder and contents will be deleted as well.  Be careful!</p>
<table cellpadding="5" cellspacing = "1" width="100%">
	<thead>
		<tr>
			<th scope="col">Id</th>
			<th scope="col">Name</th>
			<th scope="col">Folder Name</th>
			<th scope="col">Filler</th>
			<th scope="col">Alt</th>
			<th scope="col">Sort</th>
			<th scope="col">Patches</th>
			<th scope="col" colspan="3">Action</th>
		</tr>
	</thead>
	<tbody>
<?php

$i = 0;
foreach ($cats as $row) {
	$cat->getCatById($row['catId']);
	
	echo "\t\t<tr".($i % 2 == 0 ? ' class="alt"' : "").">\n";
	echo "\t\t\t<th scope=\"row\">$cat->catId</th>\n";
	echo "\t\t\t<td>$cat->cat_name</td>\n";
	echo "\t\t\t<td><code>$cat->folder_name</code></td>\n";
	echo "\t\t\t<td>".($cat->use_fillers && $cat->filler_url != "" && $cat->filler_url != "http://" ? "<img src=\"$cat->filler_url\" alt=\"$cat->filler_alt\" title=\"$cat->filler_alt\" />" : "N/A")."</td>\n";
	echo "\t\t\t<td>".($cat->use_alt && $cat->alt_url != "" && $cat->alt_url != "http://" ? "<img src=\"$cat->alt_url\" alt=\"$cat->alt_alt\" title=\"$cat->alt_alt\" />" : "N/A")."</td>\n";
	echo "\t\t\t<td>";
	
	switch($cat->sort_by)
	{
		case "displayId":
			echo "Display Id";
			break;
		case "member_name":
			echo "Member Name";
			break;
		case "member_num":
			echo "Member #";
			break;
		default:
			echo "Date";
			break;
	}
	
	echo ", ".($cat->sort_how == "asc" ? "<abbr title='Ascending'>" : "<abbr title='Descending'>")."".ucwords($cat->sort_how)."</abbr></td>\n";
	echo "\t\t\t<td>".$cat->patchCount()."</td>\n";
	echo "\t\t\t<td><a href=\"existing.php?catId=$cat->catId\" title=\"View Patches in Category\">View</a></td>\n";
	echo "\t\t\t<td><a href=\"?action=edit&amp;catId=$cat->catId\" title=\"Edit Category\">Edit</a></td>\n";
	echo "\t\t\t<td>".($cat->catId == 1 || $cat->catId == 2 ? "&ndash;" : "<a href=\"?action=delete_confirm&amp;catId=$cat->catId\" title=\"Delete Category\" class=\"delete\">Delete</a>")."</td>\n";
	echo "\t\t</tr>\n";
	$i++;
}

?>
	</tbody>
</table>

<h1 id="add-cat">Add a Category</h1>
<?php if ($success) : ?>
<p class="success">Category added.</p>
<?php endif; ?>
<form action="categories.php?action=add#add-cat" method="post">
<fieldset class="long">
	<ol>
		<li>
			<label for="cat_name">Name</label>
			<input name="cat_name" id="cat_name" />
		</li>
		<li id="cat-qt">
			<label for="cat_desc">Description<br />(Optional)</label>
			<script type="text/javascript">edToolbar();</script>
			<textarea name="cat_desc" id="cat_desc" cols="25" rows="10"></textarea>
			<script type="text/javascript">var edCanvas = document.getElementById('cat_desc');</script>
		</li>
		<li>
			<label for="folder_name">Folder Name</label>
			<input name="folder_name" id="folder_name" />
			<p class="note">This folder will be created in the directory <code><?= $_SERVER['DOCUMENT_ROOT'] ?>/<?= $admin->patch_dir ?>/</code></p>
			<p class="note">Patches in this category will be stored in this folder.</p>
		</li>
		<li>
			<label for="use_existing">Use Existing Folder?</label>
			<input type="checkbox" name="use_existing" id="use_existing" value="1" class="auto" />
		</li>
		<li>
			<label for="use_fillers">Use Filler Patches?</label>
			<input type="checkbox" name="use_fillers" id="use_fillers" value="1" class="auto" />
		</li>
		<li>
			<label for="filler_url">Filler Patch <acronym title="Uniform Resource Locator">URL</acronym></label>
			<input name="filler_url" id="filler_url" value="http://" />
		</li>
		<li>
			<label for="filler_alt">Filler Patch <code>alt</code> Attribute</label>
			<input name="filler_alt" id="filler_alt" />
		</li>
		<li>
			<label for="use_alt">Use Alternating Patches?</label>
			<input type="checkbox" name="use_alt" id="use_alt" value="1" class="auto" />
		</li>
		<li>
			<label for="alt_url">Alternating Patch <acronym title="Uniform Resource Locator">URL</acronym></label>
			<input name="alt_url" id="alt_url" value="http://" />
		</li>
		<li>
			<label for="alt_alt">Alternating Patch <code>alt</code> Attribute</label>
			<input name="alt_alt" id="alt_alt" />
		</li>
		<li>
			<label for="sort_by">Sort Patches By</label>
			<select name="sort_by" id="sort_by">
				<option value="displayId">Display Id</option>
				<option value="date_received">Date</option>
			</select>
		</li>
		<li>
			<label for="sort_how">Order of Patches</label>
			<select name="sort_how" id="sort_how">
				<option value="asc">Ascending</option>
				<option value="desc">Descending</option>
			</select>
		</li>
		<li>
			<label for="perline"># of Patches Per Row</label>
			<input name="perline" id="perline" class="auto" size="3" />
			<p class="note">This number <em>includes</em> alternating and filler patches.</p>
		</li>
		<li>
			<label for="perpage"># of Patches Per Page</label>
			<input name="perpage" id="perpage" class="auto" size="3" />
			<p class="note">Like <em>Patches Per Row</em>, this number <em>includes</em> alternating and filler patches.</p>
			<p class="note">If you do not want your quilt to appear on multiple pages, type <em>-1</em> in the textbox above.</p>
		</li>
	</ol>
</fieldset>
<p><input type="submit" value="Add Category" class="button-long" /></p>
</form>

<?
include("admin_footer.php");
?>
