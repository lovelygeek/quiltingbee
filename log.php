<!--

log.php

PHP Function Usage:
===================

print_full_log() - Prints your full trade log (including archives of previous years).

-->

<?php
include("admin/config.php");
include("header.php");
?>

<?php print_full_log(); ?>

<p class="center"><a href="index.php" title="Back Home">&laquo; Back Home</a></p>

<?php
include("footer.php");
?>
