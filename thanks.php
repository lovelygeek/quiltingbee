<!--
thanks.php
This is the page that is displayed to the trader once he/she successfully fills out the form.
Feel free to edit it however you want.

<?= $name ?> - Prints the name of the trader who has just filled out the form.
-->

<?
include("header.php");
?>

<p class="center"><img src="images/thanks-for-trading.gif" width="66" height="18" alt="Thanks for Trading!" /></p>
<p class="center">Thanks for trading patches with me, <?= $name ?>!  I will add you to my quilt ASAP :)</p>
<p class="center"><a href="index.php" title="Back to my Quilt">Back to my Quilt</a></p>

<?
include("footer.php");
?>
