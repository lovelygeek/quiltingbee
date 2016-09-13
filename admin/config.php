<?
/*******************************************************************************
*
*	MyQuilt Admin Database Information
*
*******************************************************************************/

$user = "cena7508";		// MySQL Username
$host = "quilt.qbee.lovelygeek.net";		// 99% of the time, this remains as 'localhost'  If it doesn't work, check with your webspace provider.
$pw = "aquarius";		// MySQL Password
$database = "quilt_lovelygeek";	// Database for MyQuilt Admin
$table_prefix  = "mqa_";	// MyQuilt Admin Table Prefix (distinguish MyQuilt database tables from others)
   				// Must be unique; numbers, letters, and underscores only!
				
$secret = "635453";		// This is like a second password.  You won't have to remember it, so make it random.
				// (Thanks Jem - http://jemjabella.co.uk)

/*******************************************************************************
*
*	DO NOT EDIT BELOW THIS LINE!!
*
*******************************************************************************/

$connection = mysql_connect($host,$user,$pw) or die ("Couldn't connect to server.  MySQL Error: ".mysql_error()."<br /><br />Double-check the variables in your config.php file!");
mysql_select_db($database,$connection) or die ("Couldn't select database.  MySQL Error: ".mysql_error()."<br /><br />Double-check the variables in your config.php file!");

define(TODAY, gmdate("Y-m-d H:i:s"));
define(TABLE_PREFIX, $table_prefix);
define(SALT, $secret);

/* MyQuilt Admin Version */
define(MQA_VERSION, "3.0.2");

/*******************************************************************************
*
*	ADMIN FUNCTIONS
*
*******************************************************************************/

function nav() {
	global $admin;
	
	if (!isInstalled())
		return;
		
	if (strstr($_SERVER['PHP_SELF'], "/admin/sendpass.php") || strstr($_SERVER['PHP_SELF'], "/admin/install.php") || strstr($_SERVER['PHP_SELF'], "/admin/upgrade2.0.php") || strstr($_SERVER['PHP_SELF'], "/admin/upgrade3.0.php"))
		return;
	
	if (!$admin->isAdminLogged())
		return;
	
	echo "\t\t<ul>\n";
	echo "\t\t\t<li id=\"nav-profile\"><a href=\"profile.php\" title=\"Edit Admin Profile\"".(strstr($_SERVER['PHP_SELF'], "/admin/profile.php") ? " class=\"selected\"" : "").">Profile</a></li>\n";
	echo "\t\t\t<li id=\"nav-pending\"><a href=\"pending.php\" title=\"Pending Trades\"".(strstr($_SERVER['PHP_SELF'], "/admin/pending.php") ? " class=\"selected\"" : "").">Pending</a></li>\n";
	echo "\t\t\t<li id=\"nav-existing\"><a href=\"existing.php\" title=\"Existing Patches\"".(strstr($_SERVER['PHP_SELF'], "/admin/existing.php") ? " class=\"selected\"" : "").">Existing</a></li>\n";
	echo "\t\t\t<li id=\"nav-add\"><a href=\"add-patch.php\" title=\"Add a Patch\"".(strstr($_SERVER['PHP_SELF'], "/admin/add-patch.php") ? " class=\"selected\"" : "").">Add Patch</a></li>\n";
	echo "\t\t\t<li id=\"nav-categories\"><a href=\"categories.php\" title=\"Categories\"".(strstr($_SERVER['PHP_SELF'], "/admin/categories.php") ? " class=\"selected\"" : "").">Categories</a></li>\n";
	echo "\t\t\t<li id=\"nav-blog\"><a href=\"blog.php\" title=\"Blog\"".(strstr($_SERVER['PHP_SELF'], "/admin/blog.php") ? " class=\"selected\"" : "").">Blog</a></li>\n";
	echo "\t\t\t<li id=\"nav-email\"><a href=\"email.php\" title=\"Email\"".(strstr($_SERVER['PHP_SELF'], "/admin/email.php") ? " class=\"selected\"" : "").">Email</a></li>\n";
	echo "\t\t\t<li id=\"nav-logout\"><a href=\"index.php?action=logout\" title=\"Logout\">Logout</a></li>\n";
	echo "\t\t\t<li id=\"nav-quilt\"><a href=\"../index.php\" title=\"View Your Quilt\">Quilt</a></li>\n";
	echo "\t\t</ul>\n";
}

function tableExists($tableName) {
	global $database;
	
	if (mysql_num_rows(mysql_query("SHOW TABLES FROM ".escape($database)." LIKE '".TABLE_PREFIX.escape($tableName)."'")) > 0)
		return true;

	return false;
}

function isInstalled() {
	if (tableExists("admin") && tableExists("categories") && tableExists("blog") && tableExists("patches"))
		return true;
	return false;
}

function isInstalledBouncer() {
	if (strstr($_SERVER['PHP_SELF'], "/admin/install.php") || strstr($_SERVER['PHP_SELF'], "/admin/upgrade2.0.php") || strstr($_SERVER['PHP_SELF'], "/admin/upgrade3.0.php"))
		return;
	
	if (!isInstalled()) {
		$page_title = " &rsaquo; Not Installed!";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Please <a href=\"install.php\" title=\"Install MyQuilt Admin\">install</a> or <a href=\"upgrade2.0.php\" title=\"Upgrade MyQuilt Admin\">upgrade</a> MyQuilt Admin before using it.</p>";
		include("admin_footer.php");
		exit;
	}
}

function printDeletionWarning() {
	global $admin;
	
	if (!isInstalled())
		return;
	
	if (strstr($_SERVER['PHP_SELF'], "/admin/index.php") || strstr($_SERVER['PHP_SELF'], "/admin/sendpass.php") || strstr($_SERVER['PHP_SELF'], "/admin/install.php") || strstr($_SERVER['PHP_SELF'], "/admin/upgrade2.0.php") || strstr($_SERVER['PHP_SELF'], "/admin/upgrade3.0.php"))
		return;
		
	if (file_exists(dirname(__FILE__)."/install.php") || file_exists(dirname(__FILE__)."/upgrade2.0.php") || file_exists(dirname(__FILE__)."/upgrade3.0.php"))
		echo '<p class="warning">You need to delete the files <strong>admin/install.php</strong>, <strong>admin/upgrade2.0.php</strong> and <strong>admin/upgrade3.0.php</strong> from your server!</p>';
}

function isDbCurrent() {
	$result = mysql_query("SELECT version FROM ".TABLE_PREFIX."admin WHERE id = '1'");
	
	if (@mysql_num_rows($result) < 1)
		return false;
		
	$row = mysql_fetch_array($result);
	
	if (strcmp($row['version'], MQA_VERSION) != 0)
		return false;
		
	return true;
}

function isDbCurrentBouncer() {
	if (strstr($_SERVER['PHP_SELF'], "/admin/install.php") || strstr($_SERVER['PHP_SELF'], "/admin/upgrade2.0.php") || strstr($_SERVER['PHP_SELF'], "/admin/upgrade3.0.php"))
		return;
	
	if (!isDbCurrent()) {
		$page_title = " &rsaquo; Database Not Current!";
		include("admin_header.php");
		echo "<h1>Error</h1>\n";
		echo "<p>Please <a href=\"upgrade3.0.php\" title=\"Upgrade MyQuilt Admin\">upgrade</a> MyQuilt Admin's database before using it.</p>";
		include("admin_footer.php");
		exit;
	}
}

function getCats(&$count) {
	$query = "SELECT catId FROM ".TABLE_PREFIX."categories ORDER BY catId ASC";
	$result = mysql_query($query);
	
	$count = mysql_num_rows($result);
	
	$cats = array();
	while ($row = mysql_fetch_array($result)) {
		array_push($cats, $row);
	}
	return $cats;
}

function catDropdown($selected = "", $exclude_member_cat = false) {
	$query = "SELECT * FROM ".TABLE_PREFIX."categories".($exclude_member_cat ? " WHERE catId != 2" : "")." ORDER BY cat_name ASC";
	$result = mysql_query($query);
	
	echo "\t\t<li>\n";
	echo "\t\t\t<label for=\"catId\">Category</label>\n";
	echo "\t\t\t<select name=\"catId\" id=\"catId\">\n";
	
	while ($row = mysql_fetch_array($result))
		echo "\t\t\t\t<option value=\"".$row['catId']."\"".($selected == $row['catId'] ? " selected" : "").">".$row['cat_name']."</option>\n";
	
	echo "\t\t\t</select>\n";
	echo "\t\t</li>\n";
}

function catDropdownSearch($selected = "") {
	$query = "SELECT * FROM ".TABLE_PREFIX."categories ORDER BY cat_name ASC";
	$result = mysql_query($query);
	
	echo "\t\t<li>\n";
	echo "\t\t\t<label for=\"search_catId\">Browse Category</label>\n";
	echo "\t\t\t<select name=\"catId\" id=\"search_catId\">\n";
	echo "\t\t\t\t<option value=\"\"".($selected == "" ? " selected" : "").">All</option>\n";
	
	while ($row = mysql_fetch_array($result))
		echo "\t\t\t\t<option value=\"".$row['catId']."\"".($selected == $row['catId'] ? " selected" : "").">".$row['cat_name']."</option>\n";
	
	echo "\t\t\t</select>\n";
	echo "\t\t</li>\n";
}

function cleanFolderName($folder_name) {
	$folder_name = preg_replace('/[^a-z0-9_\-[:blank:]{1}]/i', '', strtolower($folder_name));;
	$folder_name = str_replace(" ", "_", $folder_name);
	return $folder_name;
}
	
function isFolderTaken($folder_name, $exclude) {
	$query = "SELECT * FROM ".TABLE_PREFIX."categories WHERE folder_name = '".escape($folder_name)."' AND catId != '".escape($exclude)."'";
	$result = mysql_query($query);
	
	if (mysql_num_rows($result) < 1)
		return false;
		
	return true;
}

function cleanFileName($filename) {
	$path_parts = pathinfo($filename);
	$filename = rtrim(basename($filename, $path_parts['extension']), ".");
	$filename = preg_replace('/[^a-z0-9_\-[:blank:]{1}]/i', '', strtolower($filename));;
	$filename = str_replace(" ", "_", $filename);
	return $filename;
}

function get_patch_filename($name, $member_num, $imgtype) {
	global $admin;
	
	switch ($imgtype)
	{
		case "1":
			$ext = ".gif";
			break;
		case "3":
			$ext = ".png";
			break;
	}
	
	if ($admin->patch_naming == "#name")
		$patch_stored = cleanFolderName($member_num . strtolower($name)) . $ext;
	
	elseif ($admin->patch_naming == "#")
		$patch_stored = cleanFolderName($member_num) . $ext;
	
	else
		$patch_stored = cleanFolderName(strtolower($name) . $member_num) . $ext;
	
	return $patch_stored;
}

function getPatchFilename($name, $member_num, $ext) {
	global $admin;
	
	if ($admin->patch_naming == "#name")
		$patch_stored = cleanFolderName($member_num . strtolower($name)) . $ext;
	
	elseif ($admin->patch_naming == "#")
		$patch_stored = cleanFolderName($member_num) . $ext;
	
	else
		$patch_stored = cleanFolderName(strtolower($name) . $member_num) . $ext;
	
	return $patch_stored;
}

function getPatches(&$count, $isApproved, $search_by = "", $search_for = "", $catId = "", $sort_by = "", $sort_how = "", $perpage_by_arg = "") {
	global $perpage;
	
	$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE isApproved = '".escape($isApproved)."'";
	
	if ($search_by == "member_name" || $search_by == "member_num" || $search_by == "member_email" || $search_by == "patch_url" || $search_by == "patch_desc" || $search_by == "patch_stored")
		$query .= " AND $search_by ".($search_by == "member_num" ? "= '".escape($search_for)."'" : "LIKE '%".escape($search_for)."%'");
		
	if ($catId != "")
		$query .= " AND catId = '".escape($catId)."'";
		
	$result = mysql_query($query);
	
	$count = mysql_num_rows($result);
	
	$query .= " ORDER BY ";
	
	switch($sort_by)
	{
		case "displayId":
		case "member_name":
		case "member_num":
		case "date_received":
			$query .= $sort_by;
			break;
		default:
			$query .= "patchId";
			break;
	}
	
	if (strtoupper($sort_how) == "ASC")
		$query .= " ASC";
	else
		$query .= " DESC";
		
	// Figure out how to paginate
	if (strstr(dirname($_SERVER['PHP_SELF']), "/admin")) {
		if (is_numeric($perpage) && $perpage > 0)
			$perpage_in = $perpage;
		else
			$perpage_in = $count;
		
		$start = validateP() * $perpage_in - $perpage_in;
		$query .= " LIMIT $start, ".escape($perpage_in);
	}
	else {
		if (is_numeric($perpage_by_arg) && $perpage_by_arg > 0)
			$perpage_in = $perpage_by_arg;
		else
			$perpage_in = $count;
		
		if ($_GET['catId'] == $catId || $_GET['catId'] == -1)
			$start = validateP() * $perpage_in - $perpage_in;
		else
			$start = 0;
		
		$query .= " LIMIT $start, ".escape($perpage_in);
	}
	
	//echo $query."<br />";
	
	$result = mysql_query($query);
	
	$patches = array();
	while ($row = mysql_fetch_array($result))
		array_push($patches, $row);
		
	return $patches;
}

function sortPatches($displayId, $catId) {
	$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE displayId = '".escape($displayId)."' AND catId = '".escape($catId)."'";
	$result = mysql_query($query);
	
	if (mysql_num_rows($result) > 0) {
		// Needs Re-Sorting
		$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE displayId >= '".escape($displayId)."' AND catId = '".escape($catId)."'";
		$result = mysql_query($query);
		
		while ($row = mysql_fetch_array($result)) {
			++$row['displayId'];
			mysql_query("UPDATE ".TABLE_PREFIX."patches SET displayId = '".$row['displayId']."' WHERE patchId = '".$row['patchId']."' LIMIT 1");
		}
	}
}

function sortPatchesAdd($displayId, $catId, $patchId) {
	$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE displayId = '".escape($displayId)."' AND catId = '".escape($catId)."' AND patchId != '".escape($patchId)."'";
	$result = mysql_query($query);
	
	if (mysql_num_rows($result) > 0) {
		// Needs Re-Sorting
		$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE displayId >= '".escape($displayId)."' AND catId = '".escape($catId)."' AND patchId != '".escape($patchId)."'";
		$result = mysql_query($query);
		
		while ($row = mysql_fetch_array($result)) {
			++$row['displayId'];
			mysql_query("UPDATE ".TABLE_PREFIX."patches SET displayId = '".$row['displayId']."' WHERE patchId = '".$row['patchId']."' LIMIT 1");
		}
	}
}

function getDisplayId($catId) {
	$query = "SELECT displayId FROM ".TABLE_PREFIX."patches WHERE catId = '".escape($catId)."' ORDER BY displayId DESC LIMIT 1";
	$result = mysql_query($query);
	
	if (mysql_num_rows($result) < 1)
		return "1";
	
	$row = mysql_fetch_array($result);
	
	return ++$row['displayId'];
}

function monthDropdown($selected, $id_concat = "") {
	echo "\t\t\t<select name=\"month\" id=\"month".$id_concat."\" class=\"auto\">\n";
	echo "\t\t\t\t<option value=\"01\"".($selected == "01" ? " selected" : "").">January</option>\n";
	echo "\t\t\t\t<option value=\"02\"".($selected == "02" ? " selected" : "").">February</option>\n";
	echo "\t\t\t\t<option value=\"03\"".($selected == "03" ? " selected" : "").">March</option>\n";
	echo "\t\t\t\t<option value=\"04\"".($selected == "04" ? " selected" : "").">April</option>\n";
	echo "\t\t\t\t<option value=\"05\"".($selected == "05" ? " selected" : "").">May</option>\n";
	echo "\t\t\t\t<option value=\"06\"".($selected == "06" ? " selected" : "").">June</option>\n";
	echo "\t\t\t\t<option value=\"07\"".($selected == "07" ? " selected" : "").">July</option>\n";
	echo "\t\t\t\t<option value=\"08\"".($selected == "08" ? " selected" : "").">August</option>\n";
	echo "\t\t\t\t<option value=\"09\"".($selected == "09" ? " selected" : "").">September</option>\n";
	echo "\t\t\t\t<option value=\"10\"".($selected == "10" ? " selected" : "").">October</option>\n";
	echo "\t\t\t\t<option value=\"11\"".($selected == "11" ? " selected" : "").">November</option>\n";
	echo "\t\t\t\t<option value=\"12\"".($selected == "12" ? " selected" : "").">December</option>\n";
	echo "\t\t\t</select>\n";
}

function dayDropdown($selected, $id_concat = "") {
	echo "\t\t\t<select name=\"day\" id=\"day".$id_concat."\" class=\"auto\">\n";
	
	for ($i = 1; $i <= 31; $i++) {
		$padded = "$i";
		
		if ($i / 10 < 1)
			$padded = str_pad($padded, 2, "0", STR_PAD_LEFT);
		
		echo "\t\t\t\t<option value=\"$padded\"".($selected == $padded ? " selected" : "").">$i</option>\n";
	}
	
	echo "\t\t\t</select>\n";
}

function hourDropdown($selected, $id_concat = "") {
	echo "\t\t\t<select name=\"hour\" id=\"hour".$id_concat."\" class=\"auto\">\n";
	
	for ($i = 1; $i <= 12; $i++) {
		// Get Selected
		if ($selected > 12) {
			$selected = $selected - 12;
			$selected = str_pad($selected, 2, "0", STR_PAD_LEFT);
		}
		elseif ($selected == "00")
			$selected = 12;
		
		$padded = "$i";
		
		if ($i / 10 < 1)
			$padded = str_pad($padded, 2, "0", STR_PAD_LEFT);
		
		echo "\t\t\t\t<option value=\"$padded\"".($selected == $padded ? " selected" : "").">$i</option>\n";
	}
	
	echo "\t\t\t</select>\n";
}

function minuteDropdown($selected, $id_concat = "") {
	echo "\t\t\t<select name=\"minute\" id=\"minute".$id_concat."\" class=\"auto\">\n";
	
	for ($i = 0; $i <= 59; $i++) {
		$padded = "$i";
		
		if ($i / 10 < 1)
			$padded = str_pad($padded, 2, "0", STR_PAD_LEFT);
		
		echo "\t\t\t\t<option value=\"$padded\"".($selected == $padded ? " selected" : "").">$padded</option>\n";
	}
	
	echo "\t\t\t</select>\n";
}

function getPosts(&$count, $year = "", $month = "", $search_by = "", $search_for = "") {
	global $perpage;
	$start = validateP() * $perpage - $perpage;
	
	$query = "SELECT * FROM ".TABLE_PREFIX."blog";
	
	switch($search_by)
	{
		case "title":
		case "content":
			$query .= " WHERE ".$search_by." LIKE '%".escape($search_for)."%'";
			$where = false;
			break;
		default:
			$where = true;
			break;
	}
	
	// Year
	if (!empty($year)) {
		$query .= ($where ? " WHERE" : " AND")." YEAR(post_date) = '".escape($year)."'";
		$where = false;
	}
		
	// Month
	if (!empty($month))
		$query .= ($where ? " WHERE" : " AND")." MONTH(post_date) = '".escape($month)."'";
	
	$result = mysql_query($query);
	$count = mysql_num_rows($result);
	
	$query .= " ORDER BY post_date DESC LIMIT $start, ".escape($perpage);
	
	$result = mysql_query($query);
	$posts = array();
	
	while ($row = mysql_fetch_array($result))
		array_push($posts, $row);
		
	return $posts;
}

function getMembers() {
	$query = "SELECT * FROM ".TABLE_PREFIX."patches WHERE isApproved = '1' AND catId = '2' ORDER BY member_name ASC";
	$result = mysql_query($query);
	
	$members = array();
	while ($row = mysql_fetch_array($result))
		array_push($members, $row);
	return $members;
}

/*******************************************************************************
*
*	GENERAL FUNCTIONS
*
*******************************************************************************/

function cleanUp($string) {
	// Clean Up Form Submissions
	$string = strip_tags($string);
	$string = str_replace("\r\n", " ", $string);
	$string = str_replace("\n", " ", $string);
	$string = str_replace("\r", " ", $string);
	$string = stripslashes($string);
	$string = htmlentities($string, ENT_QUOTES);
	$string = trim($string);
	return $string;
}

function cleanUpCode($string) {
	// Allow some html
	$string = strip_tags($string, "<a><img><em><strong><ins><del><abbr><acronym>");
	
	$string = my_nl2br($string);
	$string = stripslashes($string);
	$string = str_replace("&", "&amp;", $string);
	$string = str_replace("'", "&#039;", $string);
	$string = addslashes($string);
	$string = trim($string);
	return $string;
}

function my_nl2br($string) {
	$string = str_replace("\r\n", "<br />", $string); //DOS
	$string = str_replace("\n", "<br />", $string); //UNIX
	$string = str_replace("\r", "<br />", $string); //MAC
	return $string;
}

function getReferrer() {
	if (!empty($_GET['referrer']))
		return $_GET['referrer'];
	
	return $_SERVER['PHP_SELF'] . (!empty($_SERVER['QUERY_STRING']) ? "?" . $_SERVER['QUERY_STRING'] : "");
}

function validEmail($string) {
	return ereg("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,6})$",strtolower($string));
	// From Jem - http://www.tutorialtastic.co.uk/tutorial/php_mail_form_secure_and_protected
}

function isProperUsername($string) {
	return !preg_match("/[^A-Za-z0-9-_]/", $string);
}

function randomString($length = 7) {
	// Taken from PHP.Net
	$key_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$rand_max  = strlen($key_chars) - 1;

	for ($i = 0; $i < $length; $i++)
	{
	   $rand_pos  = rand(0, $rand_max);
	   $rand_key[] = $key_chars{$rand_pos};
	}
	
	return implode('', $rand_key);
}

function escape($string) {
	// Save typing time
	return mysql_real_escape_string($string);
}

// Pagination
function validateP() {
	$p = $_GET['p'];
	
	if (empty($p) || $p < 1 || !is_numeric($p))
		$p = 1;
		
	return $p;
}

function pagination($num) {
	global $perpage, $search_by, $search_for, $catId;
	
	$p = validateP();
	
	$prev = $p - 1;
	$next = $p + 1;
	
	$howmany = ceil($num/$perpage);
	
	if ($num <= $perpage) {
		return;
	}
	
	echo "<p class=\"pages\">\n";
	
	$url = "\t<a href=\"?";
	
	//$search_by = "", $search_for = "", $catId = "", $sort_by = "", $sort_how = ""
	
	if (!empty($search_by))
		$url .= "search_by=".$search_by."&amp;";
		
	if (!empty($search_for))
		$url .= "search_for=".$search_for."&amp;";
		
	if (!empty($catId))
		$url .= "catId=".$catId."&amp;";
		
	if (!empty($_GET['sort_by']))
		$url .= "sort_by=".$_GET['sort_by']."&amp;";
		
	if (!empty($_GET['sort_how']))
		$url .= "sort_how=".$_GET['sort_how']."&amp;";
		
	if (!empty($_GET['month']))
		$url .= "month=".$_GET['month']."&amp;";
		
	if (!empty($_GET['year']))
		$url .= "year=".$_GET['year']."&amp;";
	
	$url .= "p=";
	
	// Previous Link
	if ($prev > 0) {
		echo $url.$prev."\" title=\"Previous\">&laquo;</a>\n";
	}
	
	if ($p == 1)
		echo "\t<span>1</span>\n";
	else
		echo $url."1\" title=\"Page 1\">1</a>\n";
		
	if ($p >= 4)
		echo "\t ...\n";
	
	for ($i = $p - 2; $i <= $p + 2; $i++) {
		if ($i >= 2 && $i <= $howmany - 1) {
			if($i == $p)
				echo "\t<span>$i</span>\n";
			else
				echo $url.$i."\" title=\"Page $i\">$i</a>\n";
		}
	}
	
	if ($p + 2 < $howmany)
		echo "\t ...\n";
	
	if ($p == $howmany)
		echo "\t<span>$howmany</span>\n";
	else
		echo $url.$howmany."\" title=\"Page $howmany\">$howmany</a>\n";
	
	// Next Link
	if ($next*$perpage < $num+$perpage) {
		echo $url.$next."\" title=\"Next\">&raquo;</a>\n";
	}
	
	echo "</p>\n\n";
}

function quilt_pagination($num, $perpage, $catId) {
	$p = validateP();
	
	$prev = $p - 1;
	$next = $p + 1;
	
	$howmany = ceil($num/$perpage);
	
	if ($num <= $perpage) {
		return;
	}
	
	echo "<p class=\"pages\">\n";
	
	$url = "\t<a href=\"?catId=$catId&amp;p=";
	
	// Previous Link
	if ($prev > 0) {
		echo $url.$prev."\" title=\"Previous\">&laquo;</a>\n";
	}
	
	if ($p == 1)
		echo "\t<span>1</span>\n";
	else
		echo $url."1\" title=\"Page 1\">1</a>\n";
		
	if ($p >= 4)
		echo "\t ...\n";
	
	for ($i = $p - 2; $i <= $p + 2; $i++) {
		if ($i >= 2 && $i <= $howmany - 1) {
			if($i == $p)
				echo "\t<span>$i</span>\n";
			else
				echo $url.$i."\" title=\"Page $i\">$i</a>\n";
		}
	}
	
	if ($p + 2 < $howmany)
		echo "\t ...\n";
	
	if ($p == $howmany)
		echo "\t<span>$howmany</span>\n";
	else
		echo $url.$howmany."\" title=\"Page $howmany\">$howmany</a>\n";
	
	// Next Link
	if ($next*$perpage < $num+$perpage) {
		echo $url.$next."\" title=\"Next\">&raquo;</a>\n";
	}
	
	echo "</p>\n\n";
}

/*******************************************************************************
*
*	TEMPLATE FUNCTIONS
*
*******************************************************************************/

function print_cat_name($catId) {
	$cat = new Category();
	
	if ($cat->getCatById(cleanUp($catId)))
		echo $cat->cat_name;
		
	else
		echo "Invalid Category ID";
}

function print_cat_description($catId) {
	$cat = new Category();
	
	if ($cat->getCatById(cleanUp($catId)))
		echo stripslashes($cat->cat_desc);
		
	else
		echo "Invalid Category ID";
}

function print_cat_patch_count($catId) {
	$cat = new Category();
	
	if ($cat->getCatById(cleanUp($catId)))
		echo $cat->patchCount();
		
	else
		echo "Invalid Category ID";
}

function print_cat_patches_list($catId, $patches_on_row = "", $perpage = "", $use_fillers = "", $filler_url = "", $filler_alt = "", $use_alt = "", $alt_url = "", $alt_alt = "", $sort_by = "", $sort_how = "") {
	$admin = new Admin();
	$admin->getAdmin();
	
	$cat = new Category();
	$patch = new Patch();
	
	if (!$cat->getCatById(cleanUp($catId))) {
		echo "<p>Invalid Category ID</p>\n";
		return;
	}
	
	// Get Category Defaults
	if (!is_numeric($patches_on_row) || $patches_on_row < 1)
		$patches_on_row = $cat->perline;
		
	if (!is_numeric($perpage) || ($perpage != -1 && $perpage < 1))
		$perpage = $cat->perpage;
		
	if ($use_fillers === "")
		$use_fillers = $cat->use_fillers;
		
	if ($filler_url == "")
		$filler_url = $cat->filler_url;
		
	if ($filler_alt == "")
		$filler_alt = $cat->filler_alt;
		
	if ($use_alt === "")
		$use_alt = $cat->use_alt;
		
	if ($alt_url == "")
		$alt_url = $cat->alt_url;
		
	if ($alt_alt == "")
		$alt_alt = $cat->alt_alt;
		
	if ($sort_by != "displayId" && $sort_by != "member_name" && $sort_by != "member_num" && $sort_by != "date_received")
		$sort_by = $cat->sort_by;
		
	if ($sort_how != "asc" && $sort_how != "desc")
		$sort_how = $cat->sort_how;
		
	// Take Alternating Patches into account
	if ($use_alt == true)
		$perpage = ceil($perpage / 2);
		
	// Get Patches
	$patches = getPatches($patch_count, 1, "", "", $cat->catId, $sort_by, $sort_how, $perpage);
	
	if ($patch_count < 1)
		return;		// No patches found - print nothing
		
	// Validate Perpage
	if ($perpage < 1)
		$perpage = $patch_count;
		
	$i=0;	// Row
	$j=1;	// Patch Count
	$k=1;	// Alternating Patches
	
	echo "<ul class=\"quilt\" id=\"cat-$cat->catId\">\n";
	
	foreach ($patches as $row) {
		$patch->getPatchById($row['patchId']);
		
		// Is Alternating Patch?
		if ($k % 2 == 0 && $use_alt == true) {
			echo "\t".'<li><img src="'.$alt_url.'" alt="'.$alt_alt.'" title="'.$alt_alt.'" /></li>'."\n";
			
			// End Row?
			if ($i == $patches_on_row-1)
				$i=0;
			else
				$i++;
				
			$j++;
			$k++;
		}
		
		// Patch Info
		$path = "/".$admin->patch_dir."/".$cat->folder_name."/".$patch->patch_stored;
		$info = @getimagesize($_SERVER['DOCUMENT_ROOT'].$path);
		
		// <a href="patch_url" title="patch_desc" target="_blank"><img src="" alt="patch_desc" width="" height="" /></a>
		echo "\t<li>".($patch->patch_url != "" && $patch->patch_url != "http://" ? '<a href="'.$patch->patch_url.'" target="_blank">' : '').'<img src="'.($info ? $path : $patch->patch_img_url).'" title="'.$patch->patch_desc.'" alt="'.$patch->patch_desc.'"'.($info ? ' width="'.$info[0].'" height="'.$info[1].'"' : '').' />'.($patch->patch_url != "" && $patch->patch_url != "http://" ? '</a>' : '')."</li>\n";
		
		// End Row?
		if ($i == $patches_on_row-1)
			$i=0;
		else
			$i++;
		
		$j++;
		$k++;
	}
	
	// Filler Patches
	if ($i != 0 && $use_fillers == true) {
		$leftover = $patches_on_row - $i;
			
		for ($i=0; $i<$leftover; $i++) {
			if ($i % 2 == 0 && $use_alt == true) {
				echo "\t<li>".'<img src="'.$alt_url.'" alt="'.$alt_alt.'" title="'.$alt_alt.'" />'."</li>\n";
				continue;
			}
			echo "\t<li>".'<img src="'.$filler_url.'" alt="'.$filler_alt.'" title="'.$filler_alt.'" />'."</li>\n";
		}
	}
	
	echo "</ul>\n";
	
	// Pagination
	quilt_pagination($patch_count, $perpage, $cat->catId);
}

function print_cat_patches($catId, $patches_on_row = "", $perpage = "", $use_fillers = "", $filler_url = "", $filler_alt = "", $use_alt = "", $alt_url = "", $alt_alt = "", $sort_by = "", $sort_how = "") {
	$admin = new Admin();
	$admin->getAdmin();
	
	$cat = new Category();
	$patch = new Patch();
	
	if (!$cat->getCatById(cleanUp($catId))) {
		echo "<p>Invalid Category ID</p>\n";
		return;
	}
	
	// Get Category Defaults
	if (!is_numeric($patches_on_row) || $patches_on_row < 1)
		$patches_on_row = $cat->perline;
		
	if (!is_numeric($perpage) || ($perpage != -1 && $perpage < 1))
		$perpage = $cat->perpage;
		
	if ($use_fillers === "")
		$use_fillers = $cat->use_fillers;
		
	if ($filler_url == "")
		$filler_url = $cat->filler_url;
		
	if ($filler_alt == "")
		$filler_alt = $cat->filler_alt;
		
	if ($use_alt === "")
		$use_alt = $cat->use_alt;
		
	if ($alt_url == "")
		$alt_url = $cat->alt_url;
		
	if ($alt_alt == "")
		$alt_alt = $cat->alt_alt;
		
	if ($sort_by != "displayId" && $sort_by != "member_name" && $sort_by != "member_num" && $sort_by != "date_received")
		$sort_by = $cat->sort_by;
		
	if ($sort_how != "asc" && $sort_how != "desc")
		$sort_how = $cat->sort_how;
		
	// Take Alternating Patches into account
	if ($use_alt == true)
		$perpage = ceil($perpage / 2);
		
	// Get Patches
	$patches = getPatches($patch_count, 1, "", "", $cat->catId, $sort_by, $sort_how, $perpage);
	
	if ($patch_count < 1)
		return;		// No patches found - print nothing
		
	// Validate Perpage
	if ($perpage < 1)
		$perpage = $patch_count;
		
	$i=0;	// Row
	$j=1;	// Patch Count
	$k=1;	// Alternating Patches
	
	echo "<div class=\"quilt\" id=\"cat-$cat->catId\">\n";
	
	foreach ($patches as $row) {
		$patch->getPatchById($row['patchId']);
		
		// Is Alternating Patch?
		if ($k % 2 == 0 && $use_alt == true) {
			echo "\t".'<img src="'.$alt_url.'" alt="'.$alt_alt.'" title="'.$alt_alt.'" />'."\n";
			
			// End Row?
			if ($i == $patches_on_row-1) {
				echo "\t<br />\n";
				$i=0;
			}
			else
				$i++;
				
			$j++;
			$k++;
		}
		
		// Patch Info
		$path = "/".$admin->patch_dir."/".$cat->folder_name."/".$patch->patch_stored;
		$info = @getimagesize($_SERVER['DOCUMENT_ROOT'].$path);
		
		// <a href="patch_url" title="patch_desc" target="_blank"><img src="" alt="patch_desc" width="" height="" /></a>
		echo "\t".($patch->patch_url != "" && $patch->patch_url != "http://" ? '<a href="'.$patch->patch_url.'" target="_blank">' : '').'<img src="'.($info ? $path : $patch->patch_img_url).'" title="'.$patch->patch_desc.'" alt="'.$patch->patch_desc.'"'.($info ? ' width="'.$info[0].'" height="'.$info[1].'"' : '').' />'.($patch->patch_url != "" && $patch->patch_url != "http://" ? '</a>' : '')."\n";
		
		// End Row?
		if ($i == $patches_on_row-1) {
			echo "\t<br />\n";
			$i=0;
		}
		else
			$i++;
		
		$j++;
		$k++;
	}
	
	// Filler Patches
	if ($i != 0 && $use_fillers == true) {
		$leftover = $patches_on_row - $i;
			
		for ($i=0; $i<$leftover; $i++) {
			if ($i % 2 == 0 && $use_alt == true) {
				echo "\t".'<img src="'.$alt_url.'" alt="'.$alt_alt.'" title="'.$alt_alt.'" />'."\n";
				continue;
			}
			echo "\t".'<img src="'.$filler_url.'" alt="'.$filler_alt.'" title="'.$filler_alt.'" />'."\n";
		}
	}

	
	echo "</div>\n";	// End .quilt
	
	// Pagination
	quilt_pagination($patch_count, $perpage, $cat->catId);
}

function print_all_patches($include = "", $patches_on_row = 5, $perpage = -1, $use_fillers = false, $filler_url = "", $filler_alt = "", $use_alt = false, $alt_url = "", $alt_alt = "") {
	$admin = new Admin();
	$admin->getAdmin();
	
	$cat = new Category();
	$patch = new Patch();
	
	if ($include == "")
		return;
	
	// Validate patches_on_row; is_numeric() > 0
	if (!is_numeric($patches_on_row) || $patches_on_row < 1)
		$patches_on_row = 5;
		
	// Construct Massive Patch Array from Include Array
	$patches = array();
	
	$include = explode(",", $include);
	$i=1;
	
	foreach ($include as $val) {
		if ($cat->getCatById(cleanUp($val))) {
			$query = "SELECT patchId FROM ".TABLE_PREFIX."patches WHERE isApproved = '1' AND catId = '$cat->catId' ORDER BY ".escape($cat->sort_by)." ".escape($cat->sort_how);
			$result = mysql_query($query);
			
			while ($row = mysql_fetch_array($result)) {
				if ($i % 2 == 0 && $use_alt == true) {
					array_push($patches, "a");
					$i++;
				}
				array_push($patches, $row['patchId']);
				$i++;
			}
		}
	}
	
	$patch_count = count($patches);
	
	if ($patch_count < 1)
		return;
		
	// Quilt Container
	echo "<div class=\"quilt\" id=\"all-patches\">\n";
		
	// Validate patches per page
	if (!is_numeric($perpage) || $perpage < 1)
		$perpage = $patch_count;
		
	$start = validateP() * $perpage - $perpage;
	
	// Print the Quilt - $i = iterator, $j = index, $k = row
	for ($i=0, $j=$start, $k=0; $i<$perpage; $i++,$j++) {
		if ($j >= $patch_count)
			break;
			
		elseif ($patches[$j] == "a")
			// Print Alternating Patch
			echo "\t".'<img src="'.$alt_url.'" alt="'.$alt_alt.'" title="'.$alt_alt.'" />'."\n";
		else {
			// Print Real Patch
			$patch->getPatchById($patches[$j]);
			$cat->getCatById($patch->catId);
			
			// Patch Info
			$path = "/".$admin->patch_dir."/".$cat->folder_name."/".$patch->patch_stored;
			$info = @getimagesize($_SERVER['DOCUMENT_ROOT'].$path);
			
			// <a href="patch_url" title="patch_desc" target="_blank"><img src="" alt="patch_desc" width="" height="" /></a>
			echo "\t".($patch->patch_url != "" && $patch->patch_url != "http://" ? '<a href="'.$patch->patch_url.'" target="_blank">' : '').'<img src="'.($info ? $path : $patch->patch_img_url).'" title="'.$patch->patch_desc.'" alt="'.$patch->patch_desc.'"'.($info ? ' width="'.$info[0].'" height="'.$info[1].'"' : '').' />'.($patch->patch_url != "" && $patch->patch_url != "http://" ? '</a>' : '')."\n";
		}
		
		// End Row?
		if ($k == $patches_on_row-1) {
			echo "\t<br />\n";
			$k=0;
		}
		else
			$k++;
	}
	
	// Filler Patches
	if ($k!=0 && $use_fillers == true) {
		$leftover = $patches_on_row - $k;
			
		// Add Trailing Alt Patch
		if (!is_numeric($patches[$j])) {
			echo "\t".'<img src="'.$alt_url.'" alt="'.$alt_alt.'" title="'.$alt_alt.'" />'."\n";
			$leftover--;
		}
			
		for ($i=0; $i<$leftover; $i++) {
			if ($i % 2 != 0)
				echo "\t".'<img src="'.$alt_url.'" alt="'.$alt_alt.'" title="'.$alt_alt.'" />'."\n";
			else
				echo "\t".'<img src="'.$filler_url.'" alt="'.$filler_alt.'" title="'.$filler_alt.'" />'."\n";
			
		}
	}
	
	echo "</div>\n";
	
	// Pagination
	quilt_pagination($patch_count, $perpage, -1);
}

function print_pending_count() {
	getPatches($patchCount, 0, "", "", 2, "date_received", "desc");
	echo $patchCount;
}

function print_month_trade_count() {
	$query = "SELECT patchId FROM ".TABLE_PREFIX."patches WHERE isApproved = '1' AND catId = '2' AND MONTH(date_received) = '".date("m")."' AND YEAR(date_received) = '".date("Y")."'";
	$result = mysql_query($query);
	echo mysql_num_rows($result);
}

function print_last_added_member($display_member_num = false) {
	$admin = new Admin();
	$admin->getAdmin();
	
	$patch = new Patch();
	$patches = getPatches($patch_count, 1, "", "", 2, "date_received", "desc");
	
	if ($patch_count < 1)
		return;
	
	$patch->getPatchById($patches[0]['patchId']);
	
	echo "<a href=\"$patch->patch_url\" title=\"$patch->member_name #$patch->member_num\" target=\"_blank\">$patch->member_name".(!empty($display_member_num) ? " #$patch->member_num" : "")."</a>";
}

function print_last_added_date() {
	$admin = new Admin();
	$admin->getAdmin();
	
	$patch = new Patch();
	$patches = getPatches($patch_count, 1, "", "", 2, "date_received", "desc");
	
	if ($patch_count < 1)
		return;
	
	$patch->getPatchById($patches[0]['patchId']);
	
	echo date($admin->date_format, strtotime($patch->date_received) + ($admin->time_offset * 60 * 60));
}

function print_last_added_time() {
	$admin = new Admin();
	$admin->getAdmin();
	
	$patch = new Patch();
	$patches = getPatches($patch_count, 1, "", "", 2, "date_received", "desc");
	
	if ($patch_count < 1)
		return;
	
	$patch->getPatchById($patches[0]['patchId']);
	
	echo date($admin->time_format, strtotime($patch->date_received) + ($admin->time_offset * 60 * 60));
}

function print_post_id() {
	global $post;
	
	echo $post->id;
}

function print_post_title() {
	global $post;
	
	echo $post->title;
}

function print_post_content() {
	global $post;
	
	echo stripslashes($post->content);
}

function print_post_date($format = "") {
	global $post;
	
	$admin = new Admin();
	$admin->getAdmin();
	
	if (empty($format))
		$format = $admin->date_format;
		
	echo date($format, strtotime($post->post_date) + ($admin->time_offset * 60 * 60));
}

function print_post_time($format = "") {
	global $post;
	
	$admin = new Admin();
	$admin->getAdmin();
	
	if (empty($format))
		$format = $admin->time_format;
		
	echo date($format, strtotime($post->post_date) + ($admin->time_offset * 60 * 60));
}

function print_blog_archives() {
	// Check to see if there are blog entries
	$query = "SELECT id FROM ".TABLE_PREFIX."blog ORDER BY id DESC";
	$result = mysql_query($query);
	
	if (mysql_num_rows($result) < 1)
		return;
	
	// Get Current Year Archives
	$query = "SELECT DISTINCT MONTH(post_date) AS month, YEAR(post_date) AS year FROM ".TABLE_PREFIX."blog WHERE YEAR(post_date) = '".date("Y")."' ORDER BY post_date DESC";
	$result = mysql_query($query);
	
	echo "<ul class=\"blog-archives\">\n";
	
	// Print Archives
	while($row = mysql_fetch_array($result)) {
		$month = date("F", mktime(0, 0, 0, $row['month'], 1, $row['year']));
		echo "\t<li><a href=\"blog.php?month=".$row['month']."&amp;year=".$row['year']."\" title=\"View all posts from $month ".$row['year']."\">".$month." ".$row['year']."</a></li>\n";
	}
	
	// Get Other Year Archives
	$query = "SELECT DISTINCT YEAR(post_date) AS year FROM ".TABLE_PREFIX."blog WHERE YEAR(post_date) != '".date("Y")."' ORDER BY post_date DESC";
	$result = mysql_query($query);
	
	while($row = mysql_fetch_array($result)) {
		echo "\t<li><a href=\"blog.php?year=".$row['year']."\" title=\"View all posts from ".$row['year']."\">Year of ".$row['year']."</a></li>\n";
	}
	
	echo "</ul>\n";
}

function print_full_log($display_time = false) {
	$admin = new Admin();
	$admin->getAdmin();
	
	$patch = new Patch();
	
	// Check and see if there are existing trades
	$cat = new Category();
	$cat->getCatById(2);
	
	if ($cat->patchCount() < 1)
		return;
	
	// Validate Log Year
	if (!is_numeric($_GET['log_year']) || $_GET['log_year'] < 2000 || $_GET['log_year'] > date("Y"))
		$log_year = date("Y");
	else
		$log_year = $_GET['log_year'];
		
	// Get Trades
	$query = "SELECT patchId FROM ".TABLE_PREFIX."patches WHERE isApproved = '1' AND catId = '2' AND YEAR(date_received) = '".escape($log_year)."' ORDER BY date_received DESC";
	$result = mysql_query($query);
	
	echo "<h1 class=\"trade-log\">Trade Log</h1>\n";
	
	if (mysql_num_rows($result) < 1)
		echo "<p class=\"trade-log\">No trades for the year $log_year.</p>\n";
	
	else {
		echo "<ul class=\"trade-log\">\n";
		
		while ($row = mysql_fetch_array($result)) {
			$patch->getPatchById($row['patchId']);
			
			$date = date($admin->date_format, strtotime($patch->date_received) + ($admin->time_offset * 60 * 60));
			$time = date($admin->time_format, strtotime($patch->date_received) + ($admin->time_offset * 60 * 60));
			
			echo "\t<li><a href=\"$patch->patch_url\" title=\"$patch->member_name #$patch->member_num\" target=\"_blank\">$patch->member_name #$patch->member_num</a> on $date".(!empty($display_time) ? " at $time" : "")."</li>\n";
		}
		
		echo "</ul>\n";
	}
	
	// Get Archives
	$query = "SELECT DISTINCT YEAR(date_received) AS year FROM ".TABLE_PREFIX."patches WHERE isApproved = '1' AND catId = '2' AND YEAR(date_received) != '".escape($log_year)."' ORDER BY year DESC";
	$result = mysql_query($query);
	
	if (mysql_num_rows($result) > 0) {
		echo "<h1 class=\"trade-log-archives\">Archives</h1>\n";
		
		echo "<ul class=\"trade-log-archives\">\n";
		
		while ($row = mysql_fetch_array($result))
			echo "\t<li><a href=\"?log_year=".$row['year']."\" title=\"".$row['year']." Trade Log\">".$row['year']."</a></li>\n";
		
		echo "</ul>\n";
	}
}

/*******************************************************************************
*
*	INCLUDES
*
*******************************************************************************/

if (isInstalled()) {
	include_once("classes/Admin.php");
	include_once("classes/Blog.php");
	include_once("classes/Category.php");
	include_once("classes/Patch.php");
}

isInstalledBouncer();
isDbCurrentBouncer();
