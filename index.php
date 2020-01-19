<?php
set_time_limit(60);
error_reporting(0);

function mkhead() {
$navbar = "<div id=\"bar\"><a href=\"http://tnuc.org\">tnuC</a> &nbsp;&#187;&nbsp; <a href=\"http://unjusts.tnuc.org\">unjusts tool</a>\n<div id=\"barsw\"></div>
<div id=\"barnw\"></div>\n<div id=\"barne\"></div>\n<div id=\"barse\"></div>\n</div>";
echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>tnuC - unjusts tool</title>
<link rel="stylesheet" type="text/css" href="http://tnuc.org/style.css" />
<link rel="shortcut icon" href="http://tnuc.org/favicon.ico" type="image/vnd.microsoft.icon" />
<link rel="icon" href="http://tnuc.org/favicon.ico" type="image/vnd.microsoft.icon" />
<meta name="description" content="Tibia Unjusts Tool. Analyzes your unjustified kills, calculates how many unjusts you have left before taking black or red skull, and/or how long it takes for your skull to expire." />
<meta name="author" content="Flo - tnuC.org" />
<meta name="keywords" content="tnuc,flo,tibia,unjust,unjusts,calculator,red skull,black skull,unjustified" />
<meta name="robots" content="index, follow, noimageindex" />
</head>
<body>
<center>
<div id="wrap">
<div id="bnr"></div>
$navbar
<div id="content"><div id="inner">


EOD;
}

function mkfoot() {
echo <<<EOD


</div></div>
<div id="foot">
<p><a href="http://tnuc.org/about/">about</a> &nbsp;&bull;&nbsp; <a href="http://tnuc.org/contact/">contact</a></p>
<p>2006-2012 Flo &nbsp;|&nbsp; <a class="silver" href="http://tnuc.org">tnuC v8</a>
</p>
</div>
</center>
</body>
</html>
EOD;
}

$output = '<h1>Unjusts Tool</h1>
<a href="http://tnuc.org">back to home page</a><br /><br /><br />

<form name="unjform" action="./" method="post">
<textarea class="cform" name="kills" style="width:99%; height:150px;" autofocus>';
if(isset($_POST['kills'])) $output .= htmlspecialchars(stripslashes($_POST['kills']));
$output.= '</textarea><br />
<input class="cform" type="submit" value=" submit " />
</form><br /><br />';



function is_CEST(&$offset)
{
	$m1 = date("m");
	switch($m1) {
		case 11:
		case 12:
		case 1:
		case 2:
			return false;
		break;
		case 3:
			$y1 = date("Y");
			$d1 = date("d");
			$d2 = date("N",strtotime("{$y1}-03-31")+($offset*3600));
			$d2 = $d2 == 7 ? 31 : 31 - $d2;
			if ($d1 > $d2 || ($d1 == $d2 && date("H") != 0))
				return true;
			else return false;
		break;
		case 10:
			$y1 = date("Y");
			$d1 = date("d");
			$d2 = date("N",strtotime("{$y1}-10-31")+($offset*3600));
			$d2 = $d2 == 7 ? 31 : 31 - $d2;
			if ($d1 < $d2 || ($d1 == $d2 && date("H") == 0))
				return true;
			else return false;
		break;
		default:
			return true;
		break;
	}
}

function delta($end)
{
	$d = (int) (($end - ($end % (60*60*24))) / (60*60*24));
	$h = (int) (($end - (($end % (60*60)) + $d*3600*24 )) / 3600);
	$m = (int) (($end - (($end % 60) + $d*3600*24 + $h*3600)) / 60);
	$s = (int) ($end % 60);
	if($m<10) $m = "0$m";
	if($s<10) $s = "0$s";
	if($d > 0)
		return "$d days and $h:$m:$s";
	else
		return "$h:$m:$s";
}

function check($type,$offs,&$unj,&$redend = null,&$redoffs = null,$bancheck = false,$test = false)
{
	if($offs >= $type-1)
	{
		global $black_end;
		if($black_end > $unj[$offs][3]) {
			if($test === true) return true;
			$unj[$offs][4]=10;
			$black_end = $unj[$offs][3] + 60*60*24*45;
			return true;
		}
		if($type === 3 || $type === 6) $delta = 60*60*24;
		elseif($type === 5 || ($type === 10 && $bancheck === true)) $delta = 60*60*24*7;
		elseif(($type === 10 && $bancheck === false) || $type === 20) $delta = 60*60*24*30;
		
		if ($unj[$offs-($type-1)][3] < $unj[$offs][3]-$delta) {
			// Frag didn't cause RS/Ban
			return false;
		} else {
			// Frag caused RS/Ban
			if($test === false) {
				if($type === 3 && $redend === 0) $unj[$offs][4]=1;
				elseif($type === 5 && $redend === 0) $unj[$offs][4]=2;
				elseif($type === 10 && !$bancheck && $redend === 0) $unj[$offs][4]=3;
				elseif($type === 3) $unj[$offs][4]=4;
				elseif($type === 5) $unj[$offs][4]=5;
				elseif($type === 10 && !$bancheck) $unj[$offs][4]=6;
				elseif($type === 6) $unj[$offs][4]=7;
				elseif($type === 10 && $bancheck) $unj[$offs][4]=8;
				elseif($type === 20) $unj[$offs][4]=9;
				if(in_array($unj[$offs][4],array(7,8,9))) {	
					$black_end = $unj[$offs][3] + 60*60*24*45;
				} else $redend = $unj[$offs][3] + 60*60*24*30;
				$redoffs = $offs;
			}
			return true;
		}
	}
	else return false;
}

function timeleft(&$unj,$black = false) {
	$ar = array(	3 => 60*60*24,
					5 => 60*60*24*7,
				   10 => 60*60*24*30 
	);
	$count = count($unj);
	foreach($ar as $k => $v) {
		$offs = $black ? 2*$k - 1 : $k - 1;
		
		if( $count >= $offs ) {
			$ar[$k] = $unj[$count - $offs][3] + $v;
			}
		else unset( $ar[$k] );
	}
	return empty($ar) ? time()+60*60*24*60 : max($ar);
}

if(isset($_POST['kills']) && trim($_POST['kills']) !== '')
{
	$offset = 1; //Negative offset between server time and CET (not CEST), 1 if server uses GMT, -1 if server uses EET
	$c_total = 0;
	$c_ok = 0;
	$c_unj = 0;
	$a_r = 0;
	$a_b = 0;
	$s_a = 0;
	
	$s_u = 0;
	$red_end = 0;
	$black_end = 0;
	$red_offs = 0;
	$l2_red = 0;
	$l2_ban = 0;
	$unjusts = array();
	$summertime = is_CEST($offset);
	if($summertime) $offset+=1;
	$now = time() + ($offset*3600);
	$effect = array(0 => '',
					1 => 'got RS (3/day)',
					2 => 'got RS (5/week)',
					3 => 'got RS (10/month)',
					4 => 'renewed RS (3/day)',
					5 => 'renewed RS (5/week)',
					6 => 'renewed RS (10/month)',
					7 => 'got Black (6/day)',
					8 => 'got Black (10/week)',
					9 => 'got Black (20/month)',
					10 => 'got Black');
	

	$input =& $_POST['kills'];
	$input = trim(stripslashes($input));
	if(substr($input,-1) !== "\n") $input.="\n";
	//error_reporting(E_ALL | E_WARNING | E_NOTICE);
	//ini_set("display_errors",1);
	//preg_match_all('~([A-Za-z]{3}[\\t\\s]\\d\\d 20[0-2][0-9],[\\t\\s]+\\d\\d:\\d\\d:\\d\\d CES?T)[\\t\\s]+Killed ([A-Za-z\\'\\-\\s\\.\\u00e4\\u00c4\\u00f6\\u00d6\\u00fc\\u00dc]*) at Level (\\d{1,3})[\\t\\s]+([A-Za-z][A-Za-z\\s]*[A-Za-z])[\\t\\s]*\\r?(\\n|$)~',$input,$kills);
	preg_match_all("~([A-Za-z]{3} \d\d 20[0-2][0-9], \d\d:\d\d:\d\d CES?T)[\t ]+Killed ([A-Za-zÄÖÜäöü\'\- \.]*) at Level (\d{1,3})[\t ]+(with an? ([A-Za-z][A-Za-z\t ]+))?(ok|unjustified|war related|assisted)[\t ]*\r?\n~",$input,$kills);
	unset($input);
	unset($kills[0]);
	//echo "<pre><code>"; var_dump($kills); echo "</code></pre>"; 
	$c_total = 0;//count($kills[1]);
	
	$c_war = 0;
	$c_ok = 0;
	$c_ass = 0;
	$s_ass = 0;
	$s_w = 0;
	$s_j = 0;
	
	foreach ($kills[1] as $k => $v) {
		$c_total++;
		if($kills[6][$k] === 'ok') {
			$c_ok++;
			$s_j+=$kills[3][$k];
			unset($kills[1][$k]);
			unset($kills[2][$k]);
			unset($kills[3][$k]);
			unset($kills[4][$k]);
			unset($kills[5][$k]);
			unset($kills[6][$k]);
		}
		elseif($kills[6][$k] === 'assisted') {
			$c_ass++;
			$s_ass+=$kills[3][$k];
			unset($kills[1][$k]);
			unset($kills[2][$k]);
			unset($kills[3][$k]);
			unset($kills[4][$k]);
			unset($kills[5][$k]);
			unset($kills[6][$k]);
		}
		elseif($kills[6][$k] === 'war related') {
			$c_war++;
			$s_w+=$kills[3][$k];
			unset($kills[1][$k]);
			unset($kills[2][$k]);
			unset($kills[3][$k]);
			unset($kills[4][$k]);
			unset($kills[5][$k]);
			unset($kills[6][$k]);
		}
		elseif($kills[6][$k] === 'unjustified') {
			$c_unj++;
			$s_u+=$kills[3][$k];
			if ($summertime) {
				$kills[8][$k] = substr($kills[1][$k],22,3)!=='CET' ? strtotime(substr($kills[1][$k],0,22)) : (strtotime(substr($kills[1][$k],0,22))+3600);
			} else {
				$kills[8][$k] = substr($kills[1][$k],22,3)!=='CET' ? (strtotime(substr($kills[1][$k],0,22))-3600) : strtotime(substr($kills[1][$k],0,22));
			}
			unset($kills[4][$k]);
			unset($kills[5][$k]);
		}
		else {
			$c_total--;
			unset($kills[1][$k]);
			unset($kills[2][$k]);
			unset($kills[3][$k]);
			unset($kills[4][$k]);
			unset($kills[5][$k]);
			unset($kills[6][$k]);
		}
	}
	//$c_ok = $c_total - $c_unj;
	$s_a = $s_u + $s_j + $s_w + $s_ass;
	if(!empty($kills[8])) asort($kills[8]);
	if(!empty($kills[8])) foreach($kills[8] as $k => $v) {
		$unjusts[]=array($kills[1][$k],$kills[2][$k],$kills[3][$k],$kills[8][$k],0);	
	}
	
	unset($kills);
	
	for($i=2;$i<count($unjusts);$i++) {
		if($red_end !== 0 && $red_end <= $unjusts[$i][3]) $red_end = 0;
		check(3,$i,$unjusts,$red_end,$red_offs) or check(5,$i,$unjusts,$red_end,$red_offs) or check(10,$i,$unjusts,$red_end,$red_offs);
		if($unjusts[$i][4]!==0) {
			check(6,$i,$unjusts,$red_end,$red_offs,true) or check(10,$i,$unjusts,$red_end,$red_offs,true) or check(20,$i,$unjusts,$red_end,$red_offs,true);
		}
	}
	
	$temp = $unjusts;
	$running = true;
	do {
		$temp []= array(true,true,true,$now,0);
		if(check(3,count($temp)-1,$temp,$red_end,$red_offs,false,true) or check(5,count($temp)-1,$temp,$red_end,$red_offs,false,true) or check(10,count($temp)-1,$temp,$red_end,$red_offs,false,true)) {
			$running = false;
			break;
		}
		else $a_r++;
	}while($running);
	$temp = $unjusts;
	$running = true;
	do {
		$temp []= array(true,true,true,$now,0);
		if(check(6,count($temp)-1,$temp,$red_end,$red_offs,true,true) or check(10,count($temp)-1,$temp,$red_end,$red_offs,true,true) or check(20,count($temp)-1,$temp,$red_end,$red_offs,true,true)) {
			$running = false;
			break;
		}
		else $a_b++;
	}while($running);
	$temp = null;
	
	
	if($c_unj > 0) $avgg_u = floor($s_u / $c_unj); else $avgg_u = 0;
	if($c_ok > 0 ) $avgg_j = floor($s_j / $c_ok); else $avgg_j = 0;
	if($c_war > 0 ) $avgg_w = floor($s_w / $c_war); else $avgg_w = 0;
	if($c_ass > 0) $avgg_ass = floor($s_ass / $c_ass); else $avgg_ass = 0;
	if($c_total > 0) $avgg_a = floor(($s_u+$s_j+$s_w+$s_ass) / $c_total); else $avgg_a = 0;
	
	$black_end = $black_end <= $now ? 0 : $black_end - $now;
	$red_end = $red_end <= $now ? 0 : $red_end - $now;
	$output .= "<p>";
	if($black_end !== 0) {
		$output .= "Skull :: <b class='wrn'>BLACK</b>, expiring in <b>".delta($black_end)."</b>.<br /><br />";
	}
	elseif($red_end !== 0) {
		$output .= "Skull :: <b class='wrn'>RED</b>, expiring in <b>".delta($red_end)."</b>.<br /><br />";
	} else $output .= "Skull :: <b>None</b>.<br /><br />";
	$output .= "Unjusts left without ".($red_end===0?'taking':'renewing')." Red Skull :: <b>".($black_end===0?($a_r==0?"0 - next in ".delta(timeleft($unjusts)-time()):$a_r):"0 - Black Skulls can't take unjusts.")."</b><br />";
	$output .= "Unjusts left without taking Black Skull :: <b>".($black_end===0?($a_b==0?"0 - next in ".delta(timeleft($unjusts,true)-time()):$a_b):"0 - Black Skulls can't take unjusts.")."</b></p><br />";
	
	$output .= "<table border='0' style='width:600px;text-align:center;margin:0 auto;'><tr><th align='left'>Summary::</th><th>Count</th><th>Average Level</th><th>Total Level</th></tr>".
			   "<tr class='wrn' style='font-weight:bold;'><td style='text-align:left;'>Unjustified</td><td>$c_unj</td><td>$avgg_u</td><td>$s_u</td></tr>".
			   "<tr style='font-weight:bold;color:#F80;'><td style='text-align:left;'>War related</td><td>$c_war</td><td>$avgg_w</td><td>$s_w</td></tr>".
			   "<tr style='font-weight:bold;color:#FF0;'><td style='text-align:left;'>Assisted</td><td>$c_ass</td><td>$avgg_ass</td><td>$s_ass</td></tr>".
			   "<tr style='font-weight:bold;color:#0C0;'><td style='text-align:left;'>Ok</td><td>$c_ok</td><td>$avgg_j</td><td>$s_j</td></tr>".
			   "<tr style='font-weight:bold;'><td style='text-align:left;'>Total</td><td>$c_total</td><td>$avgg_a</td><td>$s_a</td></tr></table><br /";
	
	if($c_unj>0) {
		$output .= "<br /><br /><h2 class=\"wrn ac\">Unjusts</h2></span><table border=\"0\" cellpadding=\"2\" cellspacing=\"1\" style=\"width:600px;margin:0 auto;\">";
		for($i=count($unjusts)-1;$i>=0;$i--) {
			$output .= "<tr><td>".$unjusts[$i][0]."</td><td>".$unjusts[$i][1]."</td><td class=\"ar\">".$unjusts[$i][2]."</td><td class=\"ac\">".$effect[$unjusts[$i][4]]."</td></tr>";
		}
		$output .= "</table>";
	}
}
else $output .= "This script calculates how many unjustified kills you've got left before taking/renewing red/black skull.<br />
It also shows when your red/black skull expires and explains which unjusts caused or renewed it.<br /><br />
Copy and paste <span class=\"wrn\">ALL</span> your frags from your Tibia.com profile into the box above and click submit.<br /><br />
<strong>Updates:<br />
Sep 11 2009 - Added support for 8.5 and black skulls.<br />
Jul 6 2010 - Now counting war mode frags separately.<br />
Oct 10 2010 - Added summon kills, Umlauts in char names, assisted kills, changed stats table layout.<br />
Oct 17 2010 - Added time left until next unjust (if none possible just now).</strong>
";

mkhead();
echo $output;
mkfoot();
?>