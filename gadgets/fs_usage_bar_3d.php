<?php
/*****************************************************************************
 *
 * fs_usage_bar_3d.php 
 *
 * Author: Richard Leitner <me@g0hl1n.net>
 * Date: 23.09.2010
 * Version 1.0
 *
 * The gadget gets its data from the NagVis frontend by parameters.
 *****************************************************************************/

require('./gadgets_core.php');
header("Content-type: image/png");
//-----------------------------------------------------------------------------
// Configuration Variables
//-----------------------------------------------------------------------------
$width = 1000;
$height = 80;
$font='./fonts/DejaVuSansMono.ttf';

//-----------------------------------------------------------------------------
// Performance Variables
//-----------------------------------------------------------------------------
$value = 0.96;
$inode_value=0.4;
$main_value=$value*100;
$filesystem='/dev/test';
$warning=0.9;
$critical=0.95;

//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
$scale = $_GET["scale"];
$xscale = $_GET["xscale"];
$yscale = $_GET["yscale"];

//-----------------------------------------------------------------------------
// get values from NagVis
//-----------------------------------------------------------------------------

$aOpts = Array('name1', 'name2', 'state', 'stateType', 'perfdata');
$aPerfdata = Array();

/**
 * Optional
 *  name1=localhost
 *  name2=Current Load
 *  state=OK
 *  stateType=HARD
 */
if(isset($_GET['perfdata']) && $_GET['perfdata'] != '') {
        $aOpts['perfdata'] = $_GET['perfdata'];
} elseif ($_GET['conf'] == "1"){
       $options['perfdata'] = "config=34;80;90;0;100 inodes=17%;";
} else {
        errorBox('Filesystem is unreachable (or no perfdata submitted)');
}


# Parse Perfadata
$aPerfdata = parsePerfdata($aOpts['perfdata']);

# Store Perfdata in local variables
$filesystem=$aPerfdata[0]['label'];
$value=$aPerfdata[0]['value'];
$warning=$aPerfdata[0]['warning'];
$critical=$aPerfdata[0]['critical'];
$max=$aPerfdata[0]['max'];
$inode_value=$aPerfdata[1]['value'];

#errorBox("inode value: $inode_value");
# Calculate relative Values
$value = $value / $max;
$warning = $warning / $max;
$critical = $critical / $max;
$main_value = sprintf("%d", $value*100);
$inode_value = 1- $inode_value / 100;

//-----------------------------------------------------------------------------
// Size calculation
//-----------------------------------------------------------------------------
if($scale != 0)
{
        $width = $width*$scale;
        $height = $height*$scale;
}
if($xscale != 0)
{
        $width = $width*$xscale;
}
if($yscale != 0)
{
        $height = $height*$yscale;
}

$im = ImageCreateTrueColor($width+1, $height+1);

//-----------------------------------------------------------------------------
// Color-Definition
//-----------------------------------------------------------------------------
$col_bg = ImageColorAllocate($im, 255, 255, 255);
$col_border = ImageColorAllocate($im, 0, 0, 0);
$col_frame = ImageColorAllocate($im, 190, 190, 190);
$col_value = ImageColorAllocateAlpha($im, 100, 100, 100, 40);
$col_critical = ImageColorAllocateAlpha($im, 255, 0, 0, 50);
$col_warning = ImageColorAllocateAlpha($im, 255, 215, 0, 50);
$col_inodes = ImageColorAllocateAlpha($im, 0, 0, 0, 0);
$col_text = ImageColorAllocate($im, 0, 0, 0);
ImageColorTransparent($im, $col_bg);

if($value >= $warning)
{
	$col_value = ImageColorAllocate($im, 255, 215, 0);
	if($value >= $critical)
	{
		$col_value = ImageColorAllocate($im, 255, 0, 0);
	}
}
else
{
	$col_value = ImageColorAllocate($im, 0, 255, 0);
}

//-----------------------------------------------------------------------------
// calculate Frame
//-----------------------------------------------------------------------------
$x_off = 40;
$y_off = 5;
$val_off = 0.25;

$val_frame_top = array (
		$width/$x_off,			0,
		$width,				0,
		($x_off-1)*$width/$x_off,	$height/$y_off,
		0,				$height/$y_off
		);

$val_frame_left = array (
		$width/$x_off,			0,
		$width/$x_off,			($y_off-1)*$height/$y_off,
		0,				$height,
		0,				$height/$y_off
		);

$val_frame_right = array (
		($x_off-1)*$width/$x_off, 	$height/$y_off,
		$width,				0,
		$width,				($y_off-1)*$height/$y_off,
		($x_off-1)*$width/$x_off,	$height
		);

$val_frame_bot = array (
		$width/$x_off,			($y_off-1)*$height/$y_off,
		$width,				($y_off-1)*$height/$y_off,
		($x_off-1)*$width/$x_off,	$height,
		0,				$height
		);

# calculate Value Bar

$val_value_top = array (
		$width/$x_off/4,			(2-$val_off)*$height/$y_off, #links-unten
		$width*$value-$width/$x_off/4,		(2-$val_off)*$height/$y_off, #rechts-unten
		$width*$value-$width/$x_off/4,		(2-$val_off)*$height/$y_off-0.6*$height/$y_off, #rechts oben
		3*($width/$x_off/4),			(2-$val_off)*$height/$y_off-0.6*$height/$y_off #links oben
		);

$val_value_right = array (
		$width*$value-$width/$x_off+$width/$x_off/4,      	(2-$val_off)*$height/$y_off,
		$width*$value-$width/$x_off/4,				(2-$val_off)*$height/$y_off-0.60*$height/$y_off,
		$width*$value-$width/$x_off/4,				$height-((1+$val_off)*$height/$y_off)-0.60*$height/$y_off,
		$width*$value-$width/$x_off+$width/$x_off/4, 	$height-((1+$val_off)*$height/$y_off)
		);

#calculate Warning/Critcal areas

$val_crit = array (
                $width*$critical-2-$width/$x_off,       $height/$y_off,
                $width*$critical,                         0,
                $width*$critical,                         ($y_off-1)*$height/$y_off,
                $width*$critical-2-$width/$x_off,       $height
                );

$val_warn = array (
                $width*$warning-1-$width/$x_off,       $height/$y_off,
                $width*$warning,                         0,
                $width*$warning,                         ($y_off-1)*$height/$y_off,
                $width*$warning-1-$width/$x_off,       $height
                );

//-----------------------------------------------------------------------------
// draw Gadget
//-----------------------------------------------------------------------------
//draw Frame
ImageFillToBorder($im, 0, 0, $col_bg, $col_bg);
#backgroud
ImageFilledRectangle($im, $width/$x_off, 0, $width, ($y_off-1)*$height/$y_off, $col_frame);

# Botton Frame
ImageFilledPolygon($im, $val_frame_bot, 4, $col_frame);
ImagePolygon($im, $val_frame_bot, 4, $col_border);

# Warning & Critical area
ImageFilledRectangle($im, $width*$warning-$width/$x_off, 0, $width*$critical-2-$width/$x_off, $height-1, $col_warning);
ImageFilledRectangle($im, $width*$critical-1-$width/$x_off, 2, $width-$width/$x_off, $height-1, $col_critical);
ImagePolygon($im, $val_crit, 4, $col_border);
ImagePolygon($im, $val_warn, 4, $col_border);

# left Frame
ImageFilledPolygon($im, $val_frame_left, 4, $col_frame);
ImagePolygon($im, $val_frame_left, 4, $col_border);

# top Frame
ImageFilledPolygon($im, $val_frame_top, 4, $col_frame);
ImagePolygon($im, $val_frame_top, 4, $col_border);

//draw usage value
#value bar front
ImageFilledRectangle($im, $width/$x_off/4, (2-$val_off)*$height/$y_off, $width*$value-$width/$x_off+$width/$x_off/4, $height-((1+$val_off)*$height/$y_off), $col_value);
ImageRectangle($im, $width/$x_off/4, (2-$val_off)*$height/$y_off, $width*$value-$width/$x_off+$width/$x_off/4, $height-((1+$val_off)*$height/$y_off), $col_border);

#value bar top
ImageFilledPolygon($im, $val_value_top, 4, $col_value);
ImagePolygon($im, $val_value_top, 4, $col_border);

#value bar right
ImageFilledPolygon($im, $val_value_right, 4, $col_value);
ImagePolygon($im, $val_value_right, 4, $col_border);

ImageFilledPolygon($im, $val_frame_right, 4, $col_frame);
ImagePolygon($im, $val_frame_right, 4, $col_border);

//draw inode value
ImageFilledRectangle($im, 4, $height-$height/10, ($width-$width/$x_off)*$inode_value, $height-$height/20, $col_inodes);

//draw crit/warn lines
ImageLine($im, $width*$warning-1-$width/$x_off, $height/$y_off, $width*$warning-1-$width/$x_off, $height, $col_border);
ImageLine($im, $width*$critical-2-$width/$x_off, $height/$y_off, $width*$critical-2-$width/$x_off, $height, $col_border);

//draw text
$text = "$filesystem ($main_value%)";
ImageTTFText($im, 1.4*$height/5, 0, $width/100, 3.4*$height/5, $col_text, $font, $text);

//-----------------------------------------------------------------------------
// Output Image
//-----------------------------------------------------------------------------
if(function_exists('ImageAntialias'))
{
        ImageAntialias($im, true);
}
ImagePNG($im);
ImageDestroy($im);

?>

