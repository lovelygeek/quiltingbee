<?php
include("config.php");

$admin->adminBouncer();

$cat = new Category();
$patch = new Patch();

$page_title = " &rsaquo; Pending Trades";

if ($_GET['action'] == "update" && $patch->getPatchById(cleanUp($_POST['patchId']))) {
	if ($_POST['approve'] == 1)
		// Approve Trade
		$patch->approvePendingTrade();
	
	elseif ($_POST['approve'] == 0) 
		// Delete Trade
		$patch->deletePatch();
	
	header("Location: pending.php");
	exit;
}

include("admin_header.php");

echo "<h1>Pending Trades</h1>\n";

$patches = getPatches($patchCount, 0, "", "", 2, "date_received", "desc");

if ($patchCount > 0) :
?>
<ol>
<?php

$i=0;
foreach ($patches as $row) {
	$patch->getPatchById($row['patchId']);
	$cat->getCatById($patch->catId);
	
	echo "\t<li>\n";
	
	echo "\t\tName: $patch->member_name<br />\n";
	echo "\t\tEmail: <a href=\"mailto:$patch->member_email\" title=\"Email $patch->member_name\">$patch->member_email</a><br />\n";
	echo "\t\t<acronym title=\"Uniform Resource Locator\">URL</acronym>: <a href=\"$patch->patch_url\" title=\"Visit $patch->member_name's Website\" target=\"_blank\">$patch->patch_url</a><br />\n";
	echo "\t\tMember #<a href=\"http://theqbee.net/members.php\" title=\"Qbee Member List\" target=\"_blank\">$patch->member_num</a><br /><br />\n";
	
	echo "\t\t<p>\n";
	
	// Was Patch Uploaded?
	if ($info = $patch->patchImageExist()) {
		echo "\t\t\t<img src=\"".$info['rel_path']."\" alt=\"$patch->patch_desc\" title=\"$patch->patch_desc\" width=\"40\" height=\"40\" />";
	}
	else {
		echo "\t\t\t<img src=\"$patch->patch_img_url\" alt=\"$patch->patch_desc\" title=\"$patch->patch_desc\" width=\"40\" height=\"40\" style=\"float: left; padding-right: 10px;\" />\n";
		echo "\t\t\tRight click and save this image as <strong>$patch->patch_stored</strong>.<br />\n";
		echo "\t\t\tYou can upload it <a href=\"#pending-upload\" title=\"Upload a Patch\">here</a>.\n";
	}
	
	echo "\t\t</p>\n";
	
	echo "\t\t<form action=\"?action=update\" method=\"post\">\n";
	echo "\t\t<fieldset>\n";
	echo "\t\t\t<input type=\"hidden\" name=\"patchId\" value=\"$patch->patchId\" />\n";
	echo "\t\t\t<input type=\"radio\" name=\"approve\" id=\"approve-$i\" value=\"1\" checked /> <label for=\"approve-$i\">Approve</label><br />\n";
	echo "\t\t\t<input type=\"radio\" name=\"approve\" id=\"delete-$i\" value=\"0\" /> <label for=\"delete-$i\">Delete</label><br />\n";
	echo "\t\t</fieldset>\n";
	echo "\t\t<p><input type=\"submit\" value=\"Update\" /></p>\n";
	echo "\t\t</form>\n";
	
	echo "\t</li>\n";
	$i++;
}

?>
</ol>

<h1 id="pending-upload">Upload a Patch</h1>
<form enctype="multipart/form-data" action="existing.php?action=upload" method="post">
<fieldset style="margin-bottom: 0;">
	<input type="hidden" name="catId" value="2" />
	<input type="hidden" name="isPending" value="1" />
	<ol>
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

<?php
else :
?>
<p>No pending trades.</p>

<?
endif;
include("admin_footer.php");
?>
