<?php
/*
 * Control panel: "Server health" tab. Admin only.
 *   server_health.php            the full report (HTML fragment, loaded into the control panel)
 *   server_health.php?summary=1  quick counts as JSON, for the tab's badge
 */
require_once __DIR__ . "/bootstrap.php";
require_admin_ajax();
require_once SITE_ROOT . "/inc/health.php";
header("Cache-Control: no-store");

if(isset($_GET["summary"])){
	$c = health_counts(health_report(array("web" => true, "deep" => false)));
	header("Content-Type: application/json");
	echo json_encode(array("fail" => $c["fail"], "warn" => $c["warn"]));
	exit();
}

$t0 = microtime(true);
$report = health_report(array("web" => true, "deep" => true));
$c = health_counts($report);
$took = microtime(true) - $t0;

$icons = array(
	"ok"   => array("icon-ok-sign", "OK"),
	"warn" => array("icon-warning-sign", "Needs a look"),
	"fail" => array("icon-remove-sign", "Fix this"),
	"info" => array("icon-info-sign", "Information"),
);
if($c["fail"]){
	$state = array("fail", "icon-remove-sign", $c["fail"] . " thing" . ($c["fail"] === 1 ? "" : "s") . " to fix", "These stop part of the site working or leave it exposed. Each one says what to do.");
} elseif($c["warn"]){
	$state = array("warn", "icon-warning-sign", "Working, " . $c["warn"] . " thing" . ($c["warn"] === 1 ? "" : "s") . " to look at", "Nothing is broken; these are worth sorting out when you have time.");
} else {
	$state = array("ok", "icon-ok-sign", "All good", "Everything checked is working as it should.");
}
?>
<div class="cp-health" data-fail="<?php echo $c["fail"]; ?>" data-warn="<?php echo $c["warn"]; ?>">
	<div class="cp-health-summary cp-s-<?php echo $state[0]; ?>">
		<i class="<?php echo $state[1]; ?>" aria-hidden="true"></i>
		<div class="cp-health-summary-text">
			<h2><?php echo e($state[2]); ?></h2>
			<p><?php echo e($state[3]); ?></p>
		</div>
		<div class="cp-health-summary-counts">
			<span class="cp-count cp-s-fail"><i class="icon-remove-sign" aria-hidden="true"></i> <?php echo $c["fail"]; ?> to fix</span>
			<span class="cp-count cp-s-warn"><i class="icon-warning-sign" aria-hidden="true"></i> <?php echo $c["warn"]; ?> to look at</span>
			<span class="cp-count cp-s-ok"><i class="icon-ok-sign" aria-hidden="true"></i> <?php echo $c["ok"]; ?> OK</span>
			<button type="button" class="btn btn-default btn-sm" id="cp_health_refresh"><i class="icon-refresh" aria-hidden="true"></i> Check again</button>
			<div class="cp-muted">Checked <?php echo date("H:i:s"); ?> in <?php echo number_format($took, 2); ?> s</div>
		</div>
	</div>

	<div class="row">
<?php foreach($report as $section): if(!$section["items"] && empty($section["log"])) continue; ?>
		<div class="<?php echo $section["id"] === "errors" ? "col-xs-12" : "col-lg-6"; ?>">
			<div class="cp-card" id="health_<?php echo e($section["id"]); ?>">
				<h3><?php echo e($section["title"]); ?></h3>
				<table class="cp-checks">
<?php foreach($section["items"] as $item): $ic = $icons[$item["s"]]; ?>
					<tr class="cp-s-<?php echo $item["s"]; ?>">
						<td class="cp-check-icon"><i class="<?php echo $ic[0]; ?>" title="<?php echo $ic[1]; ?>" aria-label="<?php echo $ic[1]; ?>"></i></td>
						<th scope="row"><?php echo e($item["label"]); ?></th>
						<td>
							<?php echo e($item["value"]); ?>
<?php if(isset($item["meter"])): ?>
							<div class="cp-meter" role="img" aria-label="<?php echo $item["meter"]; ?>% used"><span style="width:<?php echo $item["meter"]; ?>%"></span></div>
<?php endif; ?>
<?php if($item["fix"] !== "" && $item["s"] !== "ok"): ?>
							<div class="cp-fix"><?php echo e($item["fix"]); ?></div>
<?php endif; ?>
						</td>
					</tr>
<?php endforeach; ?>
				</table>
<?php if(!empty($section["log"])): ?>
				<pre class="cp-log"><?php echo e(implode("\n", $section["log"])); ?></pre>
				<div class="cp-muted">Newest first. The full log is the error_log file in the site folder (File Manager).</div>
<?php endif; ?>
			</div>
		</div>
<?php endforeach; ?>
	</div>
	<p class="cp-muted">The same checks run from the command line with <code>php bin/check-install.php</code>, useful after uploading a new version.</p>
</div>
