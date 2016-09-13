<!--

member.php - Displays a quilt made up of patches from the member category

PHP Function Usage:
===================

print_cat_patches($catId) - Print the patches in the specified category

-->

<?php
include("admin/config.php");
include("header.php");
?>

<?php print_cat_patches(2); ?>

<p class="center"><a href="index.php" title="Back Home">&laquo; Back Home</a></p>

<?php
include("footer.php");
?>
