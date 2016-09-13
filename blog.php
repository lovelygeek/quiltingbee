<?php
/*

blog.php
This page displays your post-by-post blog archives.
You can use blog.php as an include or a separate page.
Please specify some options below.

*/

$perpage = 5;				// Number of post displayed per page

$include_header_footer = false;		// If you want to use blog.php as an include, type 'false'
					// If you want to use blog.php as a separate page, leave 'true'

/*

You can customize the look & feel of your blog entries below,
but pay attention to the comments that I've left for you!

*/

// NO EDITING!
include_once("admin/config.php");
$post = new Blog();

// Gets the blog posts from the database
$posts = getPosts($post_count, cleanUp($_GET['year']), cleanUp($_GET['month']));

// Include Header (if applicable)
if ($include_header_footer)
	include("header.php");

if ($post_count < 1) :
	echo "<p>No posts found.</p>\n";
	
else :

	$i = 0;
	foreach ($posts as $row) {
		$post->getBlogById($row['id']);
?>

<!-- You can edit below this line. -->
<!-- Here you can customize the look & feel of your blog entries. -->

<div id="post-<?php print_post_id(); ?>" class="post">
	<div class="post-title"><?php print_post_title(); ?></div>
	<small>Posted on <em><?php print_post_date(); ?></em> at <em><?php print_post_time(); ?></em></small>
	
	<p class="post-content"><?php print_post_content(); ?></p>
</div>

<!-- End editing. -->

<?
	}
	
	pagination($post_count);

endif;

// Include Footer (if applicable)
if ($include_header_footer)
	include("footer.php");
?>
