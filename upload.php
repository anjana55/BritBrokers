<?php
/*
Image Types
1 	IMAGETYPE_GIF
2 	IMAGETYPE_JPEG
3 	IMAGETYPE_PNG
4 	IMAGETYPE_SWF
5 	IMAGETYPE_PSD
6 	IMAGETYPE_BMP
7 	IMAGETYPE_TIFF_II (intel byte order)
8 	IMAGETYPE_TIFF_MM (motorola byte order)
9 	IMAGETYPE_JPC
10 	IMAGETYPE_JP2
11 	IMAGETYPE_JPX
12 	IMAGETYPE_JB2
13 	IMAGETYPE_SWC
14 	IMAGETYPE_IFF
15 	IMAGETYPE_WBMP
16 	IMAGETYPE_XBM
17 	IMAGETYPE_ICO
*/
include_once "functions.php";
include_once "image_processing.php";
$link = setConnection();
$ds = DIRECTORY_SEPARATOR;  //1
$storeFolder = "uploads";   //2
$prop_id = $_REQUEST['prop_id'];

$err=false;
$ret=false;

$max_width  = 1024;
$max_height = 768;



if (!empty($_FILES)) {
	
	$temp = explode(".",$_FILES["file"]["name"]);
	$uid = uniqid($prop_id);
	$newfilename = $uid . '.' . end($temp);
	$newthumbname = "tmb_" . $uid . '.' .end($temp);
	$stamp = "img/header_logo_t.png";
	
    	$tempFile = $_FILES['file']['tmp_name'];          //3  
	//$img_type = exif_imagetype($tempFile);
    	//$targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4
	$targetPath = $storeFolder . $ds;  //4
	$targetThumbPath = $storeFolder . "/thumbs" . $ds;
    	//$targetFile =  $targetPath. $_FILES['file']['name'];  //5
    	$targetFile =  $targetPath. $newfilename;  //5
	$targetThumbFile = $targetThumbPath. $newthumbname;  //5

		 
	if(move_uploaded_file($tempFile,$targetFile)){
		
		//Create a thumbnail image
		$thumb = new SimpleImage();
		$thumb->load($targetFile);

		$panorama = false;
		//Detect Panoramas
		$width = $thumb->getWidth();
		$height = $thumb->getHeight();

		//scaling factors
		$xRatio = $max_width / $width;        
		$yRatio = $max_height / $height;

		//calculate the new width and height
		if($width <= $max_width && $height <= $max_height)    //image does not need resizing
		{
			$toWidth     = $width;
			$toHeight     = $height;
		}
		else if($xRatio * $height < $max_height)
		{
			$toHeight = round($xRatio * $height);
			$toWidth  = $max_width;        
		}
		else
		{
			$toWidth = round($yRatio * $width);
			$toHeight  = $max_height;
		}
	
		if(intval($width/$height)>4){
			$panorama=true;
			$main_image_type = "PROPN";
			$thumb_type="PROPT";
			//No image resize here
		} else {
			$main_image_type = "PROMN";
			$thumb_type="PROTH";
			$thumb->resize($toWidth ,$toHeight); 
			$thumb->save($targetFile);
		}
		
		//Create a thumbnail image
		 $thumb = new SimpleImage();
		 $thumb->load($targetFile); 
		 $thumb->resize(196,147); 
		 $thumb->save($targetThumbFile);
		 
		$new_img = watermark($targetFile,$stamp);
		
		/*
		switch($img_type){
			case 1: //GIF
				break;
			case 2: //JPEG
				$ret1 = imagejpeg($new_img, $targetFile, 100 );
				break;
			case 3: //PNG
				break;
			case 6: //BMP
				break;
			case 7: //TIFF
			case 8: //TIFF
				break;
		}
		*/
		$ret1 = imagejpeg($new_img, $targetFile, 100 );
		$ret2 = addNewImage($link, $prop_id, "", $targetFile, $main_image_type );
		$ret3 = addNewImage($link, $prop_id, "", $targetThumbFile, $thumb_type );
		echo $ret1 && $ret2 && $ret3;
		
	} else {
		echo "File [" . $newfilename . "] could not be uploaded";
		$ret=false;
	}
	
	if(!$ret){
		$err=true;
	}
}

if($err){
	echo "Error uploading image(s)";
}
@mysqli_close($link); 
?> 