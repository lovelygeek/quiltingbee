<?
include("config.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$username = cleanUp($_POST['username']);
	
	$bad_username = false;
	
	if (!$admin->getAdminByUsername($username))
		$bad_username = true;
	
	else {
		// Reset Password
		$newpass = randomString();
		$admin->resetPassword(md5($newpass));
		
		// Email
		$subject = "MyQuilt Admin: Login Information";
		
		$msg = "Hello, $admin->name.  Either you or someone else has requested login information for MyQuilt Admin.\n";
		$msg .= "Your password has been reset for security reasons.  Your new password is:\n\n";
		$msg .= $newpass;
		
		mail($admin->email, $subject, $msg, $admin->mailHeaders);
		
		// Print Success Page
		$page_title = " &rsaquo; Password Reset";
		include("admin_header.php");
		echo "<h1>Password Reset</h1>\n";
		echo "<p style=\"margin-bottom: 10px;\">Your new password has been sent to <strong>".$admin->email."</strong>.  <a href='index.php' title='Login'>Login &raquo;</a></p>\n";
		include("admin_footer.php");
		exit;
	}
}

$page_title = " &rsaquo; Send Password";
include("admin_header.php");
?>

<h1>Send Password</h1>
<?php if ($bad_username) : ?>
<p class="error">The username you entered is incorrect.</p>
<?php else : ?>
<p>Don't forget to check your inbox for your new password.</p>
<?php endif; ?>

<form action="" method="post">
<fieldset>
	<ol>
		<li>
			<label for="username">Username</label>
			<input id="username" name="username" />
		</li>
	</ol>
</fieldset>

<p><input type="submit" value="Send Password" class="button" /></p>
<p style="margin-bottom: 10px;"><a href="index.php" title="Return to Login">&laquo; Return to Login</a></p>
</form>

<?
include("admin_footer.php");
?>
