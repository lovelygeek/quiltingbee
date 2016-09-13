<?
include("config.php");

$action = $_GET['action'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$username = cleanUp($_POST['username']);
	$password = md5($_POST['password']);
	
	$invalid_login = false;
	
	if (!$admin->getAdminByUsername($username) || strcmp($password, $admin->password) != 0)
		$invalid_login = true;
		
	else {
		$password = md5(SALT.$password);
		setcookie("myQa_adminUsername", $username, time() + 60*60*24*30*365, "/");
		setcookie("myQa_adminPassword", $password, time() + 60*60*24*30*365, "/");
		header("Location: ".$_POST['referrer']);
		exit;
	}
}

if ($action == "logout") {
	setcookie("myQa_adminUsername", "", time() - 60*60*24*30*365, "/");
	setcookie("myQa_adminPassword", "", time() - 60*60*24*30*365, "/");
	header("Location: index.php");
	exit;
}

if ($admin->isAdminLogged()) {
	header("Location: pending.php");
	exit;
}

$page_title = " &rsaquo; Login";
include("admin_header.php");
?>

<h1>Admin Login</h1>
<?php if ($invalid_login) : ?>
<p>The username or password you entered is incorrect.</p>
<?php endif; ?>
<form action="index.php" method="post">
<fieldset>
	<input type="hidden" name="referrer" value="<?= getReferrer() ?>" />
	<ol>
		<li><label for="username">Username</label> <input type="text" name="username" id="username" /></li>
		<li><label for="password">Password</label> <input type="password" name="password" id="password" /></li>
	</ol>
	<p><input type="submit" value="Login" class="button" /></p>
	<p style="margin-bottom: 10px;"><a href="sendpass.php" title="Forgot Your Password?">Forgot Your Password?</a></p>
</fieldset>
</form>

<?php
include("admin_footer.php");
?>
