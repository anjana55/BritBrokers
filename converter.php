<?php require_once __DIR__ . "/bootstrap.php"; ?>
<?php
page_head(array(
	"title" => "Finding the Right Property to Rent in Colombo. Best selection of Houses & Apartments",
	"description" => "",
));
?>
<meta name="description" content="We at British Brokers know how important it is for you to find the right home.
Experts in the local rental market, we will listen carefully to your needs and help you find the best house or apartment within your budget.">
<meta name="keywords" content="We at British Brokers know how important it is for you to find the right home.
Experts in the local rental market, we will listen carefully to your needs and help you find the best house or apartment within your budget.">



<?php page_body("home"); ?>
<?php
/*
require_once __DIR__ . "/bootstrap.php";
$link = setConnection();

include SITE_ROOT . "/inc/set_post_vars.php";
*/
?>


    <!-- content begin -->
	<div class="container">
		<div class="content">
			<div class="col-sm-12 col-md-12">
				<br>
				<h4 class="page_title">Currency Converter</h4>
				<iframe src="https://www.google.com/finance/converter">
				<div id="cc"></div>
			</div>
		</div>
	</div>
    <!-- content close -->

<?php
page_end(true, array("pid" => $pid ?? ""));
//@mysqli_close($link);
?>
