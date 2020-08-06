<?php
/*****************************************************************************
 *
 * name: DashedLine.php
 *
 * This is a NagVis Gadget, it draws a dashed line with a defined lentgh
 *  Options are:
 *			line= length of line
 * 			type (optional) = if no type is set, line will be horizontal
 *				= vertical -> line will be vertical
 *
 * Date: 28.04.2011
 * Version 1.1
 *
 *****************************************************************************/
/* Changelog:
 *		28.04.2011
 *			Initial Release
 *		29.04.2011
 *			Gadget can now draw vertical lines
 *****************************************************************************/

require('./gadgets_core.php');

//-----------------------------------------------------------------------------
// Performance Variables
//-----------------------------------------------------------------------------
$line = 0;

//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
// line = Line lenght
if(isset($_GET["line"])) { $line = $_GET["line"]; }
if(isset($_GET["type"])) { $type = $_GET["type"]; }

$x=$line;
$y=0;
$ximage=$line;
$yimage=1;

// Switch parameters if line should be vertical
if ($type == "vertical") {
	$x=0;
	$y=$line;
	$ximage=1;
	$yimage=$line;
}

// Create image with width = $leght and height = 2
$im = ImageCreateTrueColor($ximage, $yimage);

// set color
$col=imagecolorallocate ( $im, 255, 255, 255 );
// draw measurement line
ImageDashedLine($im, 0, 0, $x, $y, $col);


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

