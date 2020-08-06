<?php
/*****************************************************************************
 *
 * name: counter7seg.php
 *
 * This is a NagVis Gadget, it draws a 7-Segment Output with a optional measurement line in the map.
 *  Options are:
 *			fontsize= <fontsize std is 30>
 * 			maxdig= <number of maximum digits>
 *			line= <length of vertical measurement line>
 *			linedir= <direction of line, 0=down(std), 1=up
 *
 * Date: 05. Nov. 2010
 * Version 1.3
 *
 * The gadget gets its data from the NagVis frontend by parameters.
 *****************************************************************************/
/* Changelog:
 *		04.Nov.2010: 1.0 -> 1.1
 *			if no perfdata: display 0 instead of _Err
 *		04.Nov.2010: 1.1 -> 1.2
 *			argument to set if line is above or under the counter
 *		05.Nov.2010: 1.2 -> 1.3
 *			try to get state also if no Perfdata
 *****************************************************************************/

require('./gadgets_core.php');
//-----------------------------------------------------------------------------
// Configuration Variables
//-----------------------------------------------------------------------------
$width = 1000;
$height = 80;
$font='./fonts/7segment.ttf';

//-----------------------------------------------------------------------------
// Performance Variables
//-----------------------------------------------------------------------------
$value = 1337;
$state = "UNKNOWN";
$fontsize = 30;
$line = 0;
$linedir = 0;

//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
if(isset($_GET["fontsize"])) { $fontsize = $_GET["fontsize"]; }
if(isset($_GET["maxdig"])) { $maxdig = $_GET["maxdig"]; }
if(isset($_GET["line"])) { $line = $_GET["line"]; }
if(isset($_GET["linedir"])) { $linedir = $_GET["linedir"]; }

//-----------------------------------------------------------------------------
// get values from NagVis
//-----------------------------------------------------------------------------
$aOpts = Array('name1', 'name2', 'state', 'stateType', 'perfdata');
$aPerfdata = Array();
$error = "";
if(isset($_GET['perfdata']) && $_GET['perfdata'] != '') {
	$aOpts['perfdata'] = $_GET['perfdata'];
} else {
	$error = "0";
	if(isset($_GET['state'])) { $state=$_GET['state']; }
}

if($error == "") {
	$aPerfdata = parsePerfdata($aOpts['perfdata']);

	$state=$_GET['state'];

	$value=$aPerfdata[0]['value'];
	//RKOH-2011-07-21 Added round as decimal values are not displayed correctly! 
        $value=round($value);
}

//-----------------------------------------------------------------------------
// Size calculation
//-----------------------------------------------------------------------------
if(isset($maxdig)) {
	$width = $maxdig*$fontsize*0.71+5;
} else {
	if($error != "") {
		$width = strlen($error)*$fontsize*0.71+5;
	} else {
		$width = strlen($value)*$fontsize*0.71+5;
	}
}
$height = $line+$fontsize+8;

$im = ImageCreateTrueColor($width+1, $height+1);

//-----------------------------------------------------------------------------
// Color-Definition
//-----------------------------------------------------------------------------
$col_bg = ImageColorAllocate($im, 0,0,0);
$col_value = ImageColorAllocate($im, 255, 255, 255);
$col_fg = ImageColorAllocate($im, 255, 255, 255);

ImageColorTransparent($im, $col_bg);

// select color by state
if($state == 'CRITICAL') {
	$col_value = ImageColorAllocate($im, 255, 0, 0);
} elseif($state == 'WARNING') {
	$col_value = ImageColorAllocate($im, 255, 215, 0);
} elseif($state == 'UNKNOWN') {
	$col_value = ImageColorAllocate($im, 200 ,200, 200);
}

//-----------------------------------------------------------------------------
// draw Gadget
//-----------------------------------------------------------------------------
//set text to write
if(isset($maxdig)) {
	if($error != "") {
		$text = str_pad($error, $maxdig, "0", STR_PAD_LEFT);
	} else {
		$text = str_pad($value, $maxdig, "0", STR_PAD_LEFT);
	}
} else {
	if($error != "") {
		$text = $error;
	} else {
		$text = $value;
	}
}

if($linedir == 1) {
	//line goes down: counter is at top
	//draw Frame
	ImageFilledRectangle($im, 0, 0, $width, $fontsize+8, $col_fg);
	ImageFilledRectangle($im, 2, 2, $width-2, $fontsize+6, $col_bg);
	//draw text
	ImageTTFText($im, $fontsize, 0, 4, $fontsize+4, $col_value, $font, $text);
	//draw line
	ImageDashedLine($im, $width/2, $fontsize+9, $width/2, $height, $col_fg);
} else {
	//line goes up: counter is at bottom
	//draw Frame
	ImageFilledRectangle($im, 0, $line, $width, $height, $col_fg);
	ImageFilledRectangle($im, 2, $line+2, $width-2, $height-2, $col_bg);
	//draw text
	ImageTTFText($im, $fontsize, 0, 4, $height-4, $col_value, $font, $text);
	//draw line
	ImageDashedLine($im, $width/2, 0, $width/2, $line-1, $col_fg);
}


//-----------------------------------------------------------------------------
// Output Image
//-----------------------------------------------------------------------------
if(function_exists('ImageAntialias')) {
        ImageAntialias($im, true);
}

header("Content-type: image/png");
ImagePNG($im);
ImageDestroy($im);

?>

