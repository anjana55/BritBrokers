<?php require_once __DIR__ . "/bootstrap.php"; ?>
<?php
page_head(array(
	"title" => "British Brokers Real Estate & Lettings Agent specialise in Property across Colombo, Sri Lanka.",
	"description" => "British Brokers – The Home of Colombo Property.  Offering our clients the finest properties in the market, cutting edge technology, high profile marketing and professional credentials, British Brokers is Colombo’s leading property agent.",
	"json_ld" => business_json_ld(),
));
?>
<?php page_body("home"); ?>
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

<?php
require_once __DIR__ . "/bootstrap.php";
$link = setConnection();
include SITE_ROOT . "/inc/set_post_vars.php";
$page_name=basename($_SERVER['SCRIPT_NAME']);



?>

<!-- slider -->
	<section id="slider">
		<div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: 1920px; height: 672px;">
		<!-- Slides Container -->
			<div u="slides" style="cursor: move; position: absolute; overflow: hidden; left: 0px; top: 0px; width: 1920px; height: 672px;">
				<div><img src="img/bb-main-1.jpg" alt="British Brokers - Main Page Images"></div>
				<div><img src="img/bb-main-2.jpg" alt="British Brokers - Main Page Images"></div>
				<div><img src="img/bb-main-3.jpg" alt="British Brokers - Main Page Images"></div>
			</div>
		</div>
	</section>
<!-- slider close -->

    <section id="property-search">
		<?php
			include SITE_ROOT . "/templates/search_box.php";
		?>
	</section>
	
    <!-- content begin -->
    <div id="content">
	    
		<div class="container">		
            <hr class="blank">
            <div class="row">
                <div class="col-sm-9 col-md-9">
					<div >
					  <div >
					  <h3>British Brokers. The Home of Colombo Property</h3>
						<p>At British Brokers we love what we do and pride ourselves on having a unique perspective on the process of matching people with homes.</p>
						<p>Offering our clients the finest properties in the market, cutting edge technology, high profile marketing and professional credentials, British Brokers is Colombo’s leading property agent.</p>
						<p>Take a look at our website and see how we can fulfil your real estate needs.</p>
					  </div>
					</div>
					<br/>
                    <h3>Featured Properties</h3>
                    <div class="">
						<?php
						$pagination=false;
						$properties = getProperties($link,'','','','','','','','','','','','','','featured','DESC',0,8);
						//$properties = getProperties($link,$bt,$pt, $features, $min_bdrm, $max_bdrm, $min_btrm, $max_btrm, $min_price, $max_price, $keywords, $district, $city, $featured, $sort_by, $sort_order, start_from, max_rec, count_only );
						include SITE_ROOT . "/templates/print_properties.php";
						?>
					</div>
                </div>
				<div class="col-sm-3 col-md-3 widget">
				<?php
					include SITE_ROOT . "/templates/latest_news.php";
				?>
				</div>
            </div>
        </div>
    </div>
    <!-- content close -->
	<section id="testimonial" data-speed="6" data-type="background">
		<div class="container">
			<div class="col-lg-12 hidden-md hidden-sm hidden-xs">
				<div class="row">
					<div class="flexslider testi-slider">
						<ul class="slides">
							<li>'We have worked with Lyndon and British Brokers to find properties for colleagues from different nationalities. We have always been successful in finding an apartment within a couple of weeks. Lyndon has the ability to speak to any nationality and find just the right accommodation at the right price. The speed of finding the required apartment is remarkable. We are extremely satisfied with the service provided and would recommend their services to anyone.'<span>Panduka Adikaram - Ocean Lanka</span>
							</li>
							<li>'We have used British Brokers to find residential property for our family on two occasions…. Both times we have found them efficient, professional and friendly. They have understood our needs and found us exactly what we were looking for, within budget, in a short space of time. We would unhesitatingly recommend their services to others.'<span>Hamish MacDonald - Commercial Director - Product Creation </span>
							</li>
							<li>'Having lived abroad for 20 years in 10 countries, we have a lot of experience with agents. Lyndon was the most efficient but at the same time affable agent we've ever experienced. He took time to understand our needs, talked us through pros and cons of different localities and then showed us properties that actually did match our criteria. He was knowledgeable and informative about the local market environment without being in any way pushy and we managed to find our dream home on the first day. Lyndon subsequently helped us navigate the legal process making the whole house-hunting experience a pleasantly smooth one; certainly not always the case in previous experiences. I would (and do) thoroughly recommend British Brokers.'<span>David Maynard - Deputy Director - British Council</span> 
							</li>
							<li>'I find British Brokers to be extremely reliable and professional in their dealings. They go beyond the mere task of putting together tenants and landlords and add value in the form of the advice they offer. I feel that their understanding of the market enables me to let out my properties with ease while also maximising the return on investment.'<span>Priyantha Wijesekera - Deputy General Manager Leasing - Nations Trust Bank</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php
page_end(true, array("pid" => $pid ?? ""));
@mysqli_close($link);
?>
