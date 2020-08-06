<?php
/*****************************************************************************
 *
 * name: throughput_counter.php
 *
 * This is a NagVis Gadget, it draws a 7-Segment Output with a optional measurement line in the map.
 *  Options are:
 *			fontsize= <fontsize std is 30>
 * 			maxdig= <number of maximum digits>
 *			line= <length of vertical measurement line>
 *			ath= <all time high> for tachometer
 *			fact= <factor for perfdata>
 *			text= <text beneath the gadget>
 *			check_space= <1 for adding a space for a check lamp>
 *
 * Date: 10. Oct. 2011
 * Version 2.0
 *
 * The gadget gets its data from the NagVis frontend by parameters.
 *****************************************************************************/
/* Changelog:
 *		10.Nov.2010: 1.0 -> 1.1
 *			factor argument added. (to multiply perfdata by a factor)
 *		21. Dec. 2010: 1.1 -> 1.2
 *			text argument added. (to name the gadget in the map)
 *		27. Dec. 2010: 1.2 -> 1.3
 *			added dynamical with and changed argument maxdig to mindig.
 *		10. Oct. 2011: 1.3 -> 1.4
 *			added to turn value be blue when check is acknowledged
 *		10. Oct. 2011: 1.4 -> 2.0
 *			added box for placing stuff in it (via the check_space arg)
 *			complete re-write of the gadget (for clear variables and structure)
 *****************************************************************************/

require('./gadgets_core.php');
//-----------------------------------------------------------------------------
// CONFIGURATION
//-----------------------------------------------------------------------------
$font_counter='./fonts/7segment.ttf';	/* font for the counter */
$font_desc='./fonts/DejaVuSans.ttf';	/* font for the text beneath the gadget */

$m_desc_text_fontsize=12;				/* fontsize for the text beneath the gadget */
$m_outer_line_width = 2;				/* linewidth of the border lines */
$m_inner_line_width = 1;				/* linewidth of the inner seperation lines */

//-----------------------------------------------------------------------------
// DEFAULT VALUES
//-----------------------------------------------------------------------------
$p_value = 0;			/* service perf value */
$p_state = "UNKNOWN";		/* service state */
$p_linedir = 0;				/* direction where the line should go */
$p_fact = 1;				/* factor for perfdata */
$p_desc_text="";			/* text beneath the gadget */
$p_check_space_box=0;		/* space for the additional check bulb */

$m_counter_fontsize = 30;	/* fontsize for the counter */
$m_line_lenght = 0;			/* length of the vertical line */

/* space around the counter text */
$m_counter_to_left_border  = $m_counter_fontsize / 6;
$m_counter_to_right_border = $m_counter_to_left_border;
$m_counter_to_top_border   = $m_counter_to_left_border;
$m_counter_to_bot_border   = $m_counter_to_left_border;

/* space between gadget and description text */
$m_gadget_to_desc_text_space = 3;
$m_space_under_desc_text = 3;

$m_tacho_span = 110;	/* size of tacho pie in degrees */
$m_tacho_warn_area_size = 0.2;	/* warn area size of tacho */
$m_tacho_thickness = 10;	/* thickness of the tacho scala in pixels */

//-----------------------------------------------------------------------------
// GET Variables
//-----------------------------------------------------------------------------
if(isset($_GET["mindig"])) { $p_mindig = $_GET["mindig"]; }
if(isset($_GET["ath"])) { $p_ath = $_GET["ath"]; }
if(isset($_GET["fact"])) { $p_fact = $_GET["fact"]; }
if(isset($_GET["text"])) { $p_desc_text = $_GET["text"]; }
if(isset($_GET["check_space"])) { $p_check_space_box = $_GET["check_space"]; }
if(isset($_GET["fontsize"])) { $m_counter_fontsize = $_GET["fontsize"]; }
if(isset($_GET["line"])) { $m_line_lenght = $_GET["line"]; }

//-----------------------------------------------------------------------------
// get values from NagVis
//-----------------------------------------------------------------------------
if(isset($_GET['state'])) { $p_state=$_GET['state']; }	/* get service state */

/* get downtime and ack of service */
if(isset($_GET['downtime'])) { $p_downtime = $_GET['downtime']; }
if(isset($_GET['ack'])) { $p_ack = $_GET['ack']; }

if(isset($_GET['perfdata']) && $_GET['perfdata'] != '') {
	$p_perfdata = parsePerfdata($_GET['perfdata']);	/* parse perfdata */
	$p_value=$p_perfdata[0]['value']*$p_fact;		/* get first value and re-calc it with given fact */
} else {
	/* no perfdata -> error */
	$error_text = "Error";
}

//-----------------------------------------------------------------------------
// Size calculation
//-----------------------------------------------------------------------------
/* height of the box containing the 7seg counter */
$m_counter_box_height = $m_outer_line_width + $m_counter_to_top_border +
						$m_counter_fontsize + $m_counter_to_bot_border;

/* height of the box containing the tachometer */
$m_tacho_box_height = $m_counter_box_height;

/* height of the counter and tacho box */
$m_boxes_height = $m_counter_box_height + $m_inner_line_width + $m_tacho_box_height;

/* start point of box */
$m_box_x0 = 0;
$m_box_y0 = $m_line_lenght;

/* width of one 7seg digit */
$m_counter_font_width = $m_counter_fontsize * 0.71;

/* with of the check space box */
$m_check_space_box_width = $m_tacho_box_height;

/* width of the counter and the tacho box */
if( $error_text != "" ) {
	/* error occured */
	$m_counter_box_width = $m_outer_line_width * 2 + $m_counter_to_left_border +
		$m_counter_to_right_border + strlen($error_text) * $m_counter_font_width;
}
elseif( $p_value < 10^($p_mindig - 1) ) {
	/* have to fill up to minimal width */
	$m_counter_box_width = $m_outer_line_width * 2 + $m_counter_to_left_border + 
		$m_counter_to_right_border + $p_mindig * $m_counter_font_width;
}
else {
	/* value is larger than mindig */
	$m_counter_box_width = $m_outer_line_width * 2 + $m_counter_to_left_border +
		$m_counter_to_right_border + strlen($p_value) * $m_counter_font_width;
}

/* tacho scala dimensions */
$m_tacho_width = $m_counter_box_width;
$m_tacho_height = $m_tacho_box_height * 0.7 * 2;

/* angle where tacho starts */
$m_tacho_start_angle = 180 + (180 - $m_tacho_span) / 2;

/* angle where tacho ends */
$m_tacho_end_angle = 360 - (180 - $m_tacho_span) / 2;

/* angle where warn area on tacho starts */
$m_tacho_start_warn_angle = $m_tacho_end_angle - $m_tacho_span * $m_tacho_warn_area_size;

/* total image height */
if($p_desc_text != "") {
	/* if there's description text */
	$m_img_height = $m_boxes_height + $m_gadget_to_desc_text_space + $m_line_lenght +
						$m_desc_text_fontsize + $m_space_under_desc_text;
} else {
	/* there's no description text */
	$m_img_height = $m_boxes_height + $m_line_lenght;
}

/* total image width */
if( $p_check_space_box == 1 ) {
	/* with check space box */
	$m_img_width = $m_counter_box_width + $m_check_space_box_width;
}
else {
	/* without check space box */
	$m_img_width = $m_counter_box_width;
}

/* create image */
$im = ImageCreateTrueColor($m_img_width + 1, $m_img_height + 1);

//-----------------------------------------------------------------------------
// Color-Definition
//-----------------------------------------------------------------------------
$col_bg    = ImageColorAllocate($im, 0,0,0);			/* background color (black) */
$col_black = ImageColorAllocate($im, 0,0,0);			/* black */
$col_value = ImageColorAllocate($im, 255, 255, 255);	/* value color (white) */
$col_fg    = ImageColorAllocate($im, 255, 255, 255);	/* foreground color (white) */
$col_blue = ImageColorAllocate($im, 105, 173, 239);		/* blue -> for ack's & downtimes */
$col_warn = ImageColorAllocate($im, 255, 237, 140);

ImageColorTransparent($im, $col_bg);	/* background is transparent */

// select color by state
if($p_state == 'CRITICAL') {
	/* critical -> RED */
	$col_value = ImageColorAllocate($im, 255, 0, 0);
} elseif($p_state == 'WARNING') {
	/* warning -> ORANGE */
	$col_value = ImageColorAllocate($im, 255, 215, 0);
} elseif($p_state == 'UNKNOWN') {
	/* unknown -> GREY */
	$col_value = ImageColorAllocate($im, 200 ,200, 200);
}

//if state is acknowledged or downtime: let counter be blue
if($p_ack == 1 or $p_downtime == 1) {
	$col_value = $col_blue;
}

//-----------------------------------------------------------------------------
// draw Gadget
//-----------------------------------------------------------------------------
//set text to write
if($error != "") {
	/* write error text */
	$text_counter = str_pad($error_text, $p_mindig, "0", STR_PAD_LEFT);
} else {
	/* write value */
	$text_counter = str_pad($p_value, $p_mindig, "0", STR_PAD_LEFT);
}


/* draw outer frame of counter and tacho box */
ImageFilledRectangle($im,
	$m_box_x0,
	$m_box_y0,
	$m_box_x0 + $m_counter_box_width,
	$m_box_y0 + $m_boxes_height,
	$col_fg
);
ImageFilledRectangle($im,
	$m_box_x0 + $m_outer_line_width,
	$m_box_y0 + $m_outer_line_width,
	$m_box_x0 + $m_counter_box_width - $m_outer_line_width,
	$m_box_y0 + $m_boxes_height - $m_outer_line_width,
	$col_black
);

/* draw separation line between counter and tacho */
ImageFilledRectangle($im,
	$m_box_x0,
	$m_box_y0 + $m_counter_box_height + 1,
	$m_box_x0 + $m_counter_box_width,
	$m_box_y0 + $m_counter_box_height + $m_inner_line_width,
	$col_fg
);

/* draw check space box if needed */
if( $p_check_space_box == 1) {
	ImageFilledRectangle($im,
		$m_box_x0 + $m_counter_box_width - $m_outer_line_width + 1,
		$m_box_y0,
		$m_box_x0 + $m_counter_box_width + $m_check_space_box_width,
		$m_box_y0 + $m_boxes_height,
		$col_fg
	);
	ImageFilledRectangle($im,
		$m_box_x0 + $m_counter_box_width + 1,
		$m_box_y0 + $m_outer_line_width,
		$m_box_x0 + $m_counter_box_width + $m_check_space_box_width - $m_outer_line_width,
		$m_box_y0 + $m_boxes_height - $m_outer_line_width,
		$col_black
	);
}

/* draw counter value */
ImageTTFText($im,
	$m_counter_fontsize,
	0,
	$m_box_x0 + $m_outer_line_width + $m_counter_to_left_border,
	$m_box_y0 + $m_outer_line_width + $m_counter_to_top_border + $m_counter_fontsize,
	$col_value,
	$font_counter,
	$text_counter
);

	
/* draw tachometer scala */
ImageFilledArc($im,
	$m_box_x0 + $m_counter_box_width / 2,
	$m_box_y0 + $m_boxes_height,
	$m_tacho_width,
	$m_tacho_height,
	$m_tacho_start_angle,
	$m_tacho_start_warn_angle,
	$col_blue,
	IMG_ARC_PIE
);
ImageFilledArc($im,
	$m_box_x0 + $m_counter_box_width / 2,
	$m_box_y0 + $m_boxes_height,
	$m_tacho_width,
	$m_tacho_height,
	$m_tacho_start_warn_angle,
	$m_tacho_end_angle,
	$col_warn,
	IMG_ARC_PIE
);
ImageFilledArc($im,
	$m_box_x0 + $m_counter_box_width / 2,
	$m_box_y0 + $m_boxes_height + $m_tacho_thickness,
	$m_tacho_width,
	$m_tacho_height + $m_tacho_thickness,
	$m_tacho_start_angle,
	$m_tacho_end_angle,
	$col_black,
	IMG_ARC_PIE
);
ImageFilledRectangle($im,
	$m_box_x0,
	$m_box_y0 + $m_boxes_height - $m_outer_line_width + 1,
	$m_box_x0 + $m_counter_box_width,
	$m_box_y0 + $m_boxes_height,
	$col_fg
);

/* calc the percentage of the needle position on the tacho */
$percentage = $p_value * (1 - $m_tacho_warn_area_size) * 100 / $p_ath;

if($percentage >= 50 and $percentage <= 100) {
	/* if percentage is larger then 50% -> needle is right of source point */
	$angle = $percentage-50;
} elseif($percentage < 50)  {
	/* if percentage is smaller then 50% -> needle is left of source point */
	$angle = 310 + $percentage;
} else {
	/* if percentage is 50 % -> needle is vertical */
	$angle = 50;
}
	/* calc the offsets for the needle tip from the needle source */
$Xoff = ($m_counter_box_width+0) * sin(Deg2Rad($angle))/2*1.2;
$Yoff = ($m_counter_fontsize+5)*1.5/2*cos(Deg2Rad($angle))*1.2;

/* draw tacho needle */
ImageLineThick($im,
	$m_box_x0 + $m_counter_box_width / 2,
	$m_box_y0 + $m_boxes_height - 1,
	$m_box_x0 + $m_counter_box_width / 2 + $Xoff,
	$m_box_y0 + $m_boxes_height - 1 - $Yoff,
	$col_fg,
	2
);

/* draw line */
ImageDashedLine($im,
	$m_box_x0 + $m_counter_box_width / 2,
	0,
	$m_box_x0 + $m_counter_box_width / 2,
	$m_box_y0,
	$col_fg
);

/* draw description text */
ImageTTFText($im,
	$m_desc_text_fontsize,
	0,
	$m_box_x0 + $m_outer_line_width,
	$m_box_y0 + $m_boxes_height + $m_gadget_to_desc_text_space + $m_desc_text_fontsize,
	$col_fg,
	$font_desc,
	$p_desc_text
);

//-----------------------------------------------------------------------------
// Output Image
//-----------------------------------------------------------------------------
if(function_exists('ImageAntialias')) {
        ImageAntialias($im, true);
}

header("Content-type: image/png");
ImagePNG($im);
ImageDestroy($im);


function ImageLineThick($_image, $_x1, $_y1, $_x2, $_y2, $_color, $_thick = 1)
{
    if ($_thick == 1) {
        return imageline($_image, $_x1, $_y1, $_x2, $_y2, $_color);
    }
    $t = $_thick / 2 - 0.5;
    if ($_x1 == $_x2 || $_y1 == $_y2) {
        return imagefilledrectangle($_image, round(min($_x1, $_x2) - $t), round(min($_y1, $_y2) - $t), round(max($_x1, $_x2) + $t), round(max($_y1, $_y2) + $t), $_color);
    }
    $k = ($_y2 - $_y1) / ($_x2 - $_x1); //y = kx + q
    $a = $t / sqrt(1 + pow($k, 2));
    $points = array(
        round($_x1 - (1+$k)*$a), round($_y1 + (1-$k)*$a),
        round($_x1 - (1-$k)*$a), round($_y1 - (1+$k)*$a),
        round($_x2 + (1+$k)*$a), round($_y2 - (1-$k)*$a),
        round($_x2 + (1-$k)*$a), round($_y2 + (1+$k)*$a),
    );
    imagefilledpolygon($_image, $points, 4, $_color);
    return imagepolygon($_image, $points, 4, $_color);
}
?>

