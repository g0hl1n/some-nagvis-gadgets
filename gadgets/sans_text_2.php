<?php
/*****************************************************************************
 *
 * name: sans_text.php
 *
 * This is a NagVis Gadget, it draws a text.
 *
 * Following Parameters you can add in the Map-CFG:
 * text=xxx		... text which will be shown beneath the icon (OPTIONAL def=service_description)
 * fontsize=xxx	... fontsize of the text (OPTIONAL def=12)
 * width=xxx    ... width of the image
 * height=xxx   ... height of the image (OPTIONAL def=fontsize+4)
 * recolor=[0|1] .. if 1 set color to state (OPTIONAL def=0)
 * style=[n|b|i] .. set style: [n]ormal [b]old [i]talic
 *
 * Date: 05. Nov. 2010
 * Version 1.1
 *
 *****************************************************************************/
/* Changelog:
 *		05.Nov.2010: 1.0 -> 1.1
 *			added style selection (bold/italic)
 *****************************************************************************/

//-----------------------------------------------------------------------------
// Configuration Variables
//-----------------------------------------------------------------------------
$fontsize = 12;
$recolor=0;
$style="n";
$rot=0;
//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
if(isset($_GET["fontsize"])) { $fontsize = $_GET["fontsize"]; }
if(isset($_GET["perfdata"])) { $text = $_GET["perfdata"]; }
if(isset($_GET["text"])) { $text = $_GET["text"]; }
if(isset($_GET["rot"])) { $rot = $_GET["rot"]; }
if(isset($_GET["recolor"])) { $recolor = $_GET["recolor"]; }
$width=$_GET["width"];
$height=$fontsize+8;
if(isset($_GET["height"])) { $height = $_GET["height"]; }
if(isset($_GET["style"])) { $style = $_GET["style"]; }

//select font
if($style == "b") {
	$font='./fonts/DejaVuSans-Bold.ttf';
} elseif($style == "i") {
	$font='./fonts/DejaVuSans-Oblique.ttf';
} else {
	$font='./fonts/DejaVuSans.ttf';
}
//-----------------------------------------------------------------------------
// Size calculation
//-----------------------------------------------------------------------------

if($rot==90) {
$width=$height;
$height=$_GET["width"];
}
$im = ImageCreateTrueColor($width, $height);

//-----------------------------------------------------------------------------
// Color-Definition
//-----------------------------------------------------------------------------
$col_text = ImageColorAllocate($im, 255, 255, 255);
$col_bg = ImageColorAllocate($im, 0, 0, 0);
ImageColorTransparent($im, $col_bg);

if($recolor == 1) {
	if($state == 'CRITICAL') {
		$col_value = ImageColorAllocate($im, 255, 0, 0);
	} elseif($state == 'WARNING') {
		$col_value = ImageColorAllocate($im, 255, 215, 0);
	}
}

//-----------------------------------------------------------------------------
// draw Gadget
//-----------------------------------------------------------------------------
ImageFill($im, 0, 0, $col_bg);
if($rot==90) {
ImageTTFText($im, $fontsize, $rot, $fontsize, $height, $col_text, $font, $text);
} else {
ImageTTFText($im, $fontsize, 0, 0, ($height/2+$fontsize/2+0.5), $col_text, $font, $text);
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

