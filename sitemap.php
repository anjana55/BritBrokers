<?php
/*
 * XML sitemap for search engines, served as /sitemap.xml (see .htaccess).
 * Lists the main pages and every property page (by its clean URL). Built from the database on each
 * request, so new properties appear automatically. Submit it once in Google Search Console.
 */
require_once __DIR__ . "/bootstrap.php";

$link = setConnection();
header("Content-Type: application/xml; charset=utf-8");

$pages = array(
	array("", "1.0"),
	array("list_property.php", "0.9"),
	array("list_property.php?src_for=S", "0.9"),
	array("tenants.php", "0.7"),
	array("landlords.php", "0.7"),
	array("sales_investments.php", "0.7"),
	array("contact.php", "0.6"),
	array("terms.php", "0.2"),
	array("privacy.php", "0.2"),
);

$properties = db_all($link,
	"SELECT p.property_id, p.bedrooms, p.property_type, p.status, pt.property_type_name, c.city_name
	FROM properties p
	JOIN property_types pt ON pt.pt_id = p.property_type
	JOIN cities c ON c.city_id = p.city
	ORDER BY p.property_id DESC");

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach($pages as $page){
	echo "  <url><loc>", e(site_url() . "/" . $page[0]), "</loc><priority>", $page[1], "</priority></url>\n";
}
foreach($properties as $property){
	// Let and sold properties stay listed (their pages still exist) but matter less
	$priority = in_array($property['status'], array("Let", "Sold"), true) ? "0.3" : "0.8";
	echo "  <url><loc>", e(property_url($property, array(), true)), "</loc><priority>", $priority, "</priority></url>\n";
}
echo "</urlset>\n";
