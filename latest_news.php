<?php 
require('/home/britbrokers/public_html/blog/wp-blog-header.php');

$args = array(
	'posts_per_page'   => 3,
	'offset'           => 0,
	'category'         => '',
	'category_name'    => '',
	'orderby'          => 'post_date',
	'order'            => 'DESC',
	'include'          => '',
	'exclude'          => '',
	'meta_key'         => '',
	'meta_value'       => '',
	'post_type'        => 'post',
	'post_mime_type'   => '',
	'post_parent'      => '',
	'post_status'      => 'publish',
	'suppress_filters' => true 
);
$postslist = get_posts( $args );
//var_dump($postslist);
echo "<h3>Latest News</h3>";
echo "<ul class='bloglist-small'>";
include_once("functions.php");

foreach ($postslist as $post){
	//@setup_postdata($post);
	//var_dump($post);
	echo "	<li>";
	/*
	echo "		<div class='date-box'>";
	echo "			<span class='day'>" . date('d',strtotime($post->post_date)) . "</span>";
	echo "			<span class='month'>" . date('M',strtotime($post->post_date)) . "</span>";
	echo "		</div>";
	*/
	//echo "	<div class='txt'>";
	echo "		<h5><a href='" . $post->guid . "' title='" . $post->post_title . "'>" .  $post->post_title . "</a></h5>";
	echo "		<div>";
	echo "			<div>" . get_the_post_thumbnail( $post->ID, 'thumbnail', array( 'class' => 'thumbnail' ) ) . "</div>";
	$summary = truncate(strip_tags($post->post_content), 150);
	if($summary){
		$summary .= "<a href='" . $post->guid . "' title='" . $post->post_title . "'>&nbsp;more</a>";
	} else {
		$summary="";
	}
	echo "			<div>" . $summary . "</div>";
	echo "		</div>";
	//echo "	</div>";
	echo "</li>";
	
	//echo "<li><strong>" .  the_date() . "</strong><br />";
	//echo "<a href='" . $post->guid . "' title='" . $post->post_title . "'>" .  $post->post_title . "</a>";
	//echo "<br/>" . get_the_post_thumbnail( $post->ID, 'thumbnail' );
	//echo "</li>";
}
echo "</ul>";
?>