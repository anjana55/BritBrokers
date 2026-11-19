<?php
/*
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	Captcha Version 2.0 by László Zsidi, http://gifs.hu
::
::	This class is a rewritten 'Captcha.class.php' version.
::
::  Modification:
::   - Simplified and easy code,
::   - Stable working
::
::
::	Created at 2007. 02. 01. '07.47.AM'
::
*/

include_once __DIR__ . "/GIFEncoder.class.php";

if ( !defined ( "ANIM_FRAMES" ) ) define ( "ANIM_FRAMES",  5 );
if ( !defined ( "ANIM_DELAYS" ) ) define ( "ANIM_DELAYS", 10 );

Class Captcha {
	public $image;

	public function __construct ( $text, $font, $color ) {
		$C              = HexDec ( $color );
		$R              = (int) floor ( $C / pow ( 256, 2 ) );
		$G              = (int) floor ( ( $C % pow ( 256, 2 ) ) / pow ( 256, 1 ) );
		$B              = (int) floor ( ( ( $C % pow ( 256, 2 ) ) % pow ( 256, 1 ) ) / pow ( 256, 0 ) );
		$fsize          = 32;
		$bound          = array ( );
		$bound          = imageTTFBbox ( $fsize, 0, $font, $text );
		$this->image    = imageCreateTrueColor ( (int) ( $bound [ 4 ] + 5 ), (int) ( abs ( $bound [ 5 ] ) + 15 ) );
		imageFill       ( $this->image, 0, 0, ImageColorAllocate ( $this->image, 255, 255, 255 ) );
		imagettftext    ( $this->image, $fsize, 0, 2, (int) ( abs ( $bound [ 5 ] ) + 5 ), ImageColorAllocate ( $this->image, $R, $G, $B ), $font, $text );
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::::::::
	::
	::	DoNoise...
	::
	*/
	public static function DoNoise ( $image, $G0, $C0 ) {
		$W = imageSX ( $image );
		$H = imageSY ( $image );

		for ( $i = 0; $i < 768; $i++ ) {
			$arrLUT [ $i ] = $i < 512 ? ( $i < 255 ? 0 : ( $i - 256 ) ) : 255;
		}

		$G1 = $G0 / 2;
		$C1 = $C0 / 2;
		for ( $y = 0; $y < $H; $y++ ) {
			for ( $x = 0; $x < $W; $x++ ) {
				$P  = imageColorAt ( $image, $x, $y );
				$R  = ( $P >> 16 ) & 0xFF;
				$G  = ( $P >>  8 ) & 0xFF;
				$B  = ( $P >>  0 ) & 0xFF;
				$N  = (int) ( mt_rand ( 0, $G0 ) - $G1 );
				$R += 255 + $N + (int) ( mt_rand ( 0, $C0 ) - $C1 );
				$G += 255 + $N + (int) ( mt_rand ( 0, $C0 ) - $C1 );
				$B += 255 + $N + (int) ( mt_rand ( 0, $C0 ) - $C1 );
				imageSetPixel ( $image, $x, $y, ( $arrLUT [ $R ] << 16 ) | ( $arrLUT [ $G ] << 8 ) | $arrLUT [ $B ] );
			}
		}
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::::::::
	::
	::	AnimatedOut...
	::
	*/
	public function AnimatedOut ( ) {
		$f_arr = array ( );
		$d_arr = array ( );

		for ( $i = 0; $i < ANIM_FRAMES; $i++ ) {
			$image = imageCreateTrueColor ( imageSX ( $this->image ), imageSY ( $this->image ) );

			if ( imageCopy ( $image, $this->image, 0, 0, 0, 0, imageSX ( $this->image ), imageSY ( $this->image ) ) ) {
				Captcha::DoNoise ( $image, 200, 0 );

				Ob_Start		(			);
				imageGif		( $image	);

				$f_arr [ ] = Ob_Get_Contents ( );
				$d_arr [ ] = ANIM_DELAYS;

				Ob_End_Clean	(			);
			}
		}
		$GIF = new GIFEncoder ( $f_arr, $d_arr, 0, 2, -1, -1, -1, "bin" );
		return ( $GIF->GetAnimation ( ) );
	}
}
?>
