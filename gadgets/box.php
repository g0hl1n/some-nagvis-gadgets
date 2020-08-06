<?php
/*****************************************************************************
 *
 * name: box.php
 *
 * This NagVis Gadget Draws a Box in the map.
 * It can be used to visualize process flows.
 *  Options are:
 *			fontsize= <fontsize std is 10.5>
 * 			width= <width of gadget>
 *			num_services= <number of services>
 *			num_fs= <number of filesystems>
 *			title= <title>
 *			empty=0/1 <if set, only the border is drawn>
 *			nolines=0/1 <if set, only border and title is drawn>
 *			item=bla-bla <add services, seperated with ->
 *			sharpedge=0/1 <if set, edges are round, otherwise sharp>
 *
 * Date: 21. Nov. 2011
 * Version 1.3
 *
 *****************************************************************************/
/* Changelog:
 *		08.Nov.2010: 1.0 -> 1.1
 *			empty parameter added (now able to create a empty frame)
 *			item added, now gadget can write item text
 *		08.Nov.2010: 1.1 -> 1.2
 *			color change of broder due to state added
 *			font path fixed
 *		21.Nov.2011: 1.2 -> 1.3
 *			sharpedge parameter added (for creating round & sharp edges)
 *****************************************************************************/

//-----------------------------------------------------------------------------
// Configuration Variables
//-----------------------------------------------------------------------------
$width = 300;
$fontsize = 10.5;
$num_services = 2;
$num_fs = 0;
$empty = 0;
$titleonly = 0;
$font='./fonts/DejaVuSans.ttf';
$fontb='./fonts/DejaVuSans-Bold.ttf';

$textHeightFact = 1.6;
$border = 4;
$radius = $border * 7;
$fs_height = 24;
$seperatorSpace = 1;
$titlesizeInc = 2;
$spacer=2;
$fs_spacer=3;

//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
if(isset($_GET["fontsize"]))     { $fontsize = $_GET["fontsize"]; }
if(isset($_GET["width"]))        { $width = $_GET["width"]; }
if(isset($_GET["num_services"])) { $num_services = $_GET["num_services"]; }
if(isset($_GET["num_fs"]))       { $num_fs = $_GET["num_fs"]; }
if(isset($_GET["name1"]))        { $title = $_GET["name1"]; }
if(isset($_GET["title"]))        { $title = $_GET["title"]; }
if(isset($_GET["empty"]))        { $empty = $_GET["empty"]; }
if(isset($_GET["titleonly"]))    { $titleonly = $_GET["titleonly"]; }
if(isset($_GET["sharpedge"]))    { $sharpedge = $_GET["sharpedge"]; }
if(isset($_GET["item"]))		 { $items = explode('-', $_GET["item"]); }
if(isset($_GET["state"]))	 { $state = $_GET["state"]; }

//-----------------------------------------------------------------------------
// Size calculation
//-----------------------------------------------------------------------------
if(count($items) > $num_services) {
	$num_services = count($items);
}

       //      header  +                 services                         +             filesystems         + footer
$height = 2 + $radius + ($textHeightFact*$fontsize+$spacer)*$num_services + ($fs_height+$fs_spacer)*$num_fs + $radius;

$im = ImageCreateTrueColor($width, $height);

$width -= 1;
$height -= 1;
//-----------------------------------------------------------------------------
// Color-Definition
//-----------------------------------------------------------------------------
$col_bg = ImageColorAllocate($im, 0,0,0);
ImageColorTransparent($im, $col_bg);
$col_fg = ImageColorAllocate($im, 255, 255, 255);
$col_text = ImageColorAllocate($im, 255, 255, 255);


// select color by state
if($state == 'CRITICAL') {
	$col_fg = ImageColorAllocate($im, 255, 0, 0);
} elseif($state == 'WARNING') {
	$col_fg = ImageColorAllocate($im, 255, 215, 0);
}

//-----------------------------------------------------------------------------
// draw Gadget
//-----------------------------------------------------------------------------
//fill background
ImageFill($im, 0, 0, $col_bg);

if($sharpedge == 1) {
	//draw straight borders:
		//upper               X1				Y1					X2				Y2
	ImageFilledRectangle($im, 0,				0,					$width,			$border,			$col_fg);
		//lower
	ImageFilledRectangle($im, 0, 				$height-$border,	$width,			$height,			$col_fg);
		//left
	ImageFilledRectangle($im, 0,				0,					$border,		$height,			$col_fg);
		//right
	ImageFilledRectangle($im, $width-$border,	0,					$width,			$height,			$col_fg);
} else {
	//draw straight borders:
		//upper               X1				Y1					X2				Y2
	ImageFilledRectangle($im, $radius,			0,					$width-$radius, $border,			$col_fg);
		//lower
	ImageFilledRectangle($im, $radius, 			$height-$border,	$width-$radius, $height,			$col_fg);
		//left
	ImageFilledRectangle($im, 0,				$radius,			$border,		$height-$radius,	$col_fg);
		//right
	ImageFilledRectangle($im, $width-$border,	$radius,			$width,			$height-$radius,	$col_fg);

	//draw rounded edges
	$outerDiameter = 2*$radius+1;
	$innerDiameter = $outerDiameter-2*$border-1;
		//upper-left
	ImageFilledArc($im,	$radius,		$radius,			$outerDiameter,	$outerDiameter,	180,	270,	$col_fg,	IMG_ARC_PIE);
	ImageFilledArc($im, $radius,		$radius,			$innerDiameter,	$innerDiameter,	180,	270,	$col_bg,	IMG_ARC_PIE);
		//upper-right
	ImageFilledArc($im,	$width-$radius,	$radius,			$outerDiameter,	$outerDiameter,	270,	  0,	$col_fg,	IMG_ARC_PIE);
	ImageFilledArc($im, $width-$radius,	$radius,			$innerDiameter,	$innerDiameter,	270,	  0,	$col_bg,	IMG_ARC_PIE);
		//lower-left
	ImageFilledArc($im,	$radius,		$height-$radius,	$outerDiameter,	$outerDiameter,	 90,	180,	$col_fg,	IMG_ARC_PIE);
	ImageFilledArc($im, $radius,		$height-$radius,	$innerDiameter,	$innerDiameter,	 90,	180,	$col_bg,	IMG_ARC_PIE);
		//lower-right
	ImageFilledArc($im,	$width-$radius,	$height-$radius,	$outerDiameter,	$outerDiameter,	  0,	 90,	$col_fg,	IMG_ARC_PIE);
	ImageFilledArc($im, $width-$radius,	$height-$radius,	$innerDiameter,	$innerDiameter,	  0,	 90,	$col_bg,	IMG_ARC_PIE);
}
if($empty == 0) {
	$upLineY  = $border + ($fontsize+$titlesizeInc)*$textHeightFact + 1;
	$midLineY = $radius + ($textHeightFact*$fontsize+$spacer)*$num_services + $spacer;
	$lowLineY = $midLineY + ($fs_height+$fs_spacer)*$num_fs;

	if($titleonly == 0) {
		//draw seperation lines
		if($num_fs!=0) { $lowLineY = $lowLineY + $fs_spacer; }
			//upper
		ImageLine($im, 0, $upLineY, 	$width,	$upLineY,	$col_fg);
			//mid
		ImageLine($im, 0, $midLineY, 	$width,	$midLineY,	$col_fg);
			//lower
		ImageLine($im, 0, $lowLineY,	$width,	$lowLineY,	$col_fg);

		if(! empty($items)) {
			//write items
			$currentY = $upLineY;
			foreach ($items as $item) {
				$currentY = $currentY + $fontsize*$textHeightFact+1;
				ImageTTFText($im, $fontsize, 0, $radius, $currentY, $col_text, $font, $item);
			}
		}
	}

	//draw title
	ImageTTFText($im, $fontsize+$titlesizeInc, 0, $radius, $upLineY-$fontsize/3-1, $col_text, $fontb, $title);
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

