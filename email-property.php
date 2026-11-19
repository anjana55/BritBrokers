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

$title = $property['property_type_name'] . " for " . $property['business_type'] . " at " . $property['city_name'] . ", " . $property['district_name'];

$url = $_SERVER['REQUEST_URI']; //returns the current URL
$parts = explode('/',$url);
$dir = $_SERVER['SERVER_NAME'];
for ($i = 0; $i < count($parts) - 1; $i++) {
 $dir .= $parts[$i] . "/";
}
$gpf = "http://" . $dir;
?>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">	
	<link rel="icon" 
      type="image/png" 
      href="<?php echo $gpf; ?>favicon.ico">

	<style>
.container {
  padding-right: 15px;
  padding-left: 15px;
  margin-right: auto;
  margin-left: auto;
}
@media (min-width: 768px) {
  .container {
    width: 750px;
  }
}
@media (min-width: 992px) {
  .container {
    width: 970px;
  }
}
@media (min-width: 1200px) {
  .container {
    width: 1170px;
  }
}
.container-fluid {
  padding-right: 15px;
  padding-left: 15px;
  margin-right: auto;
  margin-left: auto;
}
.row {
  margin-right: -15px;
  margin-left: -15px;
}
.col-xs-1, .col-sm-1, .col-md-1, .col-lg-1, .col-xs-2, .col-sm-2, .col-md-2, .col-lg-2, .col-xs-3, .col-sm-3, .col-md-3, .col-lg-3, .col-xs-4, .col-sm-4, .col-md-4, .col-lg-4, .col-xs-5, .col-sm-5, .col-md-5, .col-lg-5, .col-xs-6, .col-sm-6, .col-md-6, .col-lg-6, .col-xs-7, .col-sm-7, .col-md-7, .col-lg-7, .col-xs-8, .col-sm-8, .col-md-8, .col-lg-8, .col-xs-9, .col-sm-9, .col-md-9, .col-lg-9, .col-xs-10, .col-sm-10, .col-md-10, .col-lg-10, .col-xs-11, .col-sm-11, .col-md-11, .col-lg-11, .col-xs-12, .col-sm-12, .col-md-12, .col-lg-12 {
  position: relative;
  min-height: 1px;
  padding-right: 15px;
  padding-left: 15px;
}
.col-xs-1, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9, .col-xs-10, .col-xs-11, .col-xs-12 {
  float: left;
}
.col-xs-12 {
  width: 100%;
}
.col-xs-11 {
  width: 91.66666667%;
}
.col-xs-10 {
  width: 83.33333333%;
}
.col-xs-9 {
  width: 75%;
}
.col-xs-8 {
  width: 66.66666667%;
}
.col-xs-7 {
  width: 58.33333333%;
}
.col-xs-6 {
  width: 50%;
}
.col-xs-5 {
  width: 41.66666667%;
}
.col-xs-4 {
  width: 33.33333333%;
}
.col-xs-3 {
  width: 25%;
}
.col-xs-2 {
  width: 16.66666667%;
}
.col-xs-1 {
  width: 8.33333333%;
}
.col-xs-pull-12 {
  right: 100%;
}
.col-xs-pull-11 {
  right: 91.66666667%;
}
.col-xs-pull-10 {
  right: 83.33333333%;
}
.col-xs-pull-9 {
  right: 75%;
}
.col-xs-pull-8 {
  right: 66.66666667%;
}
.col-xs-pull-7 {
  right: 58.33333333%;
}
.col-xs-pull-6 {
  right: 50%;
}
.col-xs-pull-5 {
  right: 41.66666667%;
}
.col-xs-pull-4 {
  right: 33.33333333%;
}
.col-xs-pull-3 {
  right: 25%;
}
.col-xs-pull-2 {
  right: 16.66666667%;
}
.col-xs-pull-1 {
  right: 8.33333333%;
}
.col-xs-pull-0 {
  right: auto;
}
.col-xs-push-12 {
  left: 100%;
}
.col-xs-push-11 {
  left: 91.66666667%;
}
.col-xs-push-10 {
  left: 83.33333333%;
}
.col-xs-push-9 {
  left: 75%;
}
.col-xs-push-8 {
  left: 66.66666667%;
}
.col-xs-push-7 {
  left: 58.33333333%;
}
.col-xs-push-6 {
  left: 50%;
}
.col-xs-push-5 {
  left: 41.66666667%;
}
.col-xs-push-4 {
  left: 33.33333333%;
}
.col-xs-push-3 {
  left: 25%;
}
.col-xs-push-2 {
  left: 16.66666667%;
}
.col-xs-push-1 {
  left: 8.33333333%;
}
.col-xs-push-0 {
  left: auto;
}
.col-xs-offset-12 {
  margin-left: 100%;
}
.col-xs-offset-11 {
  margin-left: 91.66666667%;
}
.col-xs-offset-10 {
  margin-left: 83.33333333%;
}
.col-xs-offset-9 {
  margin-left: 75%;
}
.col-xs-offset-8 {
  margin-left: 66.66666667%;
}
.col-xs-offset-7 {
  margin-left: 58.33333333%;
}
.col-xs-offset-6 {
  margin-left: 50%;
}
.col-xs-offset-5 {
  margin-left: 41.66666667%;
}
.col-xs-offset-4 {
  margin-left: 33.33333333%;
}
.col-xs-offset-3 {
  margin-left: 25%;
}
.col-xs-offset-2 {
  margin-left: 16.66666667%;
}
.col-xs-offset-1 {
  margin-left: 8.33333333%;
}
.col-xs-offset-0 {
  margin-left: 0;
}
@media (min-width: 768px) {
  .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
    float: left;
  }
  .col-sm-12 {
    width: 100%;
  }
  .col-sm-11 {
    width: 91.66666667%;
  }
  .col-sm-10 {
    width: 83.33333333%;
  }
  .col-sm-9 {
    width: 75%;
  }
  .col-sm-8 {
    width: 66.66666667%;
  }
  .col-sm-7 {
    width: 58.33333333%;
  }
  .col-sm-6 {
    width: 50%;
  }
  .col-sm-5 {
    width: 41.66666667%;
  }
  .col-sm-4 {
    width: 33.33333333%;
  }
  .col-sm-3 {
    width: 25%;
  }
  .col-sm-2 {
    width: 16.66666667%;
  }
  .col-sm-1 {
    width: 8.33333333%;
  }
  .col-sm-pull-12 {
    right: 100%;
  }
  .col-sm-pull-11 {
    right: 91.66666667%;
  }
  .col-sm-pull-10 {
    right: 83.33333333%;
  }
  .col-sm-pull-9 {
    right: 75%;
  }
  .col-sm-pull-8 {
    right: 66.66666667%;
  }
  .col-sm-pull-7 {
    right: 58.33333333%;
  }
  .col-sm-pull-6 {
    right: 50%;
  }
  .col-sm-pull-5 {
    right: 41.66666667%;
  }
  .col-sm-pull-4 {
    right: 33.33333333%;
  }
  .col-sm-pull-3 {
    right: 25%;
  }
  .col-sm-pull-2 {
    right: 16.66666667%;
  }
  .col-sm-pull-1 {
    right: 8.33333333%;
  }
  .col-sm-pull-0 {
    right: auto;
  }
  .col-sm-push-12 {
    left: 100%;
  }
  .col-sm-push-11 {
    left: 91.66666667%;
  }
  .col-sm-push-10 {
    left: 83.33333333%;
  }
  .col-sm-push-9 {
    left: 75%;
  }
  .col-sm-push-8 {
    left: 66.66666667%;
  }
  .col-sm-push-7 {
    left: 58.33333333%;
  }
  .col-sm-push-6 {
    left: 50%;
  }
  .col-sm-push-5 {
    left: 41.66666667%;
  }
  .col-sm-push-4 {
    left: 33.33333333%;
  }
  .col-sm-push-3 {
    left: 25%;
  }
  .col-sm-push-2 {
    left: 16.66666667%;
  }
  .col-sm-push-1 {
    left: 8.33333333%;
  }
  .col-sm-push-0 {
    left: auto;
  }
  .col-sm-offset-12 {
    margin-left: 100%;
  }
  .col-sm-offset-11 {
    margin-left: 91.66666667%;
  }
  .col-sm-offset-10 {
    margin-left: 83.33333333%;
  }
  .col-sm-offset-9 {
    margin-left: 75%;
  }
  .col-sm-offset-8 {
    margin-left: 66.66666667%;
  }
  .col-sm-offset-7 {
    margin-left: 58.33333333%;
  }
  .col-sm-offset-6 {
    margin-left: 50%;
  }
  .col-sm-offset-5 {
    margin-left: 41.66666667%;
  }
  .col-sm-offset-4 {
    margin-left: 33.33333333%;
  }
  .col-sm-offset-3 {
    margin-left: 25%;
  }
  .col-sm-offset-2 {
    margin-left: 16.66666667%;
  }
  .col-sm-offset-1 {
    margin-left: 8.33333333%;
  }
  .col-sm-offset-0 {
    margin-left: 0;
  }
}
@media (min-width: 992px) {
  .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12 {
    float: left;
  }
  .col-md-12 {
    width: 100%;
  }
  .col-md-11 {
    width: 91.66666667%;
  }
  .col-md-10 {
    width: 83.33333333%;
  }
  .col-md-9 {
    width: 75%;
  }
  .col-md-8 {
    width: 66.66666667%;
  }
  .col-md-7 {
    width: 58.33333333%;
  }
  .col-md-6 {
    width: 50%;
  }
  .col-md-5 {
    width: 41.66666667%;
  }
  .col-md-4 {
    width: 33.33333333%;
  }
  .col-md-3 {
    width: 25%;
  }
  .col-md-2 {
    width: 16.66666667%;
  }
  .col-md-1 {
    width: 8.33333333%;
  }
  .col-md-pull-12 {
    right: 100%;
  }
  .col-md-pull-11 {
    right: 91.66666667%;
  }
  .col-md-pull-10 {
    right: 83.33333333%;
  }
  .col-md-pull-9 {
    right: 75%;
  }
  .col-md-pull-8 {
    right: 66.66666667%;
  }
  .col-md-pull-7 {
    right: 58.33333333%;
  }
  .col-md-pull-6 {
    right: 50%;
  }
  .col-md-pull-5 {
    right: 41.66666667%;
  }
  .col-md-pull-4 {
    right: 33.33333333%;
  }
  .col-md-pull-3 {
    right: 25%;
  }
  .col-md-pull-2 {
    right: 16.66666667%;
  }
  .col-md-pull-1 {
    right: 8.33333333%;
  }
  .col-md-pull-0 {
    right: auto;
  }
  .col-md-push-12 {
    left: 100%;
  }
  .col-md-push-11 {
    left: 91.66666667%;
  }
  .col-md-push-10 {
    left: 83.33333333%;
  }
  .col-md-push-9 {
    left: 75%;
  }
  .col-md-push-8 {
    left: 66.66666667%;
  }
  .col-md-push-7 {
    left: 58.33333333%;
  }
  .col-md-push-6 {
    left: 50%;
  }
  .col-md-push-5 {
    left: 41.66666667%;
  }
  .col-md-push-4 {
    left: 33.33333333%;
  }
  .col-md-push-3 {
    left: 25%;
  }
  .col-md-push-2 {
    left: 16.66666667%;
  }
  .col-md-push-1 {
    left: 8.33333333%;
  }
  .col-md-push-0 {
    left: auto;
  }
  .col-md-offset-12 {
    margin-left: 100%;
  }
  .col-md-offset-11 {
    margin-left: 91.66666667%;
  }
  .col-md-offset-10 {
    margin-left: 83.33333333%;
  }
  .col-md-offset-9 {
    margin-left: 75%;
  }
  .col-md-offset-8 {
    margin-left: 66.66666667%;
  }
  .col-md-offset-7 {
    margin-left: 58.33333333%;
  }
  .col-md-offset-6 {
    margin-left: 50%;
  }
  .col-md-offset-5 {
    margin-left: 41.66666667%;
  }
  .col-md-offset-4 {
    margin-left: 33.33333333%;
  }
  .col-md-offset-3 {
    margin-left: 25%;
  }
  .col-md-offset-2 {
    margin-left: 16.66666667%;
  }
  .col-md-offset-1 {
    margin-left: 8.33333333%;
  }
  .col-md-offset-0 {
    margin-left: 0;
  }
}
@media (min-width: 1200px) {
  .col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-lg-10, .col-lg-11, .col-lg-12 {
    float: left;
  }
  .col-lg-12 {
    width: 100%;
  }
  .col-lg-11 {
    width: 91.66666667%;
  }
  .col-lg-10 {
    width: 83.33333333%;
  }
  .col-lg-9 {
    width: 75%;
  }
  .col-lg-8 {
    width: 66.66666667%;
  }
  .col-lg-7 {
    width: 58.33333333%;
  }
  .col-lg-6 {
    width: 50%;
  }
  .col-lg-5 {
    width: 41.66666667%;
  }
  .col-lg-4 {
    width: 33.33333333%;
  }
  .col-lg-3 {
    width: 25%;
  }
  .col-lg-2 {
    width: 16.66666667%;
  }
  .col-lg-1 {
    width: 8.33333333%;
  }
  .col-lg-pull-12 {
    right: 100%;
  }
  .col-lg-pull-11 {
    right: 91.66666667%;
  }
  .col-lg-pull-10 {
    right: 83.33333333%;
  }
  .col-lg-pull-9 {
    right: 75%;
  }
  .col-lg-pull-8 {
    right: 66.66666667%;
  }
  .col-lg-pull-7 {
    right: 58.33333333%;
  }
  .col-lg-pull-6 {
    right: 50%;
  }
  .col-lg-pull-5 {
    right: 41.66666667%;
  }
  .col-lg-pull-4 {
    right: 33.33333333%;
  }
  .col-lg-pull-3 {
    right: 25%;
  }
  .col-lg-pull-2 {
    right: 16.66666667%;
  }
  .col-lg-pull-1 {
    right: 8.33333333%;
  }
  .col-lg-pull-0 {
    right: auto;
  }
  .col-lg-push-12 {
    left: 100%;
  }
  .col-lg-push-11 {
    left: 91.66666667%;
  }
  .col-lg-push-10 {
    left: 83.33333333%;
  }
  .col-lg-push-9 {
    left: 75%;
  }
  .col-lg-push-8 {
    left: 66.66666667%;
  }
  .col-lg-push-7 {
    left: 58.33333333%;
  }
  .col-lg-push-6 {
    left: 50%;
  }
  .col-lg-push-5 {
    left: 41.66666667%;
  }
  .col-lg-push-4 {
    left: 33.33333333%;
  }
  .col-lg-push-3 {
    left: 25%;
  }
  .col-lg-push-2 {
    left: 16.66666667%;
  }
  .col-lg-push-1 {
    left: 8.33333333%;
  }
  .col-lg-push-0 {
    left: auto;
  }
  .col-lg-offset-12 {
    margin-left: 100%;
  }
  .col-lg-offset-11 {
    margin-left: 91.66666667%;
  }
  .col-lg-offset-10 {
    margin-left: 83.33333333%;
  }
  .col-lg-offset-9 {
    margin-left: 75%;
  }
  .col-lg-offset-8 {
    margin-left: 66.66666667%;
  }
  .col-lg-offset-7 {
    margin-left: 58.33333333%;
  }
  .col-lg-offset-6 {
    margin-left: 50%;
  }
  .col-lg-offset-5 {
    margin-left: 41.66666667%;
  }
  .col-lg-offset-4 {
    margin-left: 33.33333333%;
  }
  .col-lg-offset-3 {
    margin-left: 25%;
  }
  .col-lg-offset-2 {
    margin-left: 16.66666667%;
  }
  .col-lg-offset-1 {
    margin-left: 8.33333333%;
  }
  .col-lg-offset-0 {
    margin-left: 0;
  }
}	
	/* Sticky footer styles
	-------------------------------------------------- */
	html {
	  position: relative;
	  min-height: 100%;
	}
	body {
	  /* Margin bottom by footer height */
	  margin-bottom: 60px;
	}
	.footer {
	  position: absolute;
	  bottom: 0;
	  width: 100%;
	  /* Set the fixed height of the footer here */
	  height: 60px;
	  background-color: #f5f5f5;
	}
	* {
	  -webkit-box-sizing: border-box;
		 -moz-box-sizing: border-box;
			  box-sizing: border-box;
	}
	*:before,
	*:after {
	  -webkit-box-sizing: border-box;
		 -moz-box-sizing: border-box;
			  box-sizing: border-box;
	}
	html {
	  font-size: 10px;

	  -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
	}
	body {
	  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
	  font-size: 14px;
	  line-height: 1.42857143;
	  color: #333;
	  background-color: #fff;
	}
	input,
	button,
	select,
	textarea {
	  font-family: inherit;
	  font-size: inherit;
	  line-height: inherit;
	}
	a {
	  color: #337ab7;
	  text-decoration: none;
	}
	a:hover,
	a:focus {
	  color: #23527c;
	  text-decoration: underline;
	}
	a:focus {
	  outline: thin dotted;
	  outline: 5px auto -webkit-focus-ring-color;
	  outline-offset: -2px;
	}
	figure {
	  margin: 0;
	}
	img {
	  vertical-align: middle;
	}
	.img-responsive,
	.thumbnail > img,
	.thumbnail a > img,
	.carousel-inner > .item > img,
	.carousel-inner > .item > a > img {
	  display: block;
	  max-width: 100%;
	  height: auto;
	}
	.img-rounded {
	  border-radius: 6px;
	}
	.img-thumbnail {
	  display: inline-block;
	  max-width: 100%;
	  height: auto;
	  padding: 4px;
	  line-height: 1.42857143;
	  background-color: #fff;
	  border: 1px solid #ddd;
	  border-radius: 4px;
	  -webkit-transition: all .2s ease-in-out;
		   -o-transition: all .2s ease-in-out;
			  transition: all .2s ease-in-out;
	}
	/*- - - - - - - - - - - - - - - - - - - - 
		PROPERTY ITEM
	- - - - - - - - - - - - - - - - - - - - */

	.property-item .info-1{
		position:relative;
		z-index:100;
	}

	.property-item .contents{
		height: 150px;
		font-size: 12px;
	}

	.property-item .price{
		background:#A84525;
		color:#fff;
		padding:2px 10px 2px 10px;
		font-weight:bold;
		font-size:15px;
		float:left;
		position: absolute;
	}

	.property-item .name{
		padding:3px;
		font-weight:bold;
		font-size:12px;
		float:left;
		width:100%;
	}

	.property-item .contact{
		padding:3px;
		font-weight:bold;
		font-size:12px;
		width:100%;
	}

	.for-sale {
		background:#A84525;
		color:#fff;
		padding:2px 10px 2px 10px;
		font-weight:bold;
		font-size:15px;
	}

	 .for-rent{
		background:#721f1f;
		color:#fff;
		padding:2px 10px 2px 10px;
		font-weight:bold;
		font-size:15px;
	}

	.property-item .for-rent{
		background: rgb(72, 31, 31); /* Fall-back for browsers that don't
										support rgba */
		background: rgba(72, 31, 31, .6);
		font-size: 9px;
		line-height: 10px;
		float:left;
		position: absolute;		
	}

	.property-item .for-sale{
		background: rgb(168, 69, 37); /* Fall-back for browsers that don't
										support rgba */
		background: rgba(168, 69, 37, .4);
		font-size: 9px;
		line-height: 10px;
		float:left;
		position: absolute;		
	}

	.property-item img{
		margin-top:-0.1px;
	}

	.property-item{
		margin-bottom:30px;
		z-index: 5;
	}
	.property-item .price{
		background:#eee;
		color:#333;
		float:right;
		font-weight:normal;
		width: 100%;
		position: relative;
		text-align: right;
	}

	.property-item .featured{
		right:0;
		position: absolute;
		text-align: right;
		background:#FF3300;
		color:#fff;
		padding:2px 10px 2px 10px;
		font-weight:bold;
		font-size:15px;	
	}

	.property-item .reference{
		right:0;
		position: absolute;
		text-align: right;
		background:#FFFFFF;
		color:#333;
		padding:2px 10px 2px 10px;
		font-weight:bold;
		font-size:15px;	
	}

	.property-item .info{
		padding:10px;
		background: rgb(255,255,255); /* Old browsers */
	background: -moz-linear-gradient(top,  rgba(255,255,255,1) 0%, rgba(244,244,244,1) 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(100%,rgba(244,244,244,1))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(244,244,244,1) 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(244,244,244,1) 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(244,244,244,1) 100%); /* IE10+ */
	background: linear-gradient(to bottom,  rgba(255,255,255,1) 0%,rgba(244,244,244,1) 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#f4f4f4',GradientType=0 ); /* IE6-9 */
	}

	.property-item h4{
		margin:0;
	}

	.property-item .features{
		/*
		border-top:solid 1px #ddd;
		padding-top:10px;
		margin-top:10px;
		vertical-align: bottom;
		*/
	}

	.property-item .features span{
		padding-left:20px;
	}

	.property-item .features .bed{
		background:url(<?php echo $gpf; ?>img/icon-bed.png) left center no-repeat;
		float:right;
	}

	.property-item .features .bath{
		background:url(<?php echo $gpf; ?>img/icon-bath.png) left center no-repeat;
		float:right;
		margin-left:20px;
	}

	.property-item .features .area{
		background:url(<?php echo $gpf; ?>img/icon-home.png) left center no-repeat;
	}

	.property-item .features .RESHO{
		background:url(<?php echo $gpf; ?>img/icon-home.png) left center no-repeat;
		font-size: 10px;
	}

	.property-item .features .RESAN{
		background:url(<?php echo $gpf; ?>img/icon-annexe.png) left center no-repeat;
		font-size: 10px;
	}

	.property-item .features .RESAP{
		background:url(<?php echo $gpf; ?>img/icon-apartment.png) left center no-repeat;
		font-size: 10px;
	}

	.property-item .features .COMRT{
		background:url(<?php echo $gpf; ?>img/icon-retail.png) left center no-repeat;
		font-size: 10px;
	}

	.property-item .features .COMOF{
		background:url(<?php echo $gpf; ?>img/icon-office.png) left center no-repeat;
		font-size: 10px;
	}

	.property-for-sale .info{
		border:solid 1px #ddd;
		border-bottom:solid 2px #721f1f;
	}

	.property-for-rent .info{
		border:solid 1px #ddd;
		border-bottom:solid 2px #A84525;
	}
	.feature-list {
		margin: 20px 0 0;
		padding: 0;
	}
	.feature-list li {
		border-bottom: 1px solid #ddd;
		list-style: outside none none;
		padding: 3px 0;
	}
	.feature-list li::before {
		color: #ccc;
		content: "";
		font-family: FontAwesome;
		font-size: 14px;
		line-height: 0.1em;
		list-style: outside none none;
		margin-right: 10px;
	}	
	</style>	
<title>British Brokers - <?php echo $title; ?></title>
<meta name="description" content="<?php echo "British Brokers Properties: " . $title; ?>">
<meta name="keywords" content="<?php echo "British Brokers Properties: " . $title; ?>">


</head>
<body id="home">
<!-- Fixed navbar -->
<nav id="navigation" class="navbar navbar-default navbar-fixed-top" style="padding:0 25px;box-shadow: 0 5px 0 rgba(255, 255, 255, 0.15) inset, 0 5px 5px rgba(0, 0, 0, 0.075); margin-bottom: 0px; ">
	<div class="container">
		<div class="navbar-header" style="height:80px;">
		  <a class="navbar-brand" href="http://www.britbrokers.com" style="padding: 10px; margin-left: 20px;"><img src="<?php echo $gpf; ?>img/header_logo.png"></a>
		</div>
	</div>
</nav>
<div id="nav_back" class="row" style="display:block; position: relative; height: 80px;"></div>
    <!-- subheader begin -->
    <div id="subheader">
        <div class="container">
            <div class="row">
				<h1><?php echo $property['property_type_name'] . " for " . $property['business_type'] . " at " . $property['city_name'] . ", " . $property['district_name']; ?></h1>
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
							<table border="0">
							<?php
							$images = getPropertyImages($link,$pid, 25,"PROMN");
							if($images){
							$x=0;
								foreach($images as $image){
									if($x==0){
										echo "<tr>\n";
									}

									echo "<td>\n";
										echo "<a id='img_" . $image['img_id'] . "' href='http://www.britbrokers.com/single-property.php?pid=$pid'>";
										echo "<img class='img-thumbnail img-responsive' src='" . $gpf . $image['img_url'] . "' width='200px' alt=''/>\n";
										echo "</a>\n";
									echo "</td>\n";

									$x++;
									if($x>=3){
										echo "</tr>\n";
										$x=0;
									}
								}
							} else {
								//echo "<div>";
								//echo "<img src='" . $gpf . "img/property/pic (1).jpg' alt='No property images found!'>";
								//echo "</div>";										
							}														
							$tab=1;
							?>
							</table>
						</div>
                        <div class="col-sm-3 col-md-3">
						<?php
						if($property['business_type']=='Sale'){
							$pclass= 'for-sale';
						} else if ($property['business_type']=='Rent'){
							$pclass = 'for-rent';
						}						
						?>
                            <span class="<?php echo $pclass; ?>"><?php echo $property['business_type']; ?></span>
							<span>Reference #: <?php echo $pid; ?></span>
                            <h3 class="no-btm"><?php echo $property['district_name']; ?></h3>
                            <?php echo $property['city_name']; ?>
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
							?>
						  </span>

                            <ul class="feature-list">
                                <li><?php echo $property['bedrooms']; ?> (Bed)Room(s)</li>
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

                    <div class="col-sm-12">
						<h3>Information</h3>

                        <div class="lt_tab_content">

                            <div id="tab1" class="lt_content">
								<div class="container">
									<div class="col-md-3 col-lg-3">
									<?php
										echo getQRCode(curPageURL(),"M");
									?>
									</div>
									<div class="col-md-9 col-lg-9">
									<?php
										echo $property['description'];
									?>
									</div>
								</div>
                            </div>
                        </div>
                    </div>
                </div>
            <hr class="blank">
			<h1>Visit <a href="http://www.britbrokers.com">britbrokers.com</a>for more information</h1>
        </div>
    </div>
    <!-- content close -->	
	<!-- footer begin -->
    <footer class="footer">
		<div class="container">
			<div class="row">
				<div class="col-md-12 text-center">
					<span class="footer-text">&copy; Copyright 2015 - British Brokers, Sri Lanka.&nbsp;|&nbsp;<a href="<?php echo $gpf; ?>privacy.php">Privacy Policy</a>&nbsp;|&nbsp;<a href="<?php echo $gpf; ?>terms.php">Terms and Conditions</a></span>
				</div>
			</div>
		</div>
    </footer>
<?php
@mysqli_close($link);
?>
</body>
</html>
