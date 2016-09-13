<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head profile="http://gmpg.org/xfn/11">
<title>MyQuilt Admin<?= $page_title ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="admin.css" type="text/css" />
<script type="text/javascript" src="js/quicktags.js"></script>
<script type="text/javascript">

function getDescriptionValue() {
	var member_name = document.getElementById('member_name');
	var member_num = document.getElementById('member_num');
	var patch_desc = document.getElementById('patch_desc');
	
	if (patch_desc.value == "" && (member_name.value != "" || member_num.value != ""))
		patch_desc.value = member_name.value + ' #' + member_num.value;
}

</script>
</head>

<body>

<div id="wrap<?= (strstr($_SERVER['PHP_SELF'], "/admin/index.php") || strstr($_SERVER['PHP_SELF'], "/admin/sendpass.php") ? "-short" : "") ?>">
	<div id="header">
		<h1>MyQuilt Admin</h1>
<?php nav() ?>
	</div>
	
	<hr />
	
	<div id="content">
<?php printDeletionWarning(); ?>

