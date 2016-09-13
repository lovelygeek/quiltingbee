<!--

index.php

PHP Function Usage:
===================

print_cat_patches($catId) - Print the patches in the specified category
print_pending_count() - Print the number of pending trades
print_cat_patch_count($catId) - Print the number of patches in the specified category
print_month_trade_count() - Print the number of trades from the current month
print_last_added_member() - Prints the link to the latest member
print_last_added_date() - Prints the date of the most recent trade
print_last_added_time() - Prints the time of the most recent trade

-->

<?php include("admin/config.php"); include("header.php"); ?>

	<h1>Welcome to my Quilt!</h1>
	I'm an official <a href="http://www.theqbee.net" title="The Quilting Bee" target="_blank">Quilting Bee</a> member! If you are also a member, and would like to trade patches, please fill out the <a href="tradeform.php">form</a>. If you're not already a member, <a href="http://theqbee.net/join.php">join the club</a> and don't forget to tell them <a href="http://www.theqbee.net/refer.php?beenum=176">Cristina Bee #176</a> sent ya!</p>

	<h2>News <span class="amp">&amp;</span> Updates</h2>
	<?php include("blog.php"); ?>

<?php include("footer.php"); ?>
