<?php
include_once __DIR__ . "/functions.php";
start_secure_session();
$admin = is_admin();

$tsk = isset($_REQUEST['tsk']) && is_string($_REQUEST['tsk']) ? $_REQUEST['tsk'] : "";

// Tasks that change data: admin only, POST only, and with this session's CSRF token
// (so neither a link nor a form on another site can trigger them).
$write_tasks = array("adpr", "adft", "prdl", "imdl", "imft", "dlai", "edpr");
if(in_array($tsk, $write_tasks, true)){
	if(!$admin){
		http_response_code(403);
		echo "error|Not authorized!";
		exit();
	}
	require_csrf();
}

$link = setConnection();
	
if($tsk!==""){
	switch($tsk){
		case "dbfc": //Fill cities from DB
			if(isset($_REQUEST['value']) && $_REQUEST['value']!==""){
				$district = $_REQUEST['value'];
			} else {
				$district="";
			}
			
			echo fillCities($link, $district, "");
			break;
		case "dbfp": //Fill prices from DB
			if(isset($_REQUEST['src_for']) && $_REQUEST['src_for']!==""){
				$src_for = $_REQUEST['src_for'];
			} else {
				$src_for="";
			}
			
			if($src_for=="S"){
				$filter=array("type" => "SALE");
			} else if($src_for=="R"){
				$filter=array("type" => "RENT");
			} else {
				$filter=array();
			}
			
			$out = fillSelect($link, "price_ranges", "price", "price", "price", array("min_max" => "MIN") + $filter,"",true);
			$out .= "|" . fillSelect($link, "price_ranges", "price", "price", "price", array("min_max" => "MAX") + $filter,"",true);
			echo $out;
			break;
		case "adpr": //Add property
			if($admin){
				$ret = addNewProperty($link, ($_REQUEST['business_type'] ?? ""),($_REQUEST['property_type'] ?? ""), ($_REQUEST['bedrooms'] ?? ""), ($_REQUEST['bathrooms'] ?? ""), ($_REQUEST['dct'] ?? ""), ($_REQUEST['city'] ?? ""), ($_REQUEST['street'] ?? ""), ($_REQUEST['keywords'] ?? ""), ($_REQUEST['description'] ?? ""), ($_REQUEST['price'] ?? ""),($_REQUEST['currency'] ?? ""), ($_REQUEST['term'] ?? ""), ($_REQUEST['featured'] ?? ""),($_REQUEST['status'] ?? "")  );
			} else {
				$ret = "error|Not authorized!";
			}
			echo $ret;
			//echo "";
			break;
		
		case "adft": //Add features
			if($admin){
				if(isset($_REQUEST['prop_id']) && $_REQUEST['prop_id']!==""){
					if(isset($_REQUEST['features'])){
						$st="";
						$ret="";
						$err=false;
						
						foreach(to_int_list($_REQUEST['features']) as $value)
						{
							$ret = addNewFeature($link, $_REQUEST['prop_id'], $value );
							//var_dump($ret);
							$st=explode("|",$ret);
							if($st[0]=="error"){
								$err=true;
							}
						}
					
						//Add YouTube tag
						if (isset($_REQUEST['youtubetag']) && $_REQUEST['youtubetag']!=""){
							$ret = addNewImage($link, $_REQUEST['prop_id'], "", $_REQUEST['youtubetag'], "PROVD" );
							$st=explode("|",$ret);
							if($st[0]=="error"){
								$err=true;
							}							
						}
						
						if($err){
							echo "error|" . $st[1];
						} else {
							echo "success|All features successfully added";
						}
					} else {
						echo "success|No features to be added";
					}
				} else {
					echo "error|Property ID not defined!";
				}
			} else {
				echo "error|Not authorized!";
			}
			break;
		case "srpr": //Search property
			if(isset($_REQUEST['property_id']) && $_REQUEST['property_id']!=""){
				//Property ID search
				
				$results = getPropertyIDs($link,$_REQUEST['property_id']);

				if($results){
					echo "success|";
					echo "[{\"property_id\":" . $results . "}]";
				} else {
					echo "error|No results returned!";
				}
			} else {
				//Check and set default values
				$bt="";
				if(isset($_REQUEST["business_type"]) && $_REQUEST["business_type"]!=""){	//Check for business type
					$bt=$_REQUEST["business_type"];
				}
				
				$pt="";
				if(isset($_REQUEST["property_type"]) && $_REQUEST["property_type"]!=""){	//Check for property type
					$pt=$_REQUEST["property_type"];
				}

				$bdrm="";
				if(isset($_REQUEST["bedrooms"]) && $_REQUEST["bedrooms"]!=""){	//Check for bedrooms
					$bdrm=$_REQUEST["bedrooms"];
				}

				$btrm="";
				if(isset($_REQUEST["bathrooms"]) && $_REQUEST["bathrooms"]!=""){	//Check for bathrooms
					$btrm=$_REQUEST["bathrooms"];
				}

				$dct="";
				if(isset($_REQUEST["dct"]) && $_REQUEST["dct"]!=""){	//Check for district
					$dct=$_REQUEST["dct"];
				}
				
				$city="";
				if(isset($_REQUEST["city"]) && $_REQUEST["city"]!=""){	//Check for city
					$city=$_REQUEST["city"];
				}

				$older="";
				if(isset($_REQUEST["older"]) && $_REQUEST["older"]!=""){	//Check for older records
					$older=$_REQUEST["older"];
				}
				
				$featured="";
				if(isset($_REQUEST["featured"]) && $_REQUEST["featured"]!=""){	//Check for featured
					$featured=$_REQUEST["featured"];
				}
				
				$results = getPropertyIDs($link,
					'', 
					$bt,
					$pt, 
					$bdrm, 
					$bdrm, 
					$btrm, 
					$btrm, 
					'', 
					'', 
					$dct, 
					$city, 
					'', 
					'',
					$featured,
					'', 
					'', 
					'',
					$older);

				if($results){
					echo "success|";
					echo json_encode($results);
				} else {
					echo "error|No results returned!";
				}
			}
			break;
		case "prdl":	//Delete property
			if($admin){
				if(isset($_REQUEST['pid']) && $_REQUEST['pid']!=""){
					$pids = to_int_list($_REQUEST['pid']);
					
					$del_error = false;
					
					foreach($pids as $pid){
						//echo "####" . deleteProperty($link, $pid) . "###";
						if(!deleteProperty($link, $pid)){
							$del_error = true;
						}
					}
					
					if($del_error){
						echo "error|All items may not have been deleted.";
					} else {
						echo "success|All items successfully deleted.";
					}
				} else {
					echo "error|No record to be deleted!";
				}
			} else {
				echo "error|Not authorized!";
			}
			break;
		case "imdl":	//Delete images
			if($admin){
				$ret = deleteImages($link, $_REQUEST['pid'] ?? "", $_REQUEST['img_id'] ?? "");
				$st=explode("|",$ret);

				if($st[0]=="success"){
					echo "success|Image deleted successfully!";
				} else {
					echo "error|Error occurred deleting image!";
				}
			} else {
				echo "error|Not authorized!";
			}
			break;
		case "imft": //Change featured image
			if($admin){
				$img_id =0;
				$img_id = $_REQUEST['img_id'] ?? 0;
				$ret = setFeaturedImage($link, $_REQUEST['pid'] ?? "", $img_id);
				$st=explode("|",$ret);
				
				if($st[0]=="success"){
					echo "success|Image was promoted as featured successfully!";
				} else {
					echo "error|" . $st[1];
				}
			} else {
				echo "error|Not authorized!";
			}		
			break;
		case "dlai":	//Delete all images
			if($admin){
				$ret = deleteAllImages($link, $_REQUEST['pid'] ?? "");
				$st=explode("|",$ret);

				if($st[0]=="success"){
					echo "success|Images deleted successfully!" . $ret;
				} else {
					echo "error|Error occurred deleting images!" . $ret;
				}
			} else {
				echo "error|Not authorized!";
			}
			break;

		case "edpr":
			//Edit property
			if($admin){
				//$result = updateProperty($link, $pid, $bt='',$pt='', $bdrm='', $btrm='', $district='', $city='', $street='', $keywords='', $description='', $price='', $features='' );

				$result = updateProperty($link, ($_REQUEST['pid'] ?? ""), ($_REQUEST['business_type'] ?? ""),($_REQUEST['property_type'] ?? ""), ($_REQUEST['bedrooms'] ?? ""), ($_REQUEST['bathrooms'] ?? ""), ($_REQUEST['dct'] ?? ""), ($_REQUEST['city'] ?? ""), ($_REQUEST['street'] ?? ""), ($_REQUEST['keywords'] ?? ""), ($_REQUEST['description'] ?? ""), ($_REQUEST['price'] ?? ""), ($_REQUEST['currency'] ?? ""), ($_REQUEST['term'] ?? ""), ($_REQUEST['featured'] ?? ""),($_REQUEST['features'] ?? ""),($_REQUEST['status'] ?? "") );
				
				//Update YouTube tag
				if (isset($_REQUEST['youtubetag']) && $_REQUEST['youtubetag']!=""){
					$ret = updateImage($link, ($_REQUEST['pid'] ?? ""), ($_REQUEST['youtubeid'] ?? ""), ($_REQUEST['youtubetag'] ?? ""),"PROVD" );
					$st=explode("|",$ret);
					if($st[0]=="error"){
						$err=true;
					}							
				}
				
				if($result=="success"){
					echo "success|Property details updated successfully!";
				} else {
					echo "error|Error occurred updating property record!";
				}
				//var_dump($_REQUEST);
			} else {
				echo "error|Not authorized!";
			}
			break;
		case "capt":
			// Pre-check only, so the form can show an error. mailman.php checks again and uses the answer up.
			echo captcha_check($_POST['uid'] ?? "", false) ? "success" : "error";
			break;
		case "exch":  //Get exchange rate
			$cur = "USD";
			$to_cur="LKR";
			$amt = "1";
			$conv_amt=0;
			
			if ( isset ( $_POST [ 'cur' ] ) && in_array ( strtoupper ( (string) $_POST [ 'cur' ] ), array ( "USD", "LKR", "GBP" ), true ) ){
				$cur = strtoupper ( $_POST['cur'] );
				if($cur=="LKR"){
					$to_cur="USD";
				} else {
					$to_cur="LKR";
				}
			}
			
			if ( isset ( $_POST [ 'amt' ] ) && is_numeric ( $_POST [ 'amt' ] ) ){
				$amt = $_POST['amt'];
			}

			
			echo nice_number($amt) . " " . $cur . " = ";
			$conv_amt = exchange($link, $amt, $cur, $to_cur);
			if(is_numeric($conv_amt)){
				echo nice_number(exchange($link, $amt, $cur, $to_cur));
			} else {
				echo exchange($link, $amt, $cur, $to_cur);
			}
			//echo nice_number(exchange($link, $amt, $cur, "LKR"));
			echo " " . $to_cur;
			break;
		default:
			echo "error|Invalid request!";
			break;

	}
}

@mysqli_close($link); 
?>