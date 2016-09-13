<?php

/*******************************************************************************
*
*	ADMIN PROFILE
*	COMPLETE!
*
*******************************************************************************/

include("config.php");

$admin->adminBouncer();

$page_title = " &rsaquo; Edit Admin Profile";

if ($_POST['which_form'] == "profile") {
	$name = cleanUp($_POST['name']);
	$email = cleanUp($_POST['email']);
	$member_num = cleanUp($_POST['member_num']);
	
	$html_email = $_POST['html_email'];
	
	if ($html_email != 0)
		$html_email = 1;
	
	$patch_naming = $_POST['patch_naming'];
	
	if ($patch_naming != "#name" && $patch_naming != "#")
		$patch_naming = "name#";
	
	$time_offset = cleanUp($_POST['time_offset']);
	
	if (!is_numeric($time_offset))
		$time_offset = 0;
	
	$date_format = cleanUp($_POST['date_format']);
	$time_format = cleanUp($_POST['time_format']);
	$patch_dir = trim(cleanUp($_POST['patch_dir']), "/");
		
	// Flag
	$success = false;
	
	if ($name == "" || $email == "" || $member_num == "" || $time_offset == "" || $date_format == "" || $time_format == "") {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>You forgot to complete all the required fields.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	elseif (!validEmail($email)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Your email address is invalid.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	elseif (!is_numeric($member_num) || $member_num < 1) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Your member number is invalid.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	elseif (!is_dir($_SERVER['DOCUMENT_ROOT']."/".$patch_dir)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>The directory <code>".$_SERVER['DOCUMENT_ROOT']."/$patch_dir</code> does not exist.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	elseif (!is_writable($_SERVER['DOCUMENT_ROOT']."/".$patch_dir)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>The directory <code>".$_SERVER['DOCUMENT_ROOT']."/$patch_dir</code> is not writable.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	else {
		// Success!
		$admin->editProfile($name, $email, $member_num, $html_email, $patch_naming, $date_format, $time_format, $time_offset, $patch_dir);
		$success = true;
	}
}

elseif ($_POST['which_form'] == "login") {
	$username = cleanUp($_POST['username']);
	$password = md5($_POST['password']);
	
	// Flags
	$empty_fields_login = false;
	$improper_username = false;
	$pass_mismatch = false;
	
	if ($username == "" || $_POST['password'] == "") {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>You forgot to complete all the required fields.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	elseif (!isProperUsername($username)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Your username must contain only letters, digits, hyphens, or underscores.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	elseif (strcmp($_POST['password'], $_POST['pass_check']) != 0) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Your confirmed password did not match your chosen password.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
		
	else {
		$admin->editLoginInfo($username, $password);
		
		setcookie("myQa_adminUsername", "", time() - 60*60*24*30*365, "/");
		setcookie("myQa_adminPassword", "", time() - 60*60*24*30*365, "/");
		
		$page_title = " &rsaquo; Login Information Updated";
		include("admin_header.php");
		echo "<h1>Login Information Updated</h1>\n";
		echo "<p>Your login information has been updated.  You will need to <a href='index.php'>login</a> again.</p>\n";
		include("admin_footer.php");
		exit;
	}
}

include("admin_header.php");
?>

<h1>Edit Admin Profile</h1>
<?php if ($success) : ?>
<p class="success">Profile updated.</p>
<?php endif; ?>

<form action="profile.php" method="post">
<fieldset>
	<input type="hidden" name="which_form" value="profile" />
	<legend>Your Information</legend>
	<ol>
		<li>
			<label for="name">Name</label>
			<input name="name" id="name" value="<?= $admin->name ?>" />
		</li>
		<li>
			<label for="email">Email</label>
			<input name="email" id="email" value="<?= $admin->email ?>" />
		</li>
		<li>
			<label for="member_num">Member #</label>
			<input name="member_num" id="member_num" value="<?= $admin->member_num ?>" size="3" class="auto" />
		</li>
		<li class="radio">
			<p class="question">What type of email do you want to receive when someone requests to trade?</p>
			<input type="radio" name="html_email" id="html_email_y" value="1" class="radio"<?= ($admin->html_email ? " checked" : "") ?> />
			<label for="html_email_y">HTML</label>
			<input type="radio" name="html_email" id="html_email_n" value="0" class="radio"<?= (!$admin->html_email ? " checked" : "") ?> />
			<label for="html_email_n">Plain Text</label>
		</li>
	</ol>
</fieldset>

<fieldset>
<legend style="margin-bottom: 0;">Quilt Preferences</legend>
	<ol>
		<li class="radio">
			<p class="question">How do you want your member patches to be named?</p>
			<input type="radio" name="patch_naming" id="name_num" value="name#" class="radio"<?= ($admin->patch_naming == "name#" ? " checked" : "") ?> />
			<label for="name_num">bubs77.gif</label><br />
			<input type="radio" name="patch_naming" id="num_name" value="#name" class="radio"<?= ($admin->patch_naming == "#name" ? " checked" : "") ?> />
			<label for="num_name">77bubs.gif</label><br />
			<input type="radio" name="patch_naming" id="num" value="#" class="radio"<?= ($admin->patch_naming == "#" ? " checked" : "") ?> />
			<label for="num">77.gif</label>
		</li>
		<li>
			<p class="time"><span><abbr title="Greenwich Mean Time">GMT</abbr></span> <code><?= gmdate("Y-m-d h:i:s a") ?></code></p>
			<label for="time_offset">Time Offset</label>
			<input name="time_offset" id="time_offset" value="<?= $admin->time_offset ?>" class="auto" size="3" />
			<p class="time"><span>Your Time</span> <code><?= gmdate("Y-m-d h:i:s a", time() + ($admin->time_offset * 60 * 60)) ?></code></p>
		</li>
		<li>
			<label for="date_format">Date Format</label>
			<input name="date_format" id="date_format" value="<?= $admin->date_format ?>" />
			<p class="note">Currently: <strong><?= gmdate($admin->date_format, time() + ($admin->time_offset * 60 * 60)) ?></strong> (<a href="http://php.net/date/" title="Date format information">Date format info</a>)</p>
		</li>
		<li>
			<label for="time_format">Time Format</label>
			<input name="time_format" id="time_format" value="<?= $admin->time_format ?>" />
			<p class="note">Currently: <strong><?= gmdate($admin->time_format, time() + ($admin->time_offset * 60 * 60)) ?></strong> (<a href="http://php.net/date/" title="Time format information">Time format info</a>)</p>
		</li>
	</ol>
</fieldset>

<fieldset class="directory">
<legend>Patch Directory</legend>
<p>This directory must be writable in order to upload patches or create categories.</p>
	<ol>
		<li>
			<label for="patch_dir"><code><?= $_SERVER['DOCUMENT_ROOT'] ?>/</code></label>
			<input name="patch_dir" id="patch_dir" value="<?= $admin->patch_dir ?>" />
			<p class="note"><code><?= $_SERVER['DOCUMENT_ROOT'] ?>/<?= $admin->patch_dir ?></code> <?= (is_writable($_SERVER['DOCUMENT_ROOT']."/".$admin->patch_dir) ? "is" : "<strong style=\"color: red;\">is not</strong>") ?> writable.</p>
		</li>
	</ol>
</fieldset>

<p><input type="submit" value="Edit Profile" /></p>
</form>

<h1 id="login_form">Login Information</h1>
<form action="profile.php#login_form" method="post">
<fieldset>
	<input type="hidden" name="which_form" value="login" />
	<ol>
		<li>
			<label for="username">Username</label>
			<input type="text" name="username" id="username" value="<?= $admin->username ?>" />
			<p class="note">Only letters, digits, hyphens &amp; underscores allowed.</p>
		</li>
		<li>
			<label for="password">Password</label>
			<input type="password" name="password" id="password" />
		</li>
		<li>
			<label for="pass_check">Re-Type Password</label>
			<input type="password" name="pass_check" id="pass_check" />
		</li>
	</ol>
</fieldset>

<p><input type="submit" value="Edit Login Info" class="button" /></p>
</form>

<?
include("admin_footer.php");
?>
