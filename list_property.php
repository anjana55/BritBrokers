<?php require_once __DIR__ . "/bootstrap.php"; ?>
<?php
require_once __DIR__ . "/bootstrap.php";
$link = setConnection();

include SITE_ROOT . "/inc/set_post_vars.php";

$total_records = getProperties($link,$bt,$pt, $features, $min_bdrm, $max_bdrm, $min_btrm, $max_btrm, $min_price, $max_price, $keywords, $district, $city, $featured, $sort_by, $sort_order,null,null,true);

$max_records_per_page=8;
$max_pages_display=5;
$pagination=true;
$start_from = ($current_page-1) * $max_records_per_page;
//$properties = getProperties($link,'','','','','','','','','','','','','',8);
$properties = getProperties($link,$bt,$pt, $features, $min_bdrm, $max_bdrm, $min_btrm, $max_btrm, $min_price, $max_price, $keywords, $district, $city,$featured, $sort_by, $sort_order,$start_from,$max_records_per_page );

$page_name=basename($_SERVER['SCRIPT_NAME']);

$title="";
$place="";
if($t_type!=""){
	$title.= $t_type . " ";
} else {
	$title.= "Properties ";
}

if($t_for!=""){
	$title.= "for " . $t_for . " ";
} else {
	//$title.= " ";
}

if($t_district!=""){
	$title.= "at " . $t_district . " ";
	//$place = $t_district;
} else {
	//$title.= " ";
}						

if($t_city!=""){
	$title.= ", " . $t_city . " ";
	$place = $t_city;
} else {
	//$title.= " ";
}
						
page_head(array(
	"title" => "British Brokers - Property Search - " . ($place),
	"description" => ("British Brokers Search: " . $title),
));
?>

<script>
$(document).ready(function(){
	$("#srt_hp").click(function(){
		$("#sby").val('aprice');
		$("#sor").val('DESC');
		$( "#frm_search" ).submit();		
	});

	$("#srt_lp").click(function(){
		$("#sby").val('aprice');
		$("#sor").val('ASC');
		$( "#frm_search" ).submit();		
	});

	$("#srt_no").click(function(){
		$("#sby").val('featured');
		$("#sor").val('DESC');
		$( "#frm_search" ).submit();		
	});	
});

</script>
<?php page_body("home"); ?>
    <!-- subheader begin -->
    <div id="subheader">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <h1>
					<?php
					echo $title;
					?>
					</h1>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="crumb"><a href="index.php">Home</a> <span class="separator">></span> Search Results</div>
                </div>
            </div>
        </div>
    </div>
    <!-- subheader close -->
    
	<section id="property-search">
		<div class="container-fluid">
		<?php
			include SITE_ROOT . "/templates/search_box.php";
		?>
		</div>
	</section>
	
    <!-- content begin -->
    <div id="content">
        <div class="container">
            <div class="row">
                <div class="cols-xs-12 col-sm-12 col-md-9">
					<!-- Sorting button group -->
					<!--<span class="label label-default">-->
					<div class="btn-group" style="margin-bottom:5px;">
					  <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
						<span class="glyphicon glyphicon-sort" aria-hidden="true"></span>
						 Sort by price <span class="caret"></span>
					  </button>
					  <ul class="dropdown-menu" role="menu">
						<li><a id="srt_hp" href="#">Highest price first</a></li>
						<li><a id="srt_lp" href="#">Lowest price first</a></li>
						<li><a id="srt_no" href="#">No sorting by price</a></li>
					  </ul>				  
					</div>
					
					<?php
					$msg="";
					if(isset($sort_by)){
						$msg="Displaying ";
						if($sort_by=="aprice"){
							if($sort_order=="DESC"){
								$msg.=" highest ";
							} else {
								$msg.=" lowest ";
							}
							$msg.=" priced properties first";
						} else {
							$msg.=" featured properties first";
						}
					}
					//echo $msg;
					?>
					<!--<span class="badge"><?php //echo $total_records; ?> records</span>-->
					<!--</span>-->
                   <div class="">
						<?php
						//echo "Total Records:" . $total_records;
						include SITE_ROOT . "/templates/print_properties.php";
						?>
					</div>
                </div>

                <div class="col-xs-12 col-sm-12 col-md-3">
                    <div class="widget latest_news">
						<?php
						include SITE_ROOT . "/templates/latest_news.php";
						?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content close -->

    <!-- content close -->


<?php
page_end(true, array("pid" => $pid ?? ""));
@mysqli_close($link);
?>
