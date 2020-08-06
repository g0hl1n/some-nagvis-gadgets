<?php
/*****************************************************************************
 *
 * name: error_text.php
 *
 * This is a NagVis Gadget, it prints a text in the state color (black, yellow, red).
 *  Options are:
 *			fontsize= <fontsize: std is 15>
 *			text= <text>
 *			rot=<rotation in degrees: std is 270>
 *
 * Date: 17.10.2011
 * Version 1.0
 *
 * The gadget gets its data from the NagVis frontend by parameters.
 *****************************************************************************/
/* Changelog:
 *	17.10.2011: v1.0
 *		Birth of this file
 *****************************************************************************/

$font_desc='./fonts/DejaVuSans.ttf';	/* font for the text */

if(isset($_GET['name1']) && $_GET['name1'] != '') {
	$opt_name1 = $_GET['name1'];
}

if(isset($_GET['name2']) && $_GET['name2'] != '') {
	$opt_name2 = $_GET['name2'];
}

if(isset($_GET['state']) && $_GET['state'] != '') {
	$opt_state = $_GET['state'];
}else{
        $opt_state = 'OK';
}

if(isset($_GET['text']) && $_GET['text'] != '') {
	$opt_text = $_GET['text'];
}else{
	//if no text given, text is service name
	$opt_text = "text";
}

if(isset($_GET['rot']) && $_GET['rot'] != '') {
	$opt_rot = $_GET['rot'];
}else{
	$opt_rot = 270;
}
if(isset($_GET['fontsize']) && $_GET['fontsize'] != '') {
	$opt_fontsize = $_GET['fontsize'];
}else{
	$opt_fontsize = 15;
}

$wc = strlen($opt_text);

$image = ImageCreate($opt_fontsize+1, ($opt_fontsize+1)*$wc);
$col_bg = ImageColorAllocate($image,255,0,255);
ImageColorTransparent($image,$col_bg);

if($opt_state == 'OK')
	$col_text = ImageColorAllocate($image, 0, 0, 0);
elseif($opt_state == 'WARNING')
	$col_text = ImageColorAllocate($image, 255, 215, 0);
elseif($opt_state == 'CRITICAL')
	$col_text = ImageColorAllocate($image, 255, 0, 0);

if($opt_state != 'OK') {
	ImageTTFText($image,
		$opt_fontsize,
		$opt_rot,
		0,
		0,
		$col_text,
		$font_desc,
		$opt_text
	);
}

Header("content-type: image/png");
ImagePNG($image);
?>
