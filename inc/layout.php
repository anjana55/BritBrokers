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
 *   canonical    the page's one official address (absolute or relative to SITE_URL);
 *                defaults to this page without its query string
 *   image        picture for link previews (Facebook, WhatsApp, LinkedIn, X); defaults to the site banner
 *   type         Open Graph type, default "website"
 *   robots       e.g. "noindex,follow" for search results and admin pages
 *   json_ld      structured data (array) for search engines
 *   csrf         true on admin pages: adds the CSRF token to every AJAX request
 *   admin        true on the control panel: admin scripts and styles
 */
function page_head(array $p = array()){
	foreach($p as $k => $v){
		page_meta($k, $v);
	}
	echo "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n";
	include SITE_ROOT . "/templates/header.php";
	$title = $p['title'] ?? "British Brokers";
	echo "<title>" . e($title) . "</title>\n";
	if(!empty($p['description'])){
		echo "<meta name=\"description\" content=\"" . e($p['description']) . "\">\n";
	}
	if(!empty($p['robots'])){
		echo "<meta name=\"robots\" content=\"" . e($p['robots']) . "\">\n";
	}
	if(empty($p['robots']) || strpos($p['robots'], "noindex") === false){
		echo seo_tags($title, $p);
	}
	echo analytics_tag();
	if(!empty($p['csrf'])){
		echo csrf_meta();
	}
}

/** Absolute URL: leaves full URLs alone, otherwise prefixes SITE_URL. */
function absolute_url($url){
	return preg_match('#^https?://#', $url) ? $url : site_url() . "/" . ltrim($url, "/");
}

/** Canonical link, Open Graph / Twitter tags for link previews, and structured data. */
function seo_tags($title, array $p){
	if(!empty($p['canonical'])){
		$canonical = absolute_url($p['canonical']);
	} else {
		$page = basename((string)($_SERVER['SCRIPT_NAME'] ?? "index.php"));
		$canonical = site_url() . "/" . ($page === "index.php" ? "" : $page);
	}
	$image = absolute_url($p['image'] ?? "img/bb-main-1.jpg");
	$out  = "<link rel=\"canonical\" href=\"" . e($canonical) . "\">\n";
	$out .= "<meta property=\"og:site_name\" content=\"British Brokers\">\n";
	$out .= "<meta property=\"og:type\" content=\"" . e($p['type'] ?? "website") . "\">\n";
	$out .= "<meta property=\"og:title\" content=\"" . e($title) . "\">\n";
	if(!empty($p['description'])){
		$out .= "<meta property=\"og:description\" content=\"" . e($p['description']) . "\">\n";
	}
	$out .= "<meta property=\"og:url\" content=\"" . e($canonical) . "\">\n";
	$out .= "<meta property=\"og:image\" content=\"" . e($image) . "\">\n";
	$out .= "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
	$out .= "<meta name=\"twitter:site\" content=\"@BritishBrokers\">\n";
	if(!empty($p['json_ld'])){
		$out .= "<script type=\"application/ld+json\">" . json_encode($p['json_ld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . "</script>\n";
	}
	return $out;
}

/** The business, for search engines (home and contact pages). */
function business_json_ld(){
	return array(
		"@context" => "https://schema.org",
		"@type" => "RealEstateAgent",
		"name" => "British Brokers",
		"url" => site_url() . "/",
		"logo" => site_url() . "/img/header_logo.png",
		"image" => site_url() . "/img/bb-main-1.jpg",
		"telephone" => "+94 77 360 3029",
		"email" => "info@britbrokers.com",
		"areaServed" => "Colombo, Sri Lanka",
		"address" => array("@type" => "PostalAddress", "addressLocality" => "Colombo", "addressCountry" => "LK"),
		"sameAs" => array("https://www.facebook.com/Britbrokers", "https://twitter.com/BritishBrokers"),
	);
}

/**
 * Google Analytics 4. Set GA4_MEASUREMENT_ID (e.g. "G-ABC123XYZ") in the config; when it is empty,
 * nothing is sent. Admin pages are not tracked.
 */
function analytics_tag(){
	$id = cfg("GA4_MEASUREMENT_ID");
	if($id === "" || !preg_match('/^G-[A-Z0-9]{4,20}$/', $id) || page_meta("csrf")){
		return "";
	}
	return "<script async src=\"https://www.googletagmanager.com/gtag/js?id=" . e($id) . "\"></script>\n"
		. "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . e($id) . "');</script>\n";
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
 * Link to a property page. When clean URLs are active (see pretty_urls()) this is
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

/**
 * Clean property links need the rewrite rules in .htaccess. By default they are used only when the
 * server confirms those rules are active (.htaccess sets BB_PRETTY_URLS), so a missing or old .htaccess
 * gives single-property.php?pid= links instead of broken ones.
 * PRETTY_URLS="0" turns them off; PRETTY_URLS="1" forces them on.
 */
function pretty_urls(){
	$setting = cfg("PRETTY_URLS", "auto");
	if($setting === "0" || $setting === "1"){
		return $setting === "1";
	}
	foreach(array("BB_PRETTY_URLS", "REDIRECT_BB_PRETTY_URLS", "REDIRECT_REDIRECT_BB_PRETTY_URLS") as $key){
		if(!empty($_SERVER[$key]) || getenv($key)){
			return true;
		}
	}
	return false;
}
