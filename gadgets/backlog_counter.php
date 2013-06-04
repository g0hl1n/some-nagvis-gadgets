<?php
/*****************************************************************************
 *
 * name: backlog_counter.php
 *
 * This is a NagVis Gadget, it draws a 7-Segment Output with a optional measurement line in the map.
 *  Options are:
 *			fontsize= <fontsize std is 30>
 * 			mindig= <number of minimum digits>
 *			line= <length of vertical measurement line>
 *			text= <title text of the counter>
 *
 * Author: Richard Leitner <me@g0hl1n.net>
 * Date: 27. Dec. 2010
 * Version 1.3r1
 *
 * The gadget gets its data from the NagVis frontend by parameters.
 *****************************************************************************/
/* Changelog:
 *		21. Dec. 2010: 1.0 -> 1.1
 *			added text argument. (to print name in map)
 *		27. Dec. 2010: 1.1 -> 1.2
 *			fixed issue with the fill-graph
 *			added dynamical widht
 *		25. May. 2011: 1.2 -> 1.3
 *			corrected value bar max value (height)
 *		14. Jun. 2011: 1.3 -> 1.3r1
 *			corrected value bar max value (height)
 *****************************************************************************/

require('./gadgets_core.php');
//-----------------------------------------------------------------------------
// Configuration Variables
//-----------------------------------------------------------------------------
$width = 1000;
$height = 80;
$font='./fonts/7segment.ttf';
$title_text_font = './fonts/DejaVuSans.ttf';
$title_text_fontsize=12;

//-----------------------------------------------------------------------------
// Performance Variables
//-----------------------------------------------------------------------------
$value = 1337;
$state = "UNKNOWN";
$fontsize = 30;
$line = 0;
$title_text = "";

//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
if(isset($_GET["fontsize"])) { $fontsize = $_GET["fontsize"]; }
if(isset($_GET["mindig"])) { $mindig = $_GET["mindig"]; }
if(isset($_GET["line"])) { $line = $_GET["line"]; }
if(isset($_GET["text"])) { $title_text = $_GET["text"]; }

//-----------------------------------------------------------------------------
// get values from NagVis
//-----------------------------------------------------------------------------
$aOpts = Array('name1', 'name2', 'state', 'stateType', 'perfdata');
$aPerfdata = Array();
$error = "";
if(isset($_GET['perfdata']) && $_GET['perfdata'] != '') {
	$aOpts['perfdata'] = $_GET['perfdata'];
} else {
	$error = "no dat";
	$digits = $mindig;
	if(isset($_GET['state'])) { $state=$_GET['state']; }
}

if($error == "") {
	$aPerfdata = parsePerfdata($aOpts['perfdata']);

	$state=$_GET['state'];
	$crit=$aPerfdata['0']['warning'];
	if($crit < 1) {
		$crit = 100000;
	}
	$value=$aPerfdata[0]['value'];
	if($state == "Critical" and ! is_numeric($value)) {
		$value = -1;
	}
}

//-----------------------------------------------------------------------------
// Size calculation
//-----------------------------------------------------------------------------
if($error != "") {
	$width = $digits*$fontsize*0.71+5;
} else {
	if(strlen($value) < $mindig) {
		$width = $mindig*$fontsize*0.71+5;
		$digits = $mindig;
	} else {
		$width = strlen($value)*$fontsize*0.71+5;
		$digits = strlen($value);
	}
}
$title_text_space=0;
if($title_text != "") {
	$title_text_space = $title_text_fontsize*2;
}
$height = $line+$fontsize+8;
$line -= $title_text_space;
$graph_width = 25;

$im = ImageCreateTrueColor($width+$graph_width+1, $height+1);

//-----------------------------------------------------------------------------
// Color-Definition
//-----------------------------------------------------------------------------
$col_bg = ImageColorAllocate($im, 0,0,0);
$col_value = ImageColorAllocate($im, 255, 255, 255);
$col_fg = ImageColorAllocate($im, 255, 255, 255);
$col_crit = ImageColorAllocate($im, 255, 140, 140);
$col_blue = ImageColorAllocate($im, 105, 173, 239);

ImageColorTransparent($im, $col_bg);

// select color by state
if($state == 'CRITICAL') {
	$col_value = ImageColorAllocate($im, 255, 0, 0);
	$col_blue = $col_value;
} elseif($state == 'WARNING') {
	$col_value = ImageColorAllocate($im, 255, 215, 0);
	$col_blue = $col_value;
} elseif($state == 'UNKNOWN') {
	$col_value = ImageColorAllocate($im, 200 ,200, 200);
	$col_blue = $col_value;
}

//-----------------------------------------------------------------------------
// draw Gadget
//-----------------------------------------------------------------------------
//set text to write
if($error != "") {
	$text = str_pad($error, $digits, "0", STR_PAD_LEFT);
} else {
	$text = str_pad($value, $digits, "0", STR_PAD_LEFT);
}

//line goes down: counter is at top
//draw Frame
ImageFilledRectangle($im, 0, 0+$title_text_space, $width+$graph_width, $fontsize+8+$title_text_space, $col_fg);
ImageFilledRectangle($im, 2, 2+$title_text_space, $width-2, $fontsize+6+$title_text_space, $col_bg);
ImageFilledRectangle($im, $width+1, 2+$title_text_space, $width+$graph_width-2, $fontsize+6+$title_text_space, $col_bg);

//calculate backlog graph
if($error == "") {
	$graph_max = $fontsize+2+$title_text_space;
	$val_max = $crit/0.8;
	$cur_val = $value/$val_max;
	if($cur_val >= 1.0) { $cur_val = 1.0; } // if current backlog is higher then the maximum defined: fully fill the graph

	//draw backlog fill-graph border
	ImageRectangle(      $im, $width+4, 5+$title_text_space, $width+$graph_width-5, 5+$graph_max,                     $col_fg);
	ImageFilledRectangle($im, $width+5, 6+$title_text_space, $width+$graph_width-6, $graph_max*0.2+$title_text_space, $col_crit);
	//draw value in graph
	ImageFilledRectangle($im, $width+5, 4+$graph_max,        $width+$graph_width-6, 4+$graph_max+($title_text_space-$graph_max)*($cur_val), $col_blue);
}

//draw text
ImageTTFText($im, $fontsize, 0, 4, $fontsize+4+$title_text_space, $col_value, $font, $text);

//draw measurement line
ImageDashedLine($im, ($width+$graph_width+1)/2, $fontsize+9+$title_text_space, ($width+$graph_width+1)/2, $height, $col_fg);

//draw tite text
if($title_text != "") {
	ImageTTFText($im, $title_text_fontsize, 0, 1, $title_text_fontsize*1.5, $col_value, $title_text_font, $title_text);
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

