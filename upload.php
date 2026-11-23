<?php
/*
 * Property photo upload (admin only), called by Dropzone on the add/edit property screens.
 *
 * Security:
 *  - admin session, POST and the session's CSRF token required
 *  - the file must decode as a real JPEG, PNG, GIF or WebP image (checked from its content, not its name)
 *  - the image is always re-encoded and saved as .jpg under a generated name,
 *    so nothing the visitor sends ends up in the file name or survives as executable content
 */
require_once __DIR__ . "/bootstrap.php";
require_once SITE_ROOT . "/inc/image_processing.php";

require_admin_ajax();
require_csrf();

$link = setConnection();
$prop_id = to_int($_REQUEST['prop_id'] ?? "", 0);

$max_width  = 1024;
$max_height = 768;
$max_bytes  = 16 * 1024 * 1024;
$allowed_types = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF);
if(defined('IMAGETYPE_WEBP')){
	$allowed_types[] = IMAGETYPE_WEBP;
}

function upload_fail($message, $code = 400){
	http_response_code($code);
	echo $message;
	exit();
}

if($prop_id <= 0 || !getPropertyByID($link, $prop_id)){
	upload_fail("Unknown property");
}

$file = $_FILES['file'] ?? null;
if(!$file || !is_array($file) || !isset($file['error']) || is_array($file['error'])){
	upload_fail("No file received");
}
if($file['error'] !== UPLOAD_ERR_OK){
	upload_fail("Upload failed (code " . (int)$file['error'] . ")");
}
if($file['size'] <= 0 || $file['size'] > $max_bytes){
	upload_fail("File is empty or larger than 16 MB");
}
if(!is_uploaded_file($file['tmp_name'])){
	upload_fail("Invalid upload");
}

$info = @getimagesize($file['tmp_name']);
if($info === false || !in_array($info[2], $allowed_types, true)){
	upload_fail("Only JPEG, PNG, GIF or WebP images are accepted");
}

$storeFolder = "uploads";
$uid = uniqid($prop_id . "_", true);
$uid = str_replace(".", "", $uid);
$newfilename  = $uid . ".jpg";
$newthumbname = "tmb_" . $uid . ".jpg";
$targetFile      = $storeFolder . "/" . $newfilename;          // stored in the DB as a relative URL
$targetThumbFile = $storeFolder . "/thumbs/" . $newthumbname;
$stamp = __DIR__ . "/img/header_logo_t.png";

// Decode from the temporary upload; the original bytes are never moved into the web root.
$img = new SimpleImage();
if(!$img->load($file['tmp_name'])){
	upload_fail("The image could not be read");
}

$width = $img->getWidth();
$height = $img->getHeight();
if($width < 1 || $height < 1){
	upload_fail("The image could not be read");
}

//scaling factors
$xRatio = $max_width / $width;
$yRatio = $max_height / $height;

//calculate the new width and height
if($width <= $max_width && $height <= $max_height){ //image does not need resizing
	$toWidth  = $width;
	$toHeight = $height;
} else if($xRatio * $height < $max_height){
	$toHeight = round($xRatio * $height);
	$toWidth  = $max_width;
} else {
	$toWidth  = round($yRatio * $width);
	$toHeight = $max_height;
}

if(intval($width/$height) > 4){
	// Panorama: keep full size
	$main_image_type = "PROPN";
	$thumb_type = "PROPT";
} else {
	$main_image_type = "PROMN";
	$thumb_type = "PROTH";
	$img->resize($toWidth, $toHeight);
}

if(!is_dir(__DIR__ . "/" . $storeFolder . "/thumbs")){
	@mkdir(__DIR__ . "/" . $storeFolder . "/thumbs", 0755, true);
}

// Main image (re-encoded as JPEG), then the thumbnail from it
$img->save(__DIR__ . "/" . $targetFile, IMAGETYPE_JPEG, 90);

$thumb = new SimpleImage();
$thumb->load(__DIR__ . "/" . $targetFile);
$thumb->resize(196, 147);
$thumb->save(__DIR__ . "/" . $targetThumbFile, IMAGETYPE_JPEG, 80);

// Watermark the main image
$ret1 = false;
if(is_readable($stamp)){
	$new_img = watermark(__DIR__ . "/" . $targetFile, $stamp);
	$ret1 = $new_img ? imagejpeg($new_img, __DIR__ . "/" . $targetFile, 90) : false;
} else {
	$ret1 = true;
}

$ret2 = addNewImage($link, $prop_id, "", $targetFile, $main_image_type);
$ret3 = addNewImage($link, $prop_id, "", $targetThumbFile, $thumb_type);

$ok = $ret1 && strpos($ret2, "success") === 0 && strpos($ret3, "success") === 0;
if(!$ok){
	upload_fail("Error uploading image(s)", 500);
}
echo "1";
@mysqli_close($link);
