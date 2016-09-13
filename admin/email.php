<?php

/*******************************************************************************
*
*	EMAIL
*
*******************************************************************************/

include("config.php");

$admin->adminBouncer();

$member = new Patch();
$members = getMembers();
$addresses = array();

// Flags
$email_all_success = false;
$email_single_success = false;

$page_title = " &rsaquo; Email";

if ($_GET['action'] == "email_all" || $_GET['action'] == "email_single") {
	$memberId = cleanUp($_POST['memberId']);
	$subject = stripslashes(strip_tags($_POST['subject']));
	$name = stripslashes(strip_tags($_POST['name']));
	$email = stripslashes(strip_tags($_POST['email']));
	$url = stripslashes(strip_tags($_POST['url']));
	$member_num = stripslashes(strip_tags($_POST['member_num']));
	$message = stripslashes(strip_tags($_POST['message']));
	
	if ($subject == "" || $name == "" || $email == "" || $url == "" || $url == "http://" || !is_numeric($member_num) || $member_num < 1 || $message == "") {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>You forgot to complete all the fields.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	if ($_GET['action'] == "email_single" && !$member->getPatchById($memberId)) {
		$page_title .= " &rsaquo; Error";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>You must select a valid member if you want to email him/her.  Please <a href=\"javascript:history.back();\" title=\"Go Back\">go back</a> and try again.</p>\n";
		include("admin_footer.php");
		exit;
	}
	
	// Prepare Email
	$body = "The following email was sent from $name #$member_num ($url) through MyQuilt Admin:\n\n";
	$body .= $message;
	
	$headers = "From: " .$name. " <" .$email. ">\r\n";
	$headers .= "Reply-To: " .$name. " <" .$email. ">\r\n";
	
	if ($_GET['action'] == "email_all") {
		if ($_POST['cc'] == 1) 
			mail($admin->email, $subject, $body, $headers);
		
		foreach ($members as $row)
			mail($row['member_email'], $subject, $body, $headers);
			
		$email_all_success = true;
	}
	
	elseif ($_GET['action'] == "email_single") {
		if ($_POST['cc'] == 1)
			$headers .= "Cc: " .$name. " <" .$email. ">\r\n";
		
		mail($member->member_email, $subject, $body, $headers);
		$email_single_success = true;
	}
	
}

if ($_GET['action'] == "get_email") {
	$id_arr = $_POST['id_arr'];
	
	if (count($id_arr) > 0)
		foreach ($id_arr as $val)
			if ($member->getPatchById(cleanUp($val)))
				array_push($addresses, $val);
}

include("admin_header.php");
?>

<h1>Email All</h1>
<div id="email-split">
	<textarea cols="25" rows="5" id="email_cont" readonly>
<?php
if (count($members) > 0) {
	foreach ($members as $row) {
		$member->getPatchById($row['patchId']);
		
		echo $member->member_email.",\n";
	}
}
?>
	</textarea>
	<p>You can copy and paste the collection of emails from this textbox into your email program or use the form below.</p>
</div>

<h1 id="email-all-form">Email All Using Form</h1>
<?php if ($email_all_success) : ?>
<p class="success">Your email has been sent.</p>
<?php endif; ?>
<form action="?action=email_all#email-all-form" method="post">
<fieldset style="margin-bottom: 0;">
	<ol>
		<li>
			<label for="all_subject">Email Subject</label>
			<input name="subject" id="all_subject" />
		</li>
		<li>
			<label for="all_name">Your Name</label>
			<input name="name" id="all_name" value="<?= $admin->name ?>" />
		</li>
		<li>
			<label for="all_email">Your Email</label>
			<input name="email" id="all_email" value="<?= $admin->email ?>" />
		</li>
		<li>
			<label for="all_member_num">Your Member #</label>
			<input name="member_num" id="all_member_num" value="<?= $admin->member_num ?>" />
		</li>
		<li>
			<label for="all_url">Your <acronym title="Uniform Resource Locator">URL</acronym></label>
			<input name="url" id="all_url" value="http://" />
		</li>
		<li>
			<label for="all_message">Email Message</label>
			<textarea name="message" id="all_message" cols="40" rows="10" class="auto"></textarea>
		</li>
		<li>
			<label for="all_cc">Send Copy to You?</label>
			<input type="checkbox" id="all_cc" name="cc" value="1" class="auto" />
		</li>
	</ol>
</fieldset>
<p><input type="submit" value="Send Message" class="button" /></p>
</form>

<h1 id="email-single">Email Single Member</h1>
<?php if ($email_single_success) : ?>
<p class="success">Your email has been sent.</p>
<?php endif; ?>
<form action="?action=email_single#email-single" method="post">
<fieldset style="margin-bottom: 0;">
	<ol>
		<li>
			<label for="single_member">Member</label>
			<select name="memberId" id="single_member">
<?php
if (count($members) > 0) {
	foreach ($members as $row) {
		$member->getPatchById($row['patchId']);
		
		echo "\t\t\t\t<option value=\"$member->patchId\">$member->member_name #$member->member_num</option>\n";
	}
}
?>
			</select>
		</li>
		<li>
			<label for="single_subject">Email Subject</label>
			<input name="subject" id="single_subject" />
		</li>
		<li>
			<label for="single_name">Your Name</label>
			<input name="name" id="single_name" value="<?= $admin->name ?>" />
		</li>
		<li>
			<label for="single_email">Your Email</label>
			<input name="email" id="single_email" value="<?= $admin->email ?>" />
		</li>
		<li>
			<label for="single_member_num">Your Member #</label>
			<input name="member_num" id="single_member_num" value="<?= $admin->member_num ?>" />
		</li>
		<li>
			<label for="single_url">Your <acronym title="Uniform Resource Locator">URL</acronym></label>
			<input name="url" id="single_url" value="http://" />
		</li>
		<li>
			<label for="single_message">Email Message</label>
			<textarea name="message" id="single_message" cols="40" rows="10" class="auto"></textarea>
		</li>
		<li>
			<label for="single_cc">Send Copy to You?</label>
			<input type="checkbox" id="single_cc" name="cc" value="1" class="auto" />
		</li>
	</ol>
</fieldset>
<p><input type="submit" value="Send Message" class="button" /></p>
</form>

<h1>Get Member Email(s)</h1>
<?php if (count($addresses) > 0) {
	echo "<textarea cols=\"60\" rows=\"5\" id=\"address_cont\" readonly>\n";
	
	foreach ($addresses as $val) {
		$member->getPatchById($val);
		
		echo $member->member_name." #".$member->member_num." &lt;".$member->member_email."&gt;,\n";
	}
	
	echo "</textarea><br /><br />\n";
}
?>
<form action="?action=get_email#address_cont" method="post">
<fieldset style="margin-bottom: 0;">
	<ol>
		<li>
			<label for="select_mem">Select Member(s)</label>
			<select name="id_arr[]" id="select_mem" multiple>
<?php
if (count($members) > 0) {
	foreach ($members as $row) {
		$member->getPatchById($row['patchId']);
		
		echo "\t\t\t<option value=\"$member->patchId\">$member->member_name #$member->member_num</option>\n";
	}
}
?>
			</select>
		</li>
	</ol>
</fieldset>
<p><input type="submit" value="Retrieve" class="button" /></p>
</form>

<?php
include("admin_footer.php");
?>
