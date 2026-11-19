<?php
/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::    ::
::                                             ::
::                                             ::
::                                             ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::

:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::          Include required classes           ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/
include "captcha.class.php";
include "captcha_functions.php";
/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::   And turn the http header into image/gif   ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/
Header ( 'Content-type: image/gif' );

if ( $dh = opendir ( "font-captcha/" ) ) {
	while ( false !== ( $dat = readdir ( $dh ) ) ) {
		if ( $dat != "." && $dat != ".." ) {
			$fonts [ ] = "font-captcha/$dat";
		}
	}
	closedir ( $dh );
}
if ( $_GET [ 'uid' ] ) {
	$UID = explode ( ";", $_GET [ 'uid' ] );
    $IMG = new Captcha ( md5_decrypt ( $UID [ 1 ] ), $fonts [ rand ( 0, ( count ( $fonts ) ) - 1 ) ], "8a8a8a" );

    echo $IMG->AnimatedOut();
}
?>
