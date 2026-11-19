<?php
if(isset($_REQUEST['src_type']) && $_REQUEST['src_type']!=""){
	$pt=$_REQUEST['src_type'];
	$t_type = getPropertyTypeByID($link,$pt);
} else {
	$pt="";
	$t_type="";
}

if(isset($_REQUEST['src_for']) && $_REQUEST['src_for']!=""){
	$bt=$_REQUEST['src_for'];
	$t_for = getBusinessTypeByID($link,$bt);
} else {
	$bt="R";
	$t_for = "";
}

if(isset($_REQUEST['src_bedrooms']) && $_REQUEST['src_bedrooms']!=""){
	$bdr=$_REQUEST['src_bedrooms'];
	if($bdr=="5+"){
		$min_bdrm=5;
		$max_bdrm="";
	} else {
		$min_bdrm=$bdr;
		$max_bdrm=$bdr;		
	}
} else {
	$min_bdrm="";
	$max_bdrm="";
}

if(isset($_REQUEST['src_bathrooms']) && $_REQUEST['src_bathrooms']!=""){
	$btr=$_REQUEST['src_bathrooms'];
	if($btr=="5+"){
		$min_btrm=5;
		$max_btrm="";
	} else {
		$min_btrm=$btr;
		$max_btrm=$btr;		
	}
} else {
	$min_btrm="";
	$max_btrm="";
}

if(isset($_REQUEST['src_district']) && $_REQUEST['src_district']!=""){
	$district=$_REQUEST['src_district'];
	$t_district = getDistrictByID($link,$district);
} else {
	$district="";
	$t_district="";
}

if(isset($_REQUEST['src_city']) && $_REQUEST['src_city']!=""){
	$city=$_REQUEST['src_city'];
	$t_city = getCityByID($link,$city);
} else {
	$city="";
	$t_city="";
}

if(isset($_REQUEST['pg']) && $_REQUEST['pg']!=""){
	$current_page=$_REQUEST['pg'];
} else {
	$current_page=1;
}

$features=array();
if(isset($_REQUEST['ft']) && $_REQUEST['ft']!=""){
	$features[] = unserialize($_REQUEST['ft']);
}

if(isset($_REQUEST['src_furnished']) && $_REQUEST['src_furnished']!=""){
	array_push($features,$_REQUEST['src_furnished']);
	$fur=$_REQUEST['src_furnished'];
} else {
	$fur="";
}

if(isset($_REQUEST['mnp']) && $_REQUEST['mnp']!=""){
	$min_price=$_REQUEST['mnp'];
} else {
	$min_price="";
}

if(isset($_REQUEST['mxp']) && $_REQUEST['mxp']!=""){
	$max_price=$_REQUEST['mxp'];
} else {
	$max_price="";
}

if(isset($_REQUEST['featured']) && $_REQUEST['featured']!=""){
	$featured=$_REQUEST['featured'];
} else {
	$featured="";
}

if(isset($_REQUEST['kwd']) && $_REQUEST['kwd']!=""){
	$keywords=htmlspecialchars($_REQUEST['kwd'],ENT_IGNORE);
} else {
	$keywords="";
}

if(isset($_REQUEST['sby']) && $_REQUEST['sby']!=""){
	$sort_by=$_REQUEST['sby'];
} else {
	$sort_by="featured";
}

if(isset($_REQUEST['sor']) && $_REQUEST['sor']!=""){
	$sort_order=$_REQUEST['sor'];
} else {
	$sort_order="DESC";
}
?>