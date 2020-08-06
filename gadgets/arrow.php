<?php
/*****************************************************************************
 *
 * name: arrow.php
 *
 * This is a NagVis Gadget, it draws an arrow in the map.
 *
 * Date: 04. Nov. 2010
 * Version 1.2
 *
 * The gadget gets its data from the NagVis frontend by parameters.
 *****************************************************************************/
/* Changelog:
 *	05.Nov.2010: 1.0 -> 1.1
 *		added support for lines (align=4,5)
 *	19.Nov.2010: 1.1 -> 1.2
 *		now color line grey if perfdata=0
 *	16.Nov:2011: 1.2 -> 1.3
 *		add color parameter (in html color format) (this parameter disable color change)
 *****************************************************************************/

//Print Header
header("Content-type: image/png");
#header("Content-type: text/html");
require('./gadgets_core.php');
//-----------------------------------------------------------------------------
// Configuration Variables
//-----------------------------------------------------------------------------
$line_width = 6;		//width of arrow(line)
$length = 100;	//length of arrow
$arr_length = 12;	//length of arrow top
$arr_width = 12;	//width of arrow top
$align = 0;		//align of image	0: --->
				//					1: <---
				//					2: v
				//					3: ^
				//					4: ---
				//					5: |
$state = 'UNKNOWN';	//state

//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
if(isset($_GET["width"])) { $line_width = $_GET["width"]; }
if(isset($_GET["length"])) { $length = $_GET["length"]; }
if(isset($_GET["topwidth"])) { $arr_width = $_GET["topwidth"]; }
if(isset($_GET["toplength"])) { $arr_length = $_GET["toplength"]; }
if(isset($_GET["align"])) { $align = $_GET["align"]; }
if(isset($_GET['state'])) { $state = $_GET['state']; }
if(isset($_GET['color'])) { $rgb_color = html2rgb($_GET['color']); } //disables color change

//-----------------------------------------------------------------------------
// draw image
//-----------------------------------------------------------------------------
//image size
if($align==0 or $align==1 or $align==4) {
	$img_width = $length+1;
	$img_height = $arr_width+1;
} else {
	$img_width = $arr_width+1;
	$img_height = $length+1;
}
	
$count = 7;
//calculate edge-points of arrow
if($align==0) {
	$points = array (
			0, 						$arr_width/2 - $line_width/2,	//left upper point of line
			$length - $arr_length,	$arr_width/2 - $line_width/2,	//right upper point of line
			$length - $arr_length,	0,								//upper point of top
			$length,				$arr_width/2,					//right point of top
			$length - $arr_length,	$arr_width,						//lower point of top
			$length - $arr_length,	$arr_width/2 + $line_width/2,	//right lower point of line
			0,						$arr_width/2 + $line_width/2,	//left lower point of line
		);
} elseif ($align==1) {
	$points = array (
			$length,		$arr_width/2 - $line_width/2,
			$arr_length,	$arr_width/2 - $line_width/2,
			$arr_length,	0,
			0,				$arr_width/2,					
			$arr_length,	$arr_width,					
			$arr_length,	$arr_width/2 + $line_width/2,
			$length,		$arr_width/2 + $line_width/2,
		);
} elseif ($align==2) {
	$points = array (
			$arr_width/2 - $line_width/2,	0,						
			$arr_width/2 - $line_width/2,	$length - $arr_length,	
			0,								$length - $arr_length,	
			$arr_width/2,					$length,				
			$arr_width,						$length - $arr_length,	
			$arr_width/2 + $line_width/2,	$length - $arr_length,	
			$arr_width/2 + $line_width/2,	0,						
		);
} elseif ($align==3) {
	$points = array (
			$arr_width/2 - $line_width/2,	$length,						
			$arr_width/2 - $line_width/2,	$arr_length,	
			0,								$arr_length,	
			$arr_width/2,					0,				
			$arr_width,						$arr_length,	
			$arr_width/2 + $line_width/2,	$arr_length,	
			$arr_width/2 + $line_width/2,	$length,						
		);
} elseif ($align==4) {
	$points = array (
			0, 			$arr_width/2 - $line_width/2 +1,
			$length,	$arr_width/2 - $line_width/2 +1,
			$length,	$arr_width/2 + $line_width/2 ,
			0,			$arr_width/2 + $line_width/2 ,
		);
	$count = 4;
} else {
	$points = array (
			$arr_width/2 - $line_width/2 +1,	0,
			$arr_width/2 - $line_width/2 +1,	$length,
			$arr_width/2 + $line_width/2 ,	$length,
			$arr_width/2 + $line_width/2 ,	0,
		);
	$count = 4;
}

//create iamge
$image = ImageCreate($img_width, $img_height);
$col_bg = ImageColorAllocate($image, 0, 0, 0);
ImageColorTransparent($image, $col_bg);
ImageFill($image, 0, 0, $bg);


if(isset($_GET['perfdata']) && $_GET['perfdata'] != '') {
	$aPerfdata = parsePerfdata($_GET['perfdata']);
	$value=$aPerfdata[0]['value'];
	if($value == "0") {
		$state="UNKNOWN";
	}
} else {
	$state = "EMPTY";
}

//define color of arrow (by state)
switch ($state) {
	case 'OK':
		$col_fg    = ImageColorAllocate($image, 0,255,0);
		break;
	case 'WARNING':
		$col_fg    = ImageColorAllocate($image,235,235,0);
		break;
	case 'CRITICAL':
		$col_fg    = ImageColorAllocate($image,255,0,0);
		break;
	case 'UNKNOWN':
		$col_fg    = ImageColorAllocate($image,100,100,100);
		break;
	case 'EMPTY':
		$col_fg    = ImageColorAllocate($image,255,255,255);
		break;
}

//if fixed color is given, apply it
if(isset($rgb_color)) {
	$col_fg = ImageColorAllocate($image, $rgb_color[0], $rgb_color[1], $rgb_color[2]);
}

ImageFilledPolygon($image, $points, $count, $col_fg);

#$rot_image = ImageRotate($image, $rotation, 0, 0);

//draw PNG
ImagePNG($image);
ImageDestroy($image);
ImageDestroy($rot_image);


function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}
?>
