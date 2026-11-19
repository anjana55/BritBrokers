<?php
if(isset($_REQUEST['pid']) && $_REQUEST['pid']!=''){
	$pid=$_REQUEST['pid'];

	if(isset($_REQUEST['bt']) && $_REQUEST['bt']!=''){
		$bt=$_REQUEST['bt'];
	} else {
		$bt="";
	}

	if(isset($_REQUEST['did']) && $_REQUEST['did']!=''){
		$did=$_REQUEST['did'];
	} else {
		$did="";
	}
} else {
	//echo "Invalid access attempt!";
	header("Location: http://britbrokers.com"); /* Redirect browser */
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php
include_once "functions.php";
$link = setConnection();

$properties = getPropertyByID($link, $pid);

if($properties){
	$property = $properties[0];
} else {
	echo "Invalid property id";
}
//var_dump($properties);

include "set_post_vars.php";

$total_records = getProperties($link,$bt,$pt, $features, $min_bdrm, $max_bdrm, $min_btrm, $max_btrm, $min_price, $max_price, $keywords, $district, $city, $featured, $sort_by, $sort_order,null,null,true);

$max_records_per_page=8;
$max_pages_display=5;
$pagination=true;
$start_from = ($current_page-1) * $max_records_per_page;
//$properties = getProperties($link,'','','','','','','','','','','','','',8);
$properties = getProperties($link,$bt,$pt, $features, $min_bdrm, $max_bdrm, $min_btrm, $max_btrm, $min_price, $max_price, $keywords, $district, $city,$featured, $sort_by, $sort_order,$start_from,$max_records_per_page );

$page_name=basename($_SERVER['PHP_SELF']);
include "header.php";

$title = $property['property_type_name'] . " for " . $property['business_type'] . " at " . $property['city_name'] . ", " . $property['district_name'];
?>
<title>British Brokers - <?php echo $title; ?></title>
<meta name="description" content="<?php echo "British Brokers Properties: " . $title; ?>">
<meta name="keywords" content="<?php echo "British Brokers Properties: " . $title; ?>">

<!-- use jssor.slider.min.js instead for release -->
<!-- jssor.slider.min.js = (jssor.js + jssor.slider.js) -->
<script type="text/javascript" src="js/jssor.js"></script>
<script type="text/javascript" src="js/jssor.slider.js"></script>
<!-- use jssor.slider.player.ytiframe.min.js for release -->
<script type="text/javascript" src="js/jssor.player.ytiframe.js"></script>
<script src="js/jquery-ui.js"></script>
<link rel="stylesheet" href="css/jquery-ui.css">
<script src="js/jquery.pano.js"></script>

<style>
.pano {
  width: 100%;
  height: 100%;
  margin: 0 auto;
  cursor: move;
}

.pano .controls {
  position: relative;
  top: 40%;
}

.pano .controls a {
  position: absolute;
  display: inline-block;
  text-decoration: none;
  color: #eee;
  font-size: 3em;
  width: 20px;
  height: 20px;
}

.pano .controls a.left { left: 10px; }

.pano .controls a.right { right: 10px; }

.pano.moving .controls a {
  opacity: 0.4;
  color: #eee;
}
</style>

<script>
    jQuery(document).ready(function ($) {
		var prop_id = <?php echo $pid; ?>;
		var popped=false;
		var slide_width=900;
		var slide_height=675;
		
        var options = {
			$FillMode: 5,
            $ThumbnailNavigatorOptions: {
				$Class: $JssorThumbnailNavigator$, //[Required] Class to create thumbnail navigator instance
				$ChanceToShow: 2, //[Required] 0 Never, 1 Mouse Over, 2 Always
				$Lanes: 1, //[Optional] Specify lanes to arrange thumbnails, default value is 1
				$SpacingX: 14, //[Optional] Horizontal space between each thumbnail in pixel, default value is 0
				$SpacingY: 12, //[Optional] Vertical space between each thumbnail in pixel, default value is 0
				$DisplayPieces: 8, //[Optional] Number of pieces to display, default value is 1
				$ParkingPosition: 156, //[Optional] The offset position to park thumbnail
				$Orientation: 1 //[Optional] Orientation to arrange thumbnails, 1 horizental, 2 vertical, default value is 1
            },
            $ArrowNavigatorOptions: {
				$Class: $JssorArrowNavigator$, //[Required] Class to create thumbnail navigator instance
				$ChanceToShow: 2, //[Required] 0 Never, 1 Mouse Over, 2 Always
				$AutoCenter: 2
            }			
        };


        var jssor_slider1 = new $JssorSlider$('slider1_container', options);
		//responsive code begin
		//you can remove responsive code if you don't want the slider scales while window resizes
		function ScaleSlider1() {
			var parent1Width = jssor_slider1.$Elmt.parentNode.clientWidth;
			if (parent1Width){
				jssor_slider1.$ScaleWidth(Math.min(parent1Width, slide_width));
			} else {
				window.setTimeout(ScaleSlider1, 30);
			}
		}
		ScaleSlider1();

		$(window).bind("load", ScaleSlider1);
		$(window).bind("resize", ScaleSlider1);
		$(window).bind("orientationchange", ScaleSlider1);
		$JssorPlayer$.$FetchPlayers(document.body);

		
		$("#phone").click(function(){
			$("#phone_no").toggle();
		});
	;

		//Bootstrap form validation
		$('#frm_staf').bootstrapValidator({
			message: 'This value is not valid',
			feedbackIcons: {
				valid: 'glyphicon glyphicon-ok',
				invalid: 'glyphicon glyphicon-remove',
				validating: 'glyphicon glyphicon-refresh'
			},
			fields: {
				s_name: {
					message: 'The senders name is not valid',
					validators: {
						notEmpty: {
							message: 'The senders name is required and cannot be empty'
						},
						stringLength: {
							min: 2,
							max: 30,
							message: 'The senders name must be more than 2 and less than 30 characters long'
						},
						regexp: {
							regexp: /^[a-zA-Z0-9_\s]+$/,
							message: 'The senders name can only consist of alphabetical, number and underscore'
						}
					}
				},
				s_email: {
					validators: {
						notEmpty: {
							message: 'The senders email is required and cannot be empty'
						},
						emailAddress: {
							message: 'The senders email is not a valid email address'
						}
					}
				}
			}
		});		
		
		$("#msg_send2").click(function(){
			//alert($('#contact_bb').serialize());
			//Human validation check
			$.ajax({
				type: 'POST',
				url: 'ajax_functions.php',
				data: {
						cid: $("#cid").val(),
						uid: $("#captcha").val(),
						tsk: $("#tsk").val()
					},
				success: function(response) {
					if(response!=="" && response.length>2 && response=="success"){
						$.ajax({
							type: 'POST',
							url: 'mailman.php',
							data: {
									from_name: "British Brokers",
									from_email: "contact@britbrokers.com" ,
									to_name: $("#s_fname").val(),
									to_email: $("#s_femail").val(),
									subject: $("#s_name").val() + ' wants you to take a look at this property at britbrokers.com',
									method:'url',
									message:$("#s_message").val(),
									url: $("#prop_url").val()
								},
							success: function(response) {
								if(response!=="" && response.length>2){
									$("#error_msg2").addClass("alert alert-success");
									var res = response.split("|");
									$("#error_msg2").html(res[1]);
									$("#msg_send2").hide();
								} else {
									$("#error_msg2").addClass("alert alert-danger");
									$("#error_msg2").html("Communication error occured");
								}
								$("#error_msg2").show();
								$("#error_msg2").css({"display":"inline"});
								//$("#work_area").html(response);
								$("#loadingDiv").modal("hide");
							}
						});				

					} else {
						$("#cpt_error").show();
					}
					$("#loadingDiv").modal("hide");
				}

			});							
		});
		
		$("#btn_send").click(function(){
			$("#send_friend").show();
		});	

		var jssor_slider2 = new $JssorSlider$('slider2_container', options);
		
		function ScaleSlider2() {
			var parent2Height = jssor_slider1.$Elmt.parentNode.clientHeight*.9;
			
			if (parent2Height){
				var parent2Width = $(window).width();
				var originalWidth = jssor_slider2.$OriginalWidth();
				var originalHeight = jssor_slider2.$OriginalHeight();
				if (originalWidth / parent2Width > originalHeight / parent2Height) {
					jssor_slider2.$ScaleHeight(Math.min(parent2Height, slide_height));
				}else {
					jssor_slider2.$ScaleWidth(Math.min(parent2Width, slide_width));
				}				
				//jssor_slider2.$ScaleHeight(Math.min(parent2Height, slide_height));
			} else {
				window.setTimeout(ScaleSlider2, 30);
			}
		}
				
		$("[id^=img_]").click(function(){
			if(!popped){
				$(window).bind("load", ScaleSlider2);
				$(window).bind("resize", ScaleSlider2);
				$(window).bind("orientationchange", ScaleSlider2);
				$JssorPlayer$.$FetchPlayers(document.body);		
				popped=true;
			} else {
				$(window).bind("load", ScaleSlider1);
				$(window).bind("resize", ScaleSlider1);
				$(window).bind("orientationchange", ScaleSlider1);
				$JssorPlayer$.$FetchPlayers(document.body);				
			}
			//$('#slider_pop').modal('show')
			$("#slider_pop").show("slow");
			$("#navigation").addClass("hidden");
			$("#nav_back").addClass("hidden");
			$("#tawkchat-iframe-container").addClass("hidden");
			ScaleSlider2();
		});
		
		$("#pop_close").click(function(){
			$("#slider_pop").hide("slow");
			$("#navigation").removeClass("hidden");
			$("#nav_back").removeClass("hidden");
			$("#tawkchat-iframe-container").removeClass("hidden");

		});

		//Panorama view resize
		function panResize(){
			var newHeight = $('#panoParent').css('height');
			$('#myPano').css({ 'height': newHeight});
			newHeight = parseInt(newHeight);
			$('#pimg_l').css({ 'height': newHeight * .1, 'background' : ''});
			$('#pimg_r').css({ 'height': newHeight * .1, 'background' : ''});
		}
		
		$(window).bind("load", panResize);
		$(window).bind("resize", panResize);
		$(window).bind("orientationchange", panResize);
		
		$("#pantab").click(panResize);
		
		$("#converter").click(function(){			
			var ctype="LKR";
			var amount="1";
			
			ctype = $("#ctype").val();
			amount = $("#amount").val();

			$.ajax({
				type: 'POST',
				url: 'ajax_functions.php',
				data: {
						cur: ctype,
						amt: amount,
						tsk: "exch"
					},
				success: function(response) {
					$("#cc").html("<h2>" + response + "</h2>");
				}

			});				
			//$("#cc").attr("src", "https://www.google.com/finance/converter?a=" + amount + "&from=" + ctype + "&to=LKR");
			//$("#cc").attr("src", "http://rate-exchange-1.appspot.com/currency?from=" + ctype + "&to=LKR");
			//$("#cc").attr("src",exchange(amount, ctype, "LKR"));
			$("#cur_converter").modal("show");
		});
	});
</script>
</head>
<body id="home">
<?php
include "navigation.php";
include "slider_full.php";
?>

    <!-- subheader begin -->
    <div id="subheader">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-md-6">
                    <h1><?php echo $property['property_type_name'] . " for " . $property['business_type'] . " at " . $property['city_name'] . ", " . $property['district_name']; ?></h1>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="crumb"><a href="index.php">Home</a> <span class="separator">></span> For <?php echo $property['business_type']; ?></div>
                </div>
            </div>
        </div>
    </div>
    <!-- subheader close -->

    <!-- content begin -->
    <div id="content">
        <div class="container">
            <div class="row">
                <div class="row">
                    <div class="row single-property">
                        <div class="col-sm-9 col-md-9">
							<div class="lt_tab">
								<ul class="lt_nav">
									<li><span class="active">Photos</span></li>
									<?php
										$videos = getPropertyImages($link,$pid, 25,"PROVD");
										if($videos){
											echo "<li><span>Videos</span></li>";
										}
										
										$panoramas = getPropertyImages($link,$pid, 25,"PROPN");
										if($panoramas){
											echo "<li id='pantab'><span>360&deg; View</span></li>";
										}
									?>
									
								</ul>

								<div class="lt_tab_content">
									<div id="tab1" class="lt_content">
										<?php
										include "slider.php";
										$tab=1;
										?>
									</div>
									
									<?php
									if($videos){
										$tab++;
										echo "<div id='tab" . $tab . "' class='lt_content'>";
										foreach($videos as $video){
											echo "<div class='embed-responsive embed-responsive-16by9'>";
											echo "<iframe width='560' height='315' src='https://www.youtube.com/embed/" . $video['img_url'] . "' frameborder='0' allowfullscreen></iframe>";
											echo "</div>";
										}
										echo "</div>";
									}
									?>
									
									<?php
									if($panoramas){
										$tab++;
										echo "<script>";
										echo "$(document).ready(function(){";
										echo "	$('#myPano').pano({";
										
										$sep="";
										foreach($panoramas as $panorama){
											//echo "<div class='panorama' style='background:url(" . $panorama['img_url'] . ");height:500px;background-size:auto 500px;'></div>";
											
											echo "img: '" . $panorama['img_url'] . "'" . $sep;
											$sep=",";
										}
										
										echo "	});";
										echo "});";
										echo "</script>";

										
										echo "<div id='tab" . $tab . "' class='lt_content'>";
										echo "	<div id='panoParent' class='embed-responsive embed-responsive-16by9' style='cursor:all-scroll;'>";
										
										echo "		<div id='myPano' class='pano' style='height:500px;background-size:auto 500px;'>";
										echo "			<div class='controls'>";
										echo "				<a href='#' class='left'><img id='pimg_l' src='img/arrow_l.png' '></a>";
										echo "				<a href='#' class='right'><img id='pimg_r' src='img/arrow_r.png' ></a> ";
										echo "			</div>";
										echo "		</div>";
										

										echo "	</div>";
										echo "</div>";
									}
									?>
								</div>
							</div>
						</div>
                        <div class="col-sm-3 col-md-3">
						<?php
						if($property['business_type']=='Sale'){
							$pclass= 'for-sale';
						} else if ($property['business_type']=='Rent'){
							$pclass = 'for-rent';
						}					
						?>
							<div class="row">

									<span class="<?php echo $pclass; ?>">
									<?php 
										echo $property['business_type'];
									?>
									</span>

									<?php
										if($property['status']=="Let" || $property['status']=="Sold"){
											echo "<span class='status'>" . $property['status'] . "</span>";
										}
									?>

							</div>

							<span>&nbsp;Reference #: <?php echo $pid; ?></span>

                            <h3 class="no-btm"><?php echo $property['district_name']; ?></h3>
                            <?php echo $property['city_name']; ?>
							<input type="hidden" name="ctype" id="ctype" value="<?php echo $property['currency']; ?>">
							<input type="hidden" name="amount" id="amount" value="<?php echo $property['price']; ?>">
							

							<span class="price">
								<?php
								$price = $property['price'];
								if($property['currency']!=="LKR"){
									$currency = $property['currency'];
								} else {
									$currency = "Rs.";
								}

								$display_price = $currency . " " . number_format($price,0,".",",");

								echo $display_price;

								$options = array( "d"=>"Per day", "w"=>"Per week", "m"=>"Per month", "y"=>"Per year" );
								?>
								<a id='converter' href='#' style="font-size:15px;">
									<span class="glyphicon"><img src="img/calculator.png" style="height:15px;"></span>
								</a>
							</span>							
							<?php 
								
								if($property['business_type']=='Sale'){
									
								} else if ($property['business_type']=='Rent'){
									echo "<span class='label label-default'>";
									echo $options[$property['term']]; 
									echo "</span>";								
								}
							?>
                            <ul class="feature-list">
                                <li><?php echo $property['bedrooms']; ?> Bedroom(s)</li>
                                <li><?php echo $property['bathrooms']; ?> Bathroom(s)</li>
								<?php
									$features = getFeaturesByPropertyID($link, $pid);
									
									if($features){
										foreach($features as $feature){
											echo "<li>" . $feature['feature'] . "</li>";
										}
									}
								?>
                            </ul>
                        </div>

                    </div>

                    <hr class="blank">

                    <div class="lt_tab">
                        <ul class="lt_nav">
                            <li><span class="active">Information</span></li>
							<li><span>Contact</span></li>
							<li><span>Share</span></li>
                        </ul>

                        <div class="lt_tab_content">

                            <div id="tab1" class="lt_content">
								<div class="container">
								    <!--
									<div class="col-md-3 col-lg-3">
									<?php
										//echo getQRCode(curPageURL(),"M");
									?>
									</div>
									-->
									<div class="col-md-12 col-lg-12">
									<?php
										echo $property['description'];
									?>
									</div>
								</div>
                            </div>

                            <div id="tab2" class="lt_content">
								<div class="col-xs-12 col-sm-3">
									<img src="img/header_logo.png" height="32px;"/>
								</div>
								<div class="col-xs-12 col-sm-3">
									<button type="button" class="btn btn-default" id="btn_email" data-toggle="modal" data-target="#pop_msg"><img src="img/email.png" width="16px;">&nbsp;Email us</button>
								</div>
								<div class="col-xs-12 col-sm-3">
									<button type="button" id="phone" class="btn btn-default"><img src="img/phone.png" width="16px;">&nbsp;Phone us</button>
								</div>
								<div class="col-xs-12 col-sm-3">
									<div id="phone_no" style="display:none;">
									<h4><a href="tel:+94773603029">+94 (0) 773603029</a></h4>
									</div>								
								</div>
                                <div class="clearfix"></div>
                            </div>
							<div id="tab3" class="lt_content">
								<div class="social-icons">
									<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']; ?>">
									<img alt="Share with Facebook" src="img/social-icons/facebook.png">
									</a>
									<a href="https://twitter.com/home?status=<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']; ?>">
									<img alt="Share with Twitter" src="img/social-icons/twitter.png">
									</a>
									<a href="https://plus.google.com/share?url=<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']; ?>">
									<img alt="Share with Google+" src="img/social-icons/google+.png">
									</a>
									<button type="button" class="btn btn-default" id="btn_send" style="padding:5px;"><img src="img/email.png" width="16px;">&nbsp;Send to a friend</button><br/>
								</div>
								
								<div class="panel panel-default" id="send_friend" style="display:none;margin-top:10px;">
									<div class="panel-heading">Send to a Friend</div>
									<div class="panel-body">
										<form name="frm_staf" id="frm_staf" action="#" method="POST" class="form-horizontal" data-toggle="validator">
											<div class="form-group">
												<div class="col-md-4">
													<label for="s_name" class="control-label input">Your Name</label>
												</div>
												<div class="col-md-8">
													<input type="text" name="s_name" id="s_name" placeholder="Your Name" class="form-control input" required>
													<div class="help-block with-errors"></div>
												</div>
											</div>
											<div class="form-group">
												<div class="col-md-4">
													<label for="s_email" class="control-label input">Your Email</label>
												</div>
												<div class="col-md-8">
													<input type="email" name="s_email" id="s_email"  placeholder="your_email@domain.com" class="form-control input" required>
													<div class="help-block with-errors"></div>
												</div>
											</div>
											<div class="form-group">
												<div class="col-md-4">
													<label for="s_fname" class="control-label input">Your Friend's Name</label>
												</div>
												<div class="col-md-8">
													<input type="text" name="s_fname" placeholder="Your Friend's Name" class="form-control input" required>
													<div class="help-block with-errors"></div>
												</div>
											</div>
											<div class="form-group">
												<div class="col-md-4">
													<label for="s_femail" class="control-label input">Your Friend's Email</label>
												</div>
												<div class="col-md-8">
													<input type="email" name="s_femail" id="s_femail" placeholder="your_email@domain.com" class="form-control input" required>
													<div class="help-block with-errors"></div>
												</div>
											</div>
											<div class="form-group">
												<div class="col-md-4">
													<label for="s_message" class="control-label input">Message</label>
												</div>
												<div class="col-md-8">
													<textarea id="s_message" name="s_message" rows="4" placeholder="Your Message" class="form-control input" required></textarea>
													<div class="help-block with-errors"></div>
												</div>
											</div>
											<div class="form-group">
												<div class="col-md-4">
													<label for="captcha" class="control-label input">Human Verification</label>
												</div>
												<div class="col-md-8">
													<?php
													$rnd = rnd_string();
													$uid = urlencode(md5_encrypt($rnd));
													$cid = md5_encrypt($rnd);
													?>
													<div class="col-xs-4">
														<img src="captcha_image.php?uid=54;<?php echo $uid ?>" alt="" align="left" style="padding:3px">
													</div>
													<div class="col-xs-4">
														<input type="hidden" name="tsk" id="tsk" value="capt" />
														<input type="hidden" name="cid" id="cid" value="<?php echo $cid; ?>" />
														<input type="text" name="uid" id="captcha" required/><br/>
														<span id="cpt_error" class="error" style="display:none;">Human validation failed</span>
														<div class="help-block with-errors"></div>
													</div>
													<div class="col-xs-4">
														<span class="help-block">Are you human? Enter the text displayed in the animated image box in to the text box.</span>
													</div>
												</div>
											</div>
											<div class="form-group">
												<input type=hidden name="cid" value="<?php echo $cid ?>">
												<input type="hidden" name="message[]" value="<hr>Property ID: <?php echo  $pid . getUserInfo();  ?>" />
												<input type="hidden" id="prop_url"  name="prop_url" value="<?php echo "http://www.britbrokers.com/live/email-property.php?pid=" . $pid; ?>" />
											</div>
										</form>
									</div>
									<div class="panel-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										<button type="button" class="btn btn-primary" id="msg_send2">Send</button>									
									</div>
								</div>
							</div>
                        </div>
                    </div>
                </div>
            <hr class="blank">
			
            <div class="row">
				<!-- Search Begins -->
				<section id="property-search">
					<div class="container">
					<?php
						include("search_box.php");
					?>
					</div>
				</section>
				<!-- Search Ends -->		 	
			</div>
            <div class="row" style="margin-top:20px;">
                <div class="cols-xs-12 col-sm-12 col-md-9">
                    <h3>See Also</h3>
                    <div class="">
						<?php
						$pagination=false;
						$properties = getProperties($link,$bt,'','','','','','','','','','','','','featured','DESC',0,8);
						//$properties = getProperties($link,$bt,$pt, $features, $min_bdrm, $max_bdrm, $min_btrm, $max_btrm, $min_price, $max_price, $keywords, $district, $city, $featured, $sort_by, $sort_order, start_from, max_rec, count_only );
						include "print_properties.php";
						?>
					</div>
                </div>
                <div class="col-sm-3 col-md-3">
                    <div class="widget latest_news">
						<?php
						include "latest_news.php";
						?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content close -->	
<?php
include "sub_footer.php";
include "footer.php";
@mysqli_close($link);
?>
</body>
</html>
