<?php
/*
 * Admin control panel. Each tab's screen is a page fragment loaded into #work_area by
 * js/control-panel.js; the address bar's #hash keeps the open tab, so reloading or the Back button
 * stays where you were.
 */
require_once __DIR__ . "/bootstrap.php";
require_admin();
page_head(array(
	"title" => "Control panel - British Brokers",
	"robots" => "noindex,nofollow",
	"csrf" => true, // adds the CSRF token to every AJAX request made from this page and its screens
	"admin" => true, // admin scripts and styles (uploads, form validation, sortable photos, js/control-panel.js)
));
page_body("home");
$tabs = array(
	"dashboard" => array("icon-dashboard", "Dashboard"),
	"add"       => array("icon-plus", "Add property"),
	"edit"      => array("icon-edit", "Edit property"),
	"delete"    => array("icon-trash", "Delete property"),
	"health"    => array("icon-heart", "Server health"),
);
?>
<div id="cp" class="cp container-fluid">
	<div class="cp-head">
		<div>
			<h1>Control panel</h1>
			<div class="cp-muted">Signed in as <strong><?php echo e(admin_name()); ?></strong></div>
		</div>
		<div class="cp-head-actions">
			<a class="btn btn-default btn-sm" href="index.php" target="_blank" rel="noopener"><i class="icon-external-link" aria-hidden="true"></i> View site</a>
			<button type="button" class="btn btn-default btn-sm" id="btn_logoff"><i class="icon-off" aria-hidden="true"></i> Log out</button>
		</div>
	</div>

	<ul class="nav nav-tabs cp-nav">
<?php foreach($tabs as $id => $tab): ?>
		<li data-cp-tab="<?php echo $id; ?>"<?php echo $id === "dashboard" ? ' class="active"' : ""; ?>>
			<a href="#<?php echo $id; ?>"><i class="<?php echo $tab[0]; ?>" aria-hidden="true"></i> <?php echo $tab[1]; ?><?php if($id === "health"): ?> <span class="cp-badge" id="cp_health_badge" hidden></span><?php endif; ?></a>
		</li>
<?php endforeach; ?>
	</ul>

	<section id="work_area" aria-live="polite">
<?php include __DIR__ . "/stats.php"; ?>
	</section>
</div>
<?php page_end(false); ?>
