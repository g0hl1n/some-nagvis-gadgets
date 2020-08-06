<?php
/*****************************************************************************
 *
 * name: fs_usage_bar.php
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

 * Date: 21. Oct. 2010
 * Version 1.4
 *
 * The gadget gets its data from the NagVis frontend by parameters.
 *****************************************************************************/
/* Changelog:
 *		21.10.2010: 1.0 -> 1.1
 *			also display filesystemsize
 *		21.10.2010: 1.1 -> 1.2
 *			calc UOM into displayed max value
 *		25.10.2010: 1.2 -> 1.3
 *			no errorbox if no perfdata
 *		29.10.2010: 1.3 -> 1.4
 *			red background if no perfdata
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
$value = 0.96;
$inode_value=0.4;
$main_value=$value*100;
$filesystem='/device';
$warning=0.9;
$critical=0.95;

//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
$scale = $_GET["scale"];
$xscale = $_GET["xscale"];
$yscale = $_GET["yscale"];
$text = $_GET["title"];

//-----------------------------------------------------------------------------
// get values from NagVis
//-----------------------------------------------------------------------------

$aOpts = Array('name1', 'name2', 'state', 'stateType', 'perfdata');
$aPerfdata = Array();
$error = "";
if(isset($_GET['perfdata']) && $_GET['perfdata'] != '') {
	$aOpts['perfdata'] = $_GET['perfdata'];
} elseif($_GET['conf'] == "1") {
	$aOpts['perfdata'] = "/=4850MB;5848;6579;0;7311 /-inodes=17%;80;90;";
} else {
	$error = "not responding";
}
if($error == "") {
	$aPerfdata = parsePerfdata($aOpts['perfdata']);

	if(isset($text)) {
		$filesystem=$text;
	} else {
		$filesystem=$aPerfdata[0]['label'];
	}

	$value=$aPerfdata[0]['value'];
	$warning=$aPerfdata[0]['warning'];
	$critical=$aPerfdata[0]['critical'];
	$max=$aPerfdata[0]['max'];

	if(isset($aPerfdata[1]['value']) && $aPerfdata[1]['value'] < 100) {
		$inode_value=$aPerfdata[1]['value'];
	} else {
		$inode_value=100;
	}

	if(! $aPerfdata[0]['max']) {
		$max=100;
	}

	$state=$_GET['state'];

	$value = $value / $max + 0.01;
	$warning = $warning / $max;
	$critical = $critical / $max;
	$main_value = sprintf("%d", $value*100);
	$inode_value = 1 - $inode_value / 100;

	
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
$col_value = ImageColorAllocateAlpha($im, 100, 100, 100, 40);
$col_critical = ImageColorAllocateAlpha($im, 255, 0, 0, 70);
$col_warning = ImageColorAllocateAlpha($im, 255, 215, 0, 70);
$col_inodes = ImageColorAllocateAlpha($im, 100, 100, 100, 0);
$col_text = ImageColorAllocate($im, 0, 0, 0);

if($state == 'CRITICAL') {
	$col_value = ImageColorAllocate($im, 255, 0, 0);
} elseif($state == 'WARNING') {
	$col_value = ImageColorAllocate($im, 255, 215, 0);
}

//-----------------------------------------------------------------------------
// draw Gadget
//-----------------------------------------------------------------------------
//draw Frame
ImageFillToBorder($im, 0, 0, $col_bg, $col_bg);
ImageRectangle($im, 0, 0, $width, $height, $col_border);
ImageRectangle($im, 1, 1, $width-1, $height-1, $col_border);
//draw Warning/Critical area
if($error == "") {
	ImageFilledRectangle($im, $width*$warning, 2, $width*$critical-1, $height-2, $col_warning);
	ImageFilledRectangle($im, $width*$critical, 2, $width-2, $height-2, $col_critical);

	//draw usage value
	ImageFilledRectangle($im, 2, $height/6, $width*$value, $height-$height/6, $col_value);
	ImageRectangle($im, 2, $height/6, $width*$value, $height-$height/6, $col_border);
	//draw inode value
	if($inode_value > 0) {
		ImageFilledRectangle($im, 2, $height-$height/10, $width*$inode_value, $height-$height/20, $col_inodes);
	}

	//get filesystemsize and recalc it to *B
	//$max=$aPerfdata[0]['max'];
	
	if(isset($aPerfdata[0]['uom'])) {
		if($aPerfdata[0]['uom'] == 'kB' or $aPerfdata[0]['uom'] == 'KB') {
			$max=$max*1024;
		} elseif($aPerfdata[0]['uom'] == 'MB') {
			$max=$max*(1024*1024);
		} elseif($aPerfdata[0]['uom'] == 'GB') {
			$max=$max*(1024*1024*1024);
		}
	}

	$count = 0;
	while($max >= 1000) {
		$count++;
		$max = $max / 1024;
	}
	if($count==0) {
		$count="B";
	} elseif($count==1) {
		$count="kB";
	} elseif($count==2) {
		$count="MB";
	} elseif($count==3) {
		$count="GB";
	} elseif($count==4) {
		$count="TB";
	} elseif($count==4) {
		$count="EB";
	}
	$max = number_format($max,1);

	if($aPerfdata[0]['uom'] == '%') {
		$count="%";
		$main_value=$aPerfdata[0]['value'];
	}

	//draw text (percentage of usage and size)
	$text = "$filesystem ($main_value% of $max$count)";
}
else {
	ImageFilledRectangle($im, 2, 2, $width-2, $height-2, $col_critical);
}

if($error != "") {
	ImageTTFText($im, 2*$height/5, 0, $width/100, 3.5*$height/5, $col_text, $font, "$filesystem $error");
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

