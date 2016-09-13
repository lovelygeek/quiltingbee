<?php
/*

trade.php
This file processes the trade request form.
Do not edit unless you know what you are doing :)

*/

include("admin/config.php");
$admin->getAdmin();

$name = cleanUp($_POST['name']);
$email = cleanUp($_POST['email']);
$url = cleanUp($_POST['url']);
$patch_url = cleanUp($_POST['patch']);
$member_num = cleanUp($_POST['member_num']);

$cat = new Category();
$patch = new Patch();

if ($name == "" || $email == "" || $url == "" || $url == "http://" || $patch_url == "" || $patch_url == "http://" || $member_num == "" || $_POST['comments'] == "") {
	// Empty Fields
	include("header.php");
	echo "<h1 class=\"error\">Error</h1>\n";
	echo "<p class=\"error\">You forgot to complete the required form fields.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
	include("footer.php");
	exit;
}

if (!validEmail($email)) {
	// Invalid Email
	include("header.php");
	echo "<h1 class=\"error\">Error</h1>\n";
	echo "<p class=\"error\">Your email address is invalid.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
	include("footer.php");
	exit;
}

if (!is_numeric($member_num) || $member_num < 1 || $member_num > 350) {
	// Invalid Member #
	include("header.php");
	echo "<h1 class=\"error\">Error</h1>\n";
	echo "<p class=\"error\">Your member number is invalid.  Make sure it contains only numbers.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
	include("footer.php");
	exit;
}

if ($patch->getPatchByMemberNum($member_num)) {
	// Member # Exists
	include("header.php");
	echo "<h1 class=\"error\">Error</h1>\n";
	echo "<p class=\"error\">The member number <strong>$member_num</strong> is already taken by <a href=\"$patch->patch_url\" title=\"$patch->member_name\" target=\"_blank\">$patch->member_name</a>.  Perhaps he/she is a retired bee.  Contact $admin->name for more information.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
	include("footer.php");
	exit;
}

// Validate Patch
// If directory isn't writable or cURL isn't available, use patch_img_url
// Else, use cURL for error checking & auto-upload

$cat->getCatById(2);	// Member Category

$path = $_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir."/".$cat->folder_name."/";

if (!is_writable($path) || !function_exists('curl_init')) {
	$ext = strtolower(substr($patch_url, -4));
	
	if ($ext != ".gif" && $ext != ".png") {
		// Invalid Patch
		include("header.php");
		echo "<h1 class=\"error\">Error</h1>\n";
		echo "<p class=\"error\">Your patch must be exactly 40x40 pixels and either a .gif or .png.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("footer.php");
		exit;
	}
	
	if ($ext == ".png")
		$img_type = 3;
	else
		$img_type = 1;
		
	$patch_stored = get_patch_filename($name, $member_num, $img_type);
}

else {
	// Copy Temporary Image
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, $patch_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	
	// get the contents of the image into a string var
	$fileContents = curl_exec($ch);
	curl_close($ch);
	
	// give it a unique name
	$str = substr(md5(randomString(10)), 0, 10);
	$temp = $path.$str;
	
	// create a temp file and write the contents of the image to it
	$fp = fopen($temp, "wb+");
	fwrite($fp, $fileContents);
	fclose($fp);
	
	// Error Checking
	if (!$info = @getimagesize($temp)) {
		// Broken Image
		include("header.php");
		echo "<h1 class=\"error\">Error</h1>\n";
		echo "<p class=\"error\">Only valid images are allowed.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("footer.php");
		
		// Remove temp image
		unlink($temp);
		exit;
	}
			
	if ($info[0] != 40 || $info[1] != 40) {
		// Wrong Dimensions
		include("header.php");
		echo "<h1 class=\"error\">Error</h1>\n";
		echo "<p class=\"error\">The dimensions of your patch must 40x40 pixels.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("footer.php");
		
		// Remove temp image
		unlink($temp);
		exit;
	}
		
	if ($info[2] != 1 && $info[2] != 3) {
		// Wrong Type
		include("header.php");
		echo "<h1 class=\"error\">Error</h1>\n";
		echo "<p class=\"error\">Only .gif or .png patches allowed..  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("footer.php");
		
		// Remove temp image
		unlink($temp);
		exit;
	}
	
	$patch_stored = get_patch_filename($name, $member_num, $info[2]);
	
	// Image is valid; rename temp image
	rename($temp, $path.$patch_stored);
}

$patch_desc = $name . " #" . $member_num;

// Let's add trade to database
$patch->addPendingTrade($name, $member_num, $email, $url, $patch_url, $patch_stored, $patch_desc);

// Email admin about pending trade
$headers = "From: $name <" . $email . ">\r\n";
$headers .= "Reply-To: $name <" . $email . ">\r\n";

if ($admin->html_email) {
	// Send HTML Email
	$body = "<html><body>";
	$body .= "Hi $admin->name!  $name has requested to trade patches with you.<br /><br />";
	$body .= "Name: $name<br />";
	$body .= "Email: <a href=\"mailto:$email\" title=\"Email $name\">$email</a><br />";
	$body .= "Member #: <a href=\"http://www.theqbee.net/members.php\" title=\"Qbee Member List\" target=\"_blank\">$member_num</a><br />";
	$body .= "URL: <a href=\"$url\" target=\"_blank\">$url</a><br /><br />";
	
	// Was Patch Uploaded?
	if (@getimagesize($path.$patch_stored)) {
		$body .= "<img src=\"http://".$_SERVER['HTTP_HOST']."/".$admin->patch_dir."/".$cat->folder_name."/".$patch_stored."\" alt=\"$patch_desc\" title=\"$patch_desc\" /><br /><br />";
		$body .= "(This patch is saved in your member patch directory as <strong>$patch_stored</strong>)";
	}
	else {
		$body .= "<img src=\"$patch_url\" alt=\"$patch_desc\" title=\"$patch_desc\" /><br /><br />";
		$body .= "(Save this patch as <strong>$patch_stored</strong>)";
	}
	
	$body .= "<br /><br />";
	$body .= "Comments: ".stripslashes(strip_tags($_POST['comments']))."<br />";
	$body .= "</body></html>";
	
	// To send HTML mail, you can set the Content-type header.
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
}

else {
	// Send Plain Text Email
	$body = "Hi $admin->name! $name has requested to trade patches with you.\n\n";
	$body .= "Name: $name\n";
	$body .= "Email: $email\n";
	$body .= "Member #" .$member_num. "\n";
	$body .= "URL: $url\n";
	$body .= "Patch URL: $patch_url\n";
	
	// Was Patch Uploaded?
	if (@getimagesize($path.$patch_stored))
		$body .= "(This patch is saved in your member patch directory as $patch_stored)\n\n";
	else
		$body .= "(Save this patch as $patch_stored)\n\n";
	
	$body .= "Comments: ".stripslashes(strip_tags($_POST['comments']))."\n";
}

if (!mail($admin->email, "Qbee Trade", $body, $headers)) {
	// Mail couldn't be sent
}

// Success!
include("thanks.php");
?>
