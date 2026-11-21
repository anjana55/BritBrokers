<?php
/*
 * Page layout shared by every public and admin page.
 *
 *   <?php
 *   require_once __DIR__ . "/bootstrap.php";
 *   page_head(array("title" => "...", "description" => "..."));
 *   ?>
 *   ...anything else for <head>: page-specific <script> / <link> tags...
 *   <?php page_body("home"); ?>
 *   ...page content...
 *   <?php page_end(); ?>
 */

/** Values a page passes to page_head(), kept for page_body()/page_end() and the templates. */
function page_meta($key = null, $value = null){
	static $meta = array();
	if($key === null){
		return $meta;
	}
	if(func_num_args() === 2){
		$meta[$key] = $value;
	}
	return $meta[$key] ?? null;
}

/**
 * Doctype, <html>, and the shared <head> (CSS, scripts, chat widget), with this page's title and description.
 *   title        <title> text (plain text; escaped here)
 *   description  meta description (plain text; escaped here)
 *   csrf         true on admin pages: adds the CSRF token to every AJAX request
 */
function page_head(array $p = array()){
	foreach($p as $k => $v){
		page_meta($k, $v);
	}
	echo "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n";
	include SITE_ROOT . "/templates/header.php";
	echo "<title>" . e($p['title'] ?? "British Brokers") . "</title>\n";
	if(!empty($p['description'])){
		echo "<meta name=\"description\" content=\"" . e($p['description']) . "\">\n";
	}
	if(!empty($p['csrf'])){
		echo csrf_meta();
	}
}

/** Close <head>, open <body> and print the site navigation. */
function page_body($body_id = "home"){
	echo "</head>\n<body id=\"" . e($body_id) . "\">\n";
	include SITE_ROOT . "/templates/navigation.php";
}

/**
 * Footer and closing tags.
 *   $sub_footer  show the contact/subscribe band above the footer (most public pages)
 *   $vars        variables the footer templates need, e.g. array("pid" => $pid)
 */
function page_end($sub_footer = true, array $vars = array()){
	extract($vars, EXTR_SKIP);
	if($sub_footer){
		include SITE_ROOT . "/templates/sub_footer.php";
	}
	include SITE_ROOT . "/templates/footer.php";
	echo "</body>\n</html>";
}

/* ---------------- Property links ---------------- */

/** Short text for a property link, e.g. "3-bedroom-house-colombo-7". */
function property_slug($property){
	$parts = array();
	if(!empty($property['bedrooms']) && (int)$property['bedrooms'] > 0 && strpos((string)($property['property_type'] ?? ""), "LAN") !== 0){
		$parts[] = (int)$property['bedrooms'] . "-bedroom";
	}
	$parts[] = $property['property_type_name'] ?? "";
	$parts[] = $property['city_name'] ?? "";
	$slug = strtolower(implode("-", array_filter($parts)));
	$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
	return trim(substr($slug, 0, 80), "-");
}

/**
 * Link to a property page. With PRETTY_URLS on (the default) this is
 *   property-123-3-bedroom-house-colombo-7
 * which .htaccess maps to single-property.php?pid=123. It sits in the same folder as the other pages,
 * so relative links to css/, js/ and img/ keep working. $extra adds query values, e.g. array("bt" => "R").
 * $absolute = true prefixes SITE_URL (for emails, sitemaps and share links).
 */
function property_url($property, array $extra = array(), $absolute = false){
	$pid = (int)(is_array($property) ? ($property['property_id'] ?? 0) : $property);
	if(pretty_urls()){
		$slug = is_array($property) ? property_slug($property) : "";
		$url = "property-" . $pid . ($slug !== "" ? "-" . $slug : "");
		$query = $extra ? "?" . http_build_query($extra) : "";
	} else {
		$url = "single-property.php";
		$query = "?" . http_build_query(array("pid" => $pid) + $extra);
	}
	return ($absolute ? site_url() . "/" : "") . $url . $query;
}

/** Clean property links need mod_rewrite (Apache/LiteSpeed). Set PRETTY_URLS="0" if the host has none. */
function pretty_urls(){
	return cfg("PRETTY_URLS", "1") !== "0";
}
