a<!DOCTYPE html>
<html lang="en">
<head>
<?php
include "header.php";
?>
<title>Renting Colombo Properties to Expat Tenants. Best Rentals in the Shortest Time</title>
<meta name="description" content="With extensive brokerage experience and a strong reputation with both landlords and tenants alike, British Brokers is your ideal real estate partner.  Striving to find tenants quickly and efficiently, we are here to make the process of renting out your property as smooth as possible.">
<meta name="keywords" content="With extensive brokerage experience and a strong reputation with both landlords and tenants alike, British Brokers is your ideal real estate partner.  Striving to find tenants quickly and efficiently, we are here to make the process of renting out your property as smooth as possible.">
</head>
<script src="js/jssor.slider.mini.js"></script>
<script src="js/jquery.flexslider.js"></script>
<script>
    jQuery(document).ready(function ($) {
		var _SlideshowTransitions = [{$Duration:1000,$Delay:60,$Cols:1,$Rows:1,$Opacity:2}];
        var options = {
            $DragOrientation: 3,
            $AutoPlay: true,
            $SlideDuration: 1500,
            $AutoPlayInterval: 4500,
            $SlideshowOptions: {                                //Options which specifies enable slideshow or not
                $Class: $JssorSlideshowRunner$,                 //Class to create instance of slideshow
                $Transitions: _SlideshowTransitions,            //Transitions to play slide, see jssor slideshow transition builder
                $TransitionsOrder: 1,                           //The way to choose transition to play slide, 1 Sequence, 0 Random
                $ShowLink: 2,                                   //0 After Slideshow, 2 Always
                $ContentMode: false                             //Whether to trait content as slide, otherwise trait an image as slide
            }
        };	
        var jssor_slider1 = new $JssorSlider$('slider1_container', options);
		//responsive code begin
		//you can remove responsive code if you don't want the slider scales while window resizes
		function ScaleSlider() {
			var parentWidth = jssor_slider1.$Elmt.parentNode.clientWidth;
			if (parentWidth)
				jssor_slider1.$ScaleWidth(Math.min(parentWidth, 1920));
			else
				window.setTimeout(ScaleSlider, 30);
		}
		ScaleSlider();

		$(window).bind("load", ScaleSlider);
		$(window).bind("resize", ScaleSlider);
		$(window).bind("orientationchange", ScaleSlider);
		//responsive code end    

		$('.flexslider').flexslider();		
	});
</script>
<body id="home">
<?php
include "navigation.php";
include_once "functions.php";
$link = setConnection();
include "set_post_vars.php";
?>

    <!-- content begin -->
    <div id="container">
	<!-- slider -->
		<section id="slider">
			<div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: 1920px; height: 672px;">
			<!-- Slides Container -->
				<div u="slides" style="cursor: move; position: absolute; overflow: hidden; left: 0px; top: 0px; width: 1920px; height: 672px;">
					<div>				
						<img src="img/landlords-lg-1.jpg" alt="">
					</div>
					<div><img src="img/landlords-lg-2.jpg" alt=""></div>
					<div><img src="img/landlords-lg-3.jpg" alt=""></div>
				</div>
			</div>
		</section>
		<!-- slider close -->
		
		<!-- Search Begins -->
		<section id="property-search">
			<div class="container-fluid">
			<?php
				include("search_box.php");
			?>
			</div>
		</section>
		<!-- Search Ends -->
		
        <div class="container">
            <div class="content">
				<div class="col-sm-12 col-md-12">
					<br>
					<h4 class="page_title">Landlords</h4>
					<p>Renting out your property can be a daunting prospect. But with extensive brokerage experience and a strong reputation with both landlords and tenants alike, British Brokers is your ideal real estate partner. </p>
					
				</div>
				<div class="col-sm-8 col-md-8">
					<h4>Comprehensive database</h4>

					<p>We have a comprehensive database of prospective tenants, mostly expats, who are looking for everything from one-bed apartments to luxury houses and everything in between.</p>
					
					<div class="clearfix"></div>
					<h4>Reliable tenants</h4>

					<p>British Brokers has close ties to all the major companies, embassies and high commissions working in Sri Lanka. For this reason, we are able to show your property to reliable and well-established tenants.</p>
					
					<div class="clearfix"></div>
					<h4>Professional negotiators</h4>

					<p>You want to rent your property for the best possible price, in the shortest possible time. We pride ourselves in achieving this goal. Our aim is always to reach a fair deal, ensuring that both parties are happy with the result.</p>
					
					<div class="clearfix"></div>
					<h4>Predictable rental income</h4>

					<p>As we specialise in long-term rentals, you will have the security of predictable rental income over a substantial period of time, without the worry of having to continually find a new tenant.</p>

					<div class="clearfix"></div>
					<h4>Legal services</h4>

					<p>If landlords do not have their own draft lease agreement, we can provide standard documentation and/or arrange for one to be drafted and have any questions answered by our legal partners.</p>

					<div class="clearfix"></div>
					<h4>Interior design and furniture-making</h4>

					<p>British Brokers recognises the need of many landlords to furnish their properties to a high standard, making it more attractive to potential tenants and helping to maximize the return on investment.</p>
					<p>We partner with some of the best interior designers and furniture manufacturers in Sri Lanka. You can see some of their work in our slide show. Offering highly competitive prices and cutting edge design we can meet your requirements, be it an order for an individual item of furniture or fully furnishing an empty property.</p>

					<div class="clearfix"></div>
					<h4>Preparation for rental</h4>
					<p>When a tenancy has come to an end, it is usually a good idea to re-paint or touch-up your property. A thorough clean is also essential to attracting the best tenants.</p>
					<p>We work with preferred contractors who are experts in making your property look the best it can be.</p>
					<p><b>We won’t just put your property on the market, we’ll rent it.</b></p>
					<br/>
				</div>
				<div class="col-sm-4 col-md-4">	
					<div class="featured-box filled">
						<h4>Why rent your property through British Brokers?</h4>

						<ul>
							<li>The best prospective tenant database in the market</li>
							<li>Thorough understanding of expat tenants’ needs</li>
							<li>Proactive approach to marketing your property through the most effective channels</li>
							<li>Excellent value-added services at the best prices</li>
							<li>Friendly, professional, trustworthy and straight-talking</li>
						</ul>
						<a href="contact.php">Click here</a> to send you property requirements
					</div>
				</div>
            </div>
        </div>
		
        <section id="testimonial" data-speed="6" data-type="background">
			<div class="container">
				<div class="col-lg-12 hidden-md hidden-sm hidden-xs">				
					<div class="row">
						<div class="flexslider testi-slider">
							<ul class="slides">
								<li>'We were looking for a tenant who would care for our property as their own and British Brokers found us a perfect match. From the outset, their service was very professional. Starting with the initial viewing through negotiations with the prospective tenant, dealing with the paperwork and until the tenant family were settled in their new home, British Brokers were always there to handle the details. We would gladly use their services again.'<span>Anoop Kundanmal</span>
								</li>
								<li>'British Brokers are exceptionally professional, efficient and it is a pleasure to work with them.'<span>Dr Harsha Cabral PC</span>
								</li>
								<li>'We worked with British Brokers in 2014 to find a suitable tenant for our home. They were easy to work with and provided an honest view point regarding our expectations.'<span>Navin Piyatissa</span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</section>
    </div>
    <!-- content close -->

<?php
include "sub_footer.php";
include "footer.php";
@mysqli_close($link);
?>
</body>
</html>