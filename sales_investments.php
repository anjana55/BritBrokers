<?php require_once __DIR__ . "/bootstrap.php"; ?>
<?php
page_head(array(
	"title" => "Find out about Buying Houses & Apartments and Investing in Sri Lanka",
	"description" => "British Brokers prides itself in offering an efficient and professional sales service, listening carefully to the requirements of prospective buyers and matching them with suitable properties. With international reach, we showcase properties to buyers all over the world.",
));
?>
<meta name="keywords" content="British Brokers prides itself in offering an efficient and professional sales service, listening carefully to the requirements of prospective buyers and matching them with suitable properties.
With international reach, we showcase properties to buyers all over the world.">
<script src="js/jssor.slider.mini.js"></script>
<script src="js/jquery.flexslider.js"></script>
<script>
    jQuery(document).ready(function ($) {
        //var options = { $AutoPlay: true, $SlideDuration: 2000, $AutoPlayInterval: 6000 };
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
<?php page_body("home"); ?>
<?php
require_once __DIR__ . "/bootstrap.php";

$link = setConnection();

include SITE_ROOT . "/inc/set_post_vars.php";
?>

    <!-- content begin -->
    <div id="container">
	<!-- slider -->
		<section id="slider" >
			<div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: 1920px; height: 672px;">
			<!-- Slides Container -->
				<div u="slides" style="cursor: move; position: absolute; overflow: hidden; left: 0px; top: 0px; width: 1920px; height: 672px;">
					<div>				
						<img src="img/sales_and_investments-lg-1.jpg" alt="">
					</div>
					<div><img src="img/sales_and_investments-lg-2.jpg" alt=""></div>
					<div><img src="img/sales_and_investments-lg-3.jpg" alt=""></div>
				</div>
			</div>
		</section>
		<!-- slider close -->

		<!-- Search Begins -->
		<section id="property-search">
			<div class="container-fluid">
			<?php
				include SITE_ROOT . "/templates/search_box.php";
			?>
			</div>
		</section>
		<!-- Search Ends -->	
		
        <div class="container">
            <div class="content">
				<div class="col-sm-12 col-md-12">
					<br>
					<h4 class="page_title">Sales and Investments</h4>
					<p>While our core business is focused on the rental market, we also undertake sales and investments in high-end real estate.</p>
				</div>
				<div class="col-sm-8 col-md-8">
					<h4>Professional Sales Service</h4>

					<p>British Brokers prides itself in offering an efficient and professional sales service, listening carefully to the requirements of prospective buyers and matching them with suitable properties.</p>
					<p>As a seller’s appointed real estate broker, we will go out of our way to find a genuine buyer, making the process as quick and painless as possible.</p>
					
					<div class="clearfix"></div>
					<h4>Finding your Dream Home</h4>

					<p>As you go through the process of purchasing a property, it is easy to forget that you’re not just buying a house, you’re buying a home. We never do.</p>
					<p>We always advise our clients not to limit themselves too much at the start of their search. Sometimes something you thought would never suit you turns out to be your dream home. </p>
					
					<div class="clearfix"></div>
					<h4>Marketing to the world</h4>

					<p>With international reach, we showcase properties to buyers all over the world.</p>
					<p>Our diverse clientele is looking to purchase property for a multitude of reasons, including retirement, investment, residence, or commercial use.</p>
					<p>Utilising innovative, user-friendly technology such as 360 degree and video tours, British Brokers leads the way in on-line real estate in Sri Lanka.</p>
					
					<div class="clearfix"></div>
					<h4>Preparing properties for sale</h4>

					<p>We view hundreds of properties every year and that has taught us what attracts buyers and sells quickly.</p>
					<p>The most important thing is to think like a buyer. Keep decoration neutral and clutter free, helping buyers to have a better understanding of the space available.</p>
					<p>British Brokers can offer advice to sellers on how to make the most of their property. This helps buyers to quickly decide if it is right for them.</p>

					<div class="clearfix"></div>
					<h4>Legal Services</h4>

					<p>Working with our legal partners, British Brokers can assist in the provision of documentation and answer any relevant questions you may have.</p>

					<div class="clearfix"></div>
					<h4>Interior-design and Furniture-making</h4>

					<p>For those buyers who want to add a touch of style to their new property, British Brokers partners with some of the best interior designers and furniture manufacturers in Sri Lanka.</p>
					<p>Offering highly competitive prices and the latest designs we can satisfy all your requirements, be it for an individual item of furniture or fully furnishing an empty property. </p>
					<p><b>British Brokers. Investing time and care into every sale.</b></p>
					<br/>
				</div>
				<div class="col-sm-4 col-md-4">	
					<div class="featured-box filled">
						<h4>Why sell or buy your property through British Brokers:</h4>

						<ul>
							<li>An excellent selection of pre-selected apartments and homes</li>
							<li>Proactive approach to marketing your property through the most effective channels</li>
							<li>Market-leading website with cutting edge technology</li>
							<li>Unparalleled knowledge of the Colombo real estate market</li>
							<li>Excellent value-added services at the best prices</li>
							<li>Experienced, friendly, trustworthy and straight-talking</li>
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
								<li>'I find British Brokers to be professional, helpful and courteous, and have recommended them to my colleagues.'<span>Michele Fonseka</span>
								</li>
								<li>'Thank you for the very prompt and efficient service. I was pleasantly surprised to have a tenant occupying my apartment within a few days of having spoken to British Brokers. The introductions and reaching mutual agreement where all parties were concerned were handled very professionally by you. I look forward to working with British Brokers in the coming years.'<span>Rita Marcelline</span>
								</li>
								<li>'My experience with British Brokers was a smooth & efficient process from the initial discussions up to the point of getting the keys to hand. They handled the negotiations with utmost professionalism and never required following up. I also realised that they had an eye for what my wife and I liked and wouldn't waste our time with unsuitable properties, a unique quality given my experience with other realtors. I would highly recommend British Brokers and have already done so with friends and family.'<span>Asoka Obeyesekere</span>
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
page_end(true, array("pid" => $pid ?? ""));
@mysqli_close($link);
?>
