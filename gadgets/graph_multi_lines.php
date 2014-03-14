<?php
/*
 *    Nagvis Gadget for generating a customizable RRD-Graph with multible lines
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; version 2 of the License.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY OR FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *    General Public License for more details.
 *
 * 	ATTENTION -> you have to adjust the path to your perfdata and an font of your choice 
 *	 - font 		-> line 65 
 *	 - path to rrdfiles 	-> line 97
 *	 - path to rrdtool 	-> line 52 (the - is needed)
 *
 *	graph_multi_lines.php?linescount=1&host1=Windows Server1&service1=Check Ping&desc1=Ping on Windows Server1
 *	- mandatory parameters
 *		- linescount=NUM	-	How many lines should we draw?
 *		- host(NUM)		-	Hostname in nagios ( for every line -> host1=Host1&host2=Host2 if linescount=2 )
 *		- service(NUM)		-	Servicedescription in nagios ( service1=Ping&service2=pong if linecount=2 )
 *		- desc(NUM)		-	Description (used in Legend for every line)
 *	- optional parameters ( for each line )
 *		- perfdatarow(NUM)	-	The row of the perfdata you want to use			Default: 1
 *		- color(NUM)		-	The color (HTML Hex Code) of the line			Default: random color
 *	- optional parameters
 *		- width			-	Width of the graphic in pixels				Default: 250
 *		- height		-	Height of the graphic in pixels				Default: 80
 *		- title			-	Title of the graph (visible on top of the graph)	Default: none
 *		- secondsback		-	How many seconds should we count back? 			Default: 14400 -> 4 hours
 *		- linewidth		-	The width of the lines 					Default: 1
 *		- unit			-	The vertical unit (most times UOM)			Default: none
 *		- font			-	The font rrdtool should use				Default: DejaVuSans.ttf (with full path)
 *
 *	- Example
 *	Two different lines with all optional values set
 *		~ graph_multi_lines.php?linescount=2&host1=Examplehost1&service1=Ping&desc1=Ping RTT on Examplehost1&perfdatarow=2&color1=00FF00&\
 *		  host2=Examplehost2&service2=Ping&desc2=Ping RTT on Examplehost2&perfdatarow2=2&color2=0000FF&width720&height=200&title=Ping roundtrip times&\
 *		  secondsback=3600&linewidth=3&unit=ms
 *
 *	This results in an graph showing 2 lines ( 2 different hosts - always perfdata line 2 of the ping check which should be the roundtrip time )
 *	The graph will have a size of 720x200 pixels (width / height)
 *	The vertical label will be "ms" and the legend will show "Ping RTT on Examplehost1" and "Ping RTT on Examplehost1" (unit / desc(NUM))
 *	The graph will contain data of the last 4 hours (secondsback=3600)
 *	
 *    Questions/Comments/Bugfixes to Stefan Schörghofer ( amd1212@vier-ringe.at)
 *
 */

# General settings
$rrdtool = "/usr/bin/rrdtool - ";
# Default width and height
$width=250;
$height=80;
# Default perfdatarow
$perfdatarow=1;
# Default timeframe calculated (in Seconds)
$secondsback=14400;
# Default linewidth (in graph)
$linewidth=1;
# Default title of the graph -> none
$title="";
# Default vertical label -> none
$unit="";
# Used font -> if not exists gadget will not work
$font="/usr/local/nagios/addons/nagvis/share/userfiles/gadgets/fonts/DejaVuSans.ttf";

# Mandatory parameter - how many lines should we draw?
$linescount = $_GET["linescount"] or die;
$linescommand = "";
$linescommand_run = "";

# Parse optional parameters
if( isset($_GET['width']) ) { $width = $_GET['width']; }
if( isset($_GET['height']) ) { $height = $_GET['height']; }
if(isset($_GET["secondsback"])) { $secondsback = $_GET["secondsback"]; }
if(isset($_GET["title"])) { $title = $_GET["title"]; }
if(isset($_GET["linewidth"])) { $linewidth = $_GET["linewidth"]; }
if(isset($_GET["unit"])) { $unit = $_GET["unit"]; }

# Build rrdtool command
for ($i = 1; $i <= $linescount; $i++) {
	$host = $_GET["host$i"] or die;
	$service = preg_replace("/ /", "_", $_GET["service$i"]) or die;
	$desc = $_GET["desc$i"] or die;
	if(isset($_GET["perfdatarow$i"]) ) { 
		$perfdatarow = $_GET["perfdatarow$i"];
	 } else {
		 $perfdatarow = 1;
	}
	if(isset($_GET["color$i"]) ) { 
		$color = $_GET["color$i"];
	 } else {
		 $color = random_color();
	}
	$linescommand_run = ' DEF:var'.$i.'=/usr/local/nagios/addons/pnp4nagios/var/perfdata/'.$host.'/'.$service.'.rrd:'.$perfdatarow.':MAX LINE'.$linewidth.':var'.$i.'#'.$color.':"'.$desc.'"';
	$linescommand .= $linescommand_run;
}

# Precommand -> basic settings for rrdtool
$precommand = 'graph - --imgformat PNG --start '.(time('')-$secondsback).' --end '.time('').' --height '.$height.' --width '.$width.' --title "'.$title.'" --vertical-label "'.$unit.'" --font DEFAULT:10:'.$font.'';
# Postcommand -> other settins -> ' -g' for disabling legend (just an example)
$postcommand = '';
$command = $precommand;
$command .= $linescommand;
$command .= $postcommand;

# the whole rrdtool magic
$descriptorspec = array (
	0 => array ("pipe","r"),
	1 => array ("pipe","w")
);

$process = proc_open($rrdtool, $descriptorspec, $pipes);

if (is_resource($process)) {

	fwrite($pipes[0], $command);
	fclose($pipes[0]);

	$data = fgets($pipes[1]);
	if (preg_match('/^ERROR/', $data)) {
		$deb = Array();
		$deb['data'] = $data;
		$deb['command'] = $command;
		echo "err";
	} else {
		header("Content-type: image/png");
		echo $data;
		fpassthru($pipes[1]);
	}
	fclose($pipes[1]);
	proc_close($process); 
}


# Functions
##########################
# Generate random color if no color per line is set
function random_color(){
    mt_srand((double)microtime()*1000000);
    $c = '';
    while(strlen($c)<6){
        $c .= sprintf("%02X", mt_rand(0, 255));
    }
    return $c;
}

?>
