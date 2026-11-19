<?php
/*
 * "Latest News": the three newest blog posts.
 *
 * Reads the WordPress tables directly (same database) instead of loading WordPress itself.
 * The blog runs WordPress 4.9, which does not support PHP 8; loading it here would break the
 * home page once the server moves to PHP 8.5. If the blog tables are missing, the box is left out.
 */
include_once __DIR__ . "/functions.php";

$news_link = setConnection();
$wp_prefix = preg_replace('/[^A-Za-z0-9_]/', '', cfg("WP_TABLE_PREFIX", "bbb_"));

$SQL = "SELECT p.ID, p.post_title, p.post_content, p.guid,
		(SELECT am.meta_value FROM {$wp_prefix}postmeta tm
			JOIN {$wp_prefix}posts a ON a.ID = tm.meta_value
			JOIN {$wp_prefix}postmeta am ON am.post_id = a.ID AND am.meta_key = '_wp_attachment_metadata'
			WHERE tm.post_id = p.ID AND tm.meta_key = '_thumbnail_id' LIMIT 1) AS thumb_meta
	FROM {$wp_prefix}posts p
	WHERE p.post_type = 'post' AND p.post_status = 'publish'
	ORDER BY p.post_date DESC
	LIMIT 3";
$postslist = runQuery($news_link, $SQL, "RA");

/** URL of the post's 'thumbnail' size image, from WordPress attachment metadata. */
function news_thumbnail_url($meta){
	if(!$meta){
		return "";
	}
	$data = @unserialize($meta, array("allowed_classes" => false));
	if(!is_array($data) || empty($data['file'])){
		return "";
	}
	$dir = dirname($data['file']);
	$file = $data['sizes']['thumbnail']['file'] ?? basename($data['file']);
	return cfg("WP_UPLOADS_URL", "https://britbrokers.com/blog/wp-content/uploads") . "/" . ($dir !== "." ? $dir . "/" : "") . rawurlencode($file);
}

if($postslist){
	echo "<h3>Latest News</h3>";
	echo "<ul class='bloglist-small'>";

	foreach ($postslist as $post){
		$url = e($post['guid']);
		$title = e($post['post_title']);
		$thumb = news_thumbnail_url($post['thumb_meta']);

		echo "	<li>";
		echo "		<h5><a href='" . $url . "' title='" . $title . "'>" .  $title . "</a></h5>";
		echo "		<div>";
		if($thumb !== ""){
			echo "			<div><img src='" . e($thumb) . "' class='thumbnail' alt='' width='150' height='150' loading='lazy'></div>";
		}
		$summary = e(truncate(html_entity_decode(strip_tags($post['post_content']), ENT_QUOTES), 150));
		if($summary){
			$summary .= "<a href='" . $url . "' title='" . $title . "'>&nbsp;more</a>";
		}
		echo "			<div>" . $summary . "</div>";
		echo "		</div>";
		echo "</li>";
	}
	echo "</ul>";
}
