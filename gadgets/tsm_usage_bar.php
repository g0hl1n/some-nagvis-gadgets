<?php
/*****************************************************************************
 *
 * name: tsm_usage_bar.php
 *
 * This is a NagVis Gadget, it draws a Filesystem-Usage-Bar in the map.
 * The Bar gets orange/red, when the state gets warning/critical
 * It displays the warning/critical ranges, the current usage in Percent,
 * the ilesystemsize and the Filesystemname.
 * If the nagios-Check reports it, the Inode-Usage is shown by a small bar.
 *
 * Following Parameters you can add in the Map-CFG:
 * scale=xxx	... Scale the Gadget (Value between 0 and 1)
 * xscale=xxx	... Scale the X-Axis of the Gadget (Value between 0 and 1)
 * yscale=xxx	... Scale the Y-Axis of the Gadget (Value between 0 and 1)
 * title=xxx	... Set the title (name of FS) manually

 * Date: 29. Oct. 2010
 * Version 1.0
 *
 * The gadget gets its data from the NagVis frontend by parameters.
 *****************************************************************************/
/* Changelog:
 *****************************************************************************/

require('./gadgets_core.php');
//-----------------------------------------------------------------------------
// Configuration Variables
//-----------------------------------------------------------------------------
$width = 1000;
$height = 80;
$font='./fonts/DejaVuSans-Bold.ttf';

//-----------------------------------------------------------------------------
// Performance Variables
//-----------------------------------------------------------------------------
$used = 0.96;
$inode_value=0.4;
$main_value=$used*100;
$library='/device';
$warning=0.9;
$available=0.95;

//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
$scale = $_GET["scale"];
$xscale = $_GET["xscale"];
$yscale = $_GET["yscale"];
$text = $_GET["title"];
$lib = $_GET["library"];

//-----------------------------------------------------------------------------
// get values from NagVis
//-----------------------------------------------------------------------------

$aOpts = Array('name1', 'name2', 'state', 'stateType', 'perfdata');
$aPerfdata = Array();
$error = "";
if(isset($_GET['perfdata']) && $_GET['perfdata'] != '') {
	$aOpts['perfdata'] = $_GET['perfdata'];
} elseif($_GET['conf'] == "1") {
	$aOpts['perfdata'] = "/TEST=137;;1337;;2000";
} else {
	$error = "not available";
}
if($error == "") {
	$aPerfdata = parsePerfdata($aOpts['perfdata']);

	if(isset($lib)) {
			for($i=0;isset($aPerfdata[$i]['label']);$i++) {
				if($aPerfdata[$i]['label'] == $lib) {
					$ID=$i;
					break;
				}
			}
	} else {
		$ID=0;
	}
	if(!isset($ID)) {
		errorBox("Unknown Library");
	}
	if(isset($text)) {
		$library=$text;
	} else {
		$library=$aPerfdata[0]['label'];
	}

	$used=$aPerfdata[0]['value'];
	$available=$aPerfdata[0]['critical'];
	$max=$aPerfdata[0]['max'];

	$state=$_GET['state'];

	$used = $used / $max + 0.01;
	$available = $available / $max;
}
//-----------------------------------------------------------------------------
// Size calculation
//-----------------------------------------------------------------------------
if($scale != 0) {
        $width = $width*$scale;
        $height = $height*$scale;
}
if($xscale != 0) {
        $width = $width*$xscale;
}
if($yscale != 0) {
        $height = $height*$yscale;
}

$im = ImageCreateTrueColor($width+1, $height+1);

//-----------------------------------------------------------------------------
// Color-Definition
//-----------------------------------------------------------------------------
$col_bg = ImageColorAllocate($im, 255, 255, 255);
$col_border = ImageColorAllocate($im, 0, 0, 0);
$col_used = ImageColorAllocate($im, 150, 150, 150);
$col_avail = ImageColorAllocate($im, 0, 255, 0);
$col_text = ImageColorAllocate($im, 0, 0, 0);

//-----------------------------------------------------------------------------
// draw Gadget
//-----------------------------------------------------------------------------
//draw Frame
ImageFillToBorder($im, 0, 0, $col_bg, $col_bg);
ImageRectangle($im, 0, 0, $width, $height, $col_border);
ImageRectangle($im, 1, 1, $width-1, $height-1, $col_border);

if($error == "") {
	//draw available value
	ImageFilledRectangle($im, 2, $height/6, $width*$available, $height-$height/6, $col_avail);
	ImageRectangle($im, 2, $height/6, $width*$available, $height-$height/6, $col_border);
	//draw available value
	ImageFilledRectangle($im, 2, $height/6, $width*$used, $height-$height/6, $col_used);
	ImageRectangle($im, 2, $height/6, $width*$used, $height-$height/6, $col_border);

	//draw text (percentage of usage and size)
	$used=$aPerfdata[0]['value'];
	$available=$aPerfdata[0]['critical'];
	$max=$aPerfdata[0]['max'];
	
	$text = "$library $used/$available/$max";
}
if($error != "") {
	ImageTTFText($im, 2*$height/5, 0, $width/100, 3.5*$height/5, $col_text, $font, "$library $error");
} else {
	ImageTTFText($im, 2*$height/5, 0, $width/100, 3.5*$height/5, $col_text, $font, $text);
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

