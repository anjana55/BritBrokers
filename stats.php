<?php
/*
 * Control panel: "Dashboard" tab. Admin only. Shown inside control_panel.php, which loads the
 * page head (scripts and styles); also loaded on its own when the tab is reopened.
 */
require_once __DIR__ . "/bootstrap.php";
require_admin_ajax();
require_once SITE_ROOT . "/inc/health.php"; // health_ago()
$link = setConnection();

$t = db_one($link, "SELECT COUNT(*) AS total,
		SUM(business_type = 'S') AS sale,
		SUM(business_type = 'R') AS rent,
		SUM(featured = 'Y') AS featured,
		SUM(status = 'Available Now') AS available,
		SUM(time_submitted >= NOW() - INTERVAL 7 DAY) AS week,
		SUM(time_submitted >= NOW() - INTERVAL 30 DAY) AS month,
		SUM(time_submitted < NOW() - INTERVAL 6 MONTH AND status = 'Available Now') AS stale
	FROM properties") ?: array();
$n = function($key) use ($t){ return (int)($t[$key] ?? 0); };

$media = array("PROMN" => 0, "PROPN" => 0, "PROVD" => 0);
foreach(db_all($link, "SELECT img_type, COUNT(*) AS n FROM images WHERE img_type IN ('PROMN','PROPN','PROVD') GROUP BY img_type") as $row){
	$media[$row["img_type"]] = (int)$row["n"];
}

$rates = db_one($link, "SELECT * FROM rates LIMIT 1");
$rate_age = ($rates && !empty($rates["updated_at"])) ? strtotime($rates["updated_at"]) : null;

// Listings added per month, last 12 months (months with none show as 0)
$months = array();
for($i = 11; $i >= 0; $i--){
	$months[date("Y-m", strtotime("first day of -" . $i . " month"))] = 0;
}
foreach(db_all($link, "SELECT DATE_FORMAT(time_submitted, '%Y-%m') AS ym, COUNT(*) AS n FROM properties
		WHERE time_submitted >= DATE_FORMAT(NOW() - INTERVAL 11 MONTH, '%Y-%m-01') GROUP BY ym") as $row){
	if(isset($months[$row["ym"]])){ $months[$row["ym"]] = (int)$row["n"]; }
}
$month_max = max(1, max($months));

$by_type = db_all($link, "SELECT pt.property_type_name AS name, COUNT(*) AS n FROM properties p
		JOIN property_types pt ON pt.pt_id = p.property_type GROUP BY pt.pt_id, pt.property_type_name ORDER BY n DESC");
$by_district = db_all($link, "SELECT d.district_name AS name, COUNT(*) AS n FROM properties p
		JOIN districts d ON d.district_id = p.district GROUP BY d.district_id, d.district_name ORDER BY n DESC LIMIT 6");

$listing_sql = "SELECT p.property_id, p.business_type, p.property_type, p.bedrooms, p.price, p.currency, p.status, p.featured,
		p.time_submitted, pt.property_type_name, c.city_name, d.district_name,
		(SELECT i.img_url FROM images i WHERE i.property_id = p.property_id AND i.img_type = 'PROTH' ORDER BY i.featured = 'Y' DESC, i.img_id LIMIT 1) AS thumb
	FROM properties p
	JOIN property_types pt ON pt.pt_id = p.property_type
	JOIN cities c ON c.city_id = p.city
	JOIN districts d ON d.district_id = p.district";
$recent = db_all($link, $listing_sql . " ORDER BY p.property_id DESC LIMIT 8");

$no_photos_count = (int)(db_one($link, "SELECT COUNT(*) AS n FROM properties p WHERE NOT EXISTS
		(SELECT 1 FROM images i WHERE i.property_id = p.property_id AND i.img_type = 'PROMN')")["n"] ?? 0);
$no_photos = db_all($link, $listing_sql . " WHERE NOT EXISTS (SELECT 1 FROM images i WHERE i.property_id = p.property_id AND i.img_type = 'PROMN')
		ORDER BY p.property_id DESC LIMIT 5");
$stale = db_all($link, $listing_sql . " WHERE p.status = 'Available Now' AND p.time_submitted < NOW() - INTERVAL 6 MONTH
		ORDER BY p.time_submitted LIMIT 5");
$closed_count = (int)(db_one($link, "SELECT COUNT(*) AS n FROM properties WHERE status IN ('Sold', 'Let')")["n"] ?? 0);
$closed = db_all($link, $listing_sql . " WHERE p.status IN ('Sold', 'Let') ORDER BY p.property_id DESC LIMIT 5");

mysqli_close($link);

function cp_price($p){
	return e($p["currency"]) . " " . number_format((float)$p["price"]);
}
function cp_title($p){
	$beds = ((int)$p["bedrooms"] > 0 && strpos($p["property_type"], "LAN") !== 0) ? (int)$p["bedrooms"] . "-bed " : "";
	return $beds . $p["property_type_name"] . ($p["business_type"] === "R" ? " to rent" : " for sale");
}
/** A short list of listings with view and edit buttons. */
function cp_mini_list(array $rows, $what){
	foreach($rows as $p){
		echo '<li><span class="cp-pid">#' . (int)$p["property_id"] . '</span> '
			. '<span class="cp-mini-title">' . e(cp_title($p)) . ', ' . e($p["city_name"]) . '</span>'
			. '<span class="cp-mini-meta">' . e($what($p)) . '</span>'
			. '<span class="cp-mini-actions"><a class="btn btn-default btn-xs" href="' . e(property_url($p)) . '" target="_blank" rel="noopener" title="View on the site"><i class="icon-external-link" aria-hidden="true"></i><span class="sr-only">View</span></a> '
			. '<button type="button" class="btn btn-primary btn-xs" data-cp-edit="' . (int)$p["property_id"] . '">Edit</button></span></li>';
	}
}
?>
<div class="cp-dashboard">
	<div class="row cp-tiles">
		<div class="col-xs-6 col-sm-4 col-lg-2"><div class="cp-tile">
			<div class="cp-tile-label">Listings</div>
			<div class="cp-tile-value"><?php echo number_format($n("total")); ?></div>
			<div class="cp-tile-note"><?php echo number_format($n("sale")); ?> for sale · <?php echo number_format($n("rent")); ?> to rent</div>
		</div></div>
		<div class="col-xs-6 col-sm-4 col-lg-2"><div class="cp-tile">
			<div class="cp-tile-label">Added, last 30 days</div>
			<div class="cp-tile-value"><?php echo $n("month"); ?></div>
			<div class="cp-tile-note"><?php echo $n("week"); ?> in the last 7 days</div>
		</div></div>
		<div class="col-xs-6 col-sm-4 col-lg-2"><div class="cp-tile">
			<div class="cp-tile-label">Available now</div>
			<div class="cp-tile-value"><?php echo number_format($n("available")); ?></div>
			<div class="cp-tile-note"><?php echo $closed_count; ?> marked sold or let</div>
		</div></div>
		<div class="col-xs-6 col-sm-4 col-lg-2"><div class="cp-tile">
			<div class="cp-tile-label">Featured</div>
			<div class="cp-tile-value"><?php echo number_format($n("featured")); ?></div>
			<div class="cp-tile-note">shown first in searches</div>
		</div></div>
		<div class="col-xs-6 col-sm-4 col-lg-2"><div class="cp-tile">
			<div class="cp-tile-label">Photos</div>
			<div class="cp-tile-value"><?php echo number_format($media["PROMN"]); ?></div>
			<div class="cp-tile-note"><?php echo $media["PROPN"]; ?> panoramas · <?php echo $media["PROVD"]; ?> videos</div>
		</div></div>
		<div class="col-xs-6 col-sm-4 col-lg-2"><div class="cp-tile">
			<div class="cp-tile-label">1 USD</div>
			<div class="cp-tile-value"><?php echo $rates ? number_format((float)$rates["LKR"], 2) : "n/a"; ?> <small>LKR</small></div>
			<div class="cp-tile-note"><?php echo $rate_age ? "updated " . e(health_ago($rate_age)) : "not updated automatically"; ?></div>
		</div></div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<div class="cp-card">
				<h3>Listings added per month</h3>
				<div class="cp-columns" role="img" aria-label="Listings added in each of the last 12 months">
<?php $last = array_key_last($months); foreach($months as $ym => $count):
		$label = date("M Y", strtotime($ym . "-01"));
		$show = ($count === $month_max && $count > 0) || $ym === $last; ?>
					<div class="cp-col" data-tip="<?php echo e($label . ": " . $count . " listing" . ($count === 1 ? "" : "s")); ?>" tabindex="0">
						<div class="cp-col-plot">
							<div class="cp-col-bar" style="height:<?php echo $count ? max(2, round(100 * $count / $month_max)) : 0; ?>%"><?php if($show): ?><span class="cp-col-value"><?php echo $count; ?></span><?php endif; ?></div>
						</div>
						<div class="cp-col-label"><?php echo date("M", strtotime($ym . "-01")); ?></div>
					</div>
<?php endforeach; ?>
				</div>
				<table class="sr-only"><caption>Listings added per month</caption>
					<?php foreach($months as $ym => $count): ?><tr><th><?php echo e($ym); ?></th><td><?php echo $count; ?></td></tr><?php endforeach; ?>
				</table>
			</div>
		</div>
		<div class="col-md-4">
			<div class="cp-card">
				<h3>By type</h3>
				<?php $max = max(1, $by_type ? (int)$by_type[0]["n"] : 1); foreach($by_type as $row): ?>
				<div class="cp-hbar"><span class="cp-hbar-label"><?php echo e($row["name"]); ?></span><span class="cp-hbar-track"><span style="width:<?php echo round(100 * $row["n"] / $max); ?>%"></span></span><span class="cp-hbar-value"><?php echo (int)$row["n"]; ?></span></div>
				<?php endforeach; ?>
				<h3 class="cp-h3-gap">Top districts</h3>
				<?php $max = max(1, $by_district ? (int)$by_district[0]["n"] : 1); foreach($by_district as $row): ?>
				<div class="cp-hbar"><span class="cp-hbar-label"><?php echo e($row["name"]); ?></span><span class="cp-hbar-track"><span style="width:<?php echo round(100 * $row["n"] / $max); ?>%"></span></span><span class="cp-hbar-value"><?php echo (int)$row["n"]; ?></span></div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<div class="cp-card">
				<h3>Recently added</h3>
				<div class="table-responsive">
				<table class="table cp-table">
					<thead><tr><th></th><th>Listing</th><th>Price</th><th>Status</th><th>Added</th><th></th></tr></thead>
					<tbody>
<?php foreach($recent as $p): ?>
						<tr>
							<td class="cp-thumb"><?php if($p["thumb"]): ?><img src="<?php echo e($p["thumb"]); ?>" alt="" loading="lazy" width="56" height="42"><?php else: ?><span class="cp-thumb-none" title="No photos"><i class="icon-picture" aria-hidden="true"></i></span><?php endif; ?></td>
							<td><span class="cp-pid">#<?php echo (int)$p["property_id"]; ?></span> <?php echo e(cp_title($p)); ?><?php if($p["featured"] === "Y"): ?> <span class="label label-default">Featured</span><?php endif; ?><div class="cp-muted"><?php echo e($p["city_name"] . ", " . $p["district_name"]); ?></div></td>
							<td class="cp-nowrap"><?php echo cp_price($p); ?></td>
							<td class="cp-nowrap"><?php echo e($p["status"]); ?></td>
							<td class="cp-nowrap"><?php echo e(date("j M Y", strtotime($p["time_submitted"]))); ?></td>
							<td class="cp-nowrap text-right">
								<a class="btn btn-default btn-xs" href="<?php echo e(property_url($p)); ?>" target="_blank" rel="noopener">View</a>
								<button type="button" class="btn btn-primary btn-xs" data-cp-edit="<?php echo (int)$p["property_id"]; ?>">Edit</button>
							</td>
						</tr>
<?php endforeach; ?>
					</tbody>
				</table>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="cp-card">
				<h3>Needs attention</h3>
				<div class="cp-attention">
					<h4><i class="icon-picture" aria-hidden="true"></i> No photos <span class="badge"><?php echo $no_photos_count; ?></span></h4>
					<?php if($no_photos): ?><ul class="cp-mini"><?php cp_mini_list($no_photos, function($p){ return "added " . date("j M Y", strtotime($p["time_submitted"])); }); ?></ul>
					<?php else: ?><p class="cp-muted">Every listing has at least one photo.</p><?php endif; ?>
				</div>
				<div class="cp-attention">
					<h4><i class="icon-time" aria-hidden="true"></i> Available for over 6 months <span class="badge"><?php echo $n("stale"); ?></span></h4>
					<?php if($stale): ?><p class="cp-muted">Oldest first. Check they are still on the market, or mark them sold or let.</p><ul class="cp-mini"><?php cp_mini_list($stale, function($p){ return "since " . date("M Y", strtotime($p["time_submitted"])); }); ?></ul>
					<?php else: ?><p class="cp-muted">None.</p><?php endif; ?>
				</div>
				<div class="cp-attention">
					<h4><i class="icon-check" aria-hidden="true"></i> Marked sold or let <span class="badge"><?php echo $closed_count; ?></span></h4>
					<?php if($closed): ?><p class="cp-muted">Still shown on the site. Delete them when they are no longer useful as examples.</p><ul class="cp-mini"><?php cp_mini_list($closed, function($p){ return $p["status"]; }); ?></ul>
					<?php else: ?><p class="cp-muted">None.</p><?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
