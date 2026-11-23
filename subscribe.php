<?php require_once __DIR__ . "/bootstrap.php"; ?>
<?php
page_head(array(
	"title" => "British Brokers - Subscribe to newsletter",
	"description" => "British Brokers Newsletter subscription",
));
?>

<?php page_body("home"); ?>
<?php
require_once __DIR__ . "/bootstrap.php";
?>
    <!-- subheader begin -->
    <div id="subheader">
        <div class="container">
            <div class="row">
                <div class="span6">
                    <h1>Subscribe to our Newsletter</h1>
                </div>
                <div class="span6">
                    <div class="crumb"><a href="index.php">Home</a> <span class="separator">></span>Newsletter Subscription</div>
                </div>
            </div>
        </div>
    </div>
    <!-- subheader close -->

    <!-- content begin -->
    <div id="content">
        <div class="container">
            <div class="row">
                <div class="span8">
                    <div class="blog-read">
                        <div>
                            <div class="preview">
								<!--
                                <img src="terms.jpg" data-original="img/terms.jpg" alt="">
								-->
								<h1>Newsletter Subscription</h1>
								Please provide your email address:
								<p>
								<iframe width="100%" scrolling="no" frameborder="0" src="http://britbrokers.com/blog/?wysija-page=1&controller=subscribers&action=wysija_outter&wysija_form=1&external_site=1&wysijap=subscriptions-3" class="iframe-wysija" vspace="0" tabindex="0" style="position: static; top: 0pt; margin: 0px; border-style: none; height: 330px; left: 0pt; visibility: visible;" marginwidth="0" marginheight="0" hspace="0" allowtransparency="true" title="Subscription MailPoet"></iframe>
								</p>								
							</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content close -->

<?php
page_end(true, array("pid" => $pid ?? ""));
?>
