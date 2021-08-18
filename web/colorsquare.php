<?php

$colormap = array(
  'none'    => 0xffffff,
  'white'   => 0xffffff,
  'silver'  => 0xc0c0c0,
  'gray'    => 0x888888,
  'grey'    => 0x888888,
  'darkgray' => 0x444444,
  'darkgrey' => 0x444444,
  'black'   => 0x000000,
  'red'     => 0xff0000,
  'darkred' => 0x800000,
  'darkblue' => 0x000090,
  'skyblue' => 0x88CEFA,
  'maroon'  => 0x880000,
  'yellow'  => 0xffff00,
  'lemon'   => 0xF4D81C,
  'olive'   => 0x888800,
  'limegreen'    => 0x48B868,
  'lime'    => 0x48B868,
  'green'   => 0x008800,
  'forrestgreen' => 0x295E06,
  'aqua'    => 0x00ffff,
  'teal'    => 0x008888,
  'blue'    => 0x0000ff,
  'navy'    => 0x203068,
  'fuchsia' => 0xE870A8,
  'pink'    => 0xFAAFBE,
  'purple'  => 0x603890,
  'violet'  => 0xD0B0F0,
  'darkpurple' => 0x461B7E,
  'orange'  => 0xFFAA00,
  'tangerine'  => 0xFFAA00,
  'rust'  => 0xFFAA00,
  'gold'    => 0xEAC117,
  'tan'    => 0xEAC117,  // fixme
  'royal'   => 0x4169E1,
  'royalblue'  => 0x4169E1,
  'watermelon'  => 0xF84840,
  'neonorange'  => 0xFF4105,
  'neonyellow'  => 0xFFFF00,
  'neongreen'  => 0x6FFF00,
  'neonpink'  => 0xFF00FF,
  'hotpink'  => 0xFF00FF,
  'neonblue'  => 0x4D4DFF,
  'neonred'  => 0xFE0001,
  'burgundy'  => 0x902040,
  'huntergreen'  => 0x008000,
  'hunter' => 0x306860,
  'kellygreen'  => 0x00CC00,
  'mintgreen'  => 0xB7FFB7,
  'darkgreen'  => 0x006400,
  'lightgreen'  => 0x90E090,
  'columbiablue'  => 0x75B2DD,
  'champagne' => 0xDDCCAA,
  'mint' => 0xB0C8C0,
  'columbia' => 0xB0D8F0,
  'turquoise' => 0x00B0D8,
  'charcoal' => 0x484860,
  'kelly' => 0x00CC50,
  'lavender' => 0x9890C8,
  'lightblue' => 0xADD8E6,
  'darkpink' => 0xFF1493,
  'cobaltblue' => 0x00B0F0,
);

$test = false;
//$test = true;
//$_GET['color1']='880088';
//$_GET['color2']='ff0000';

$xsize = 16;
$ysize = 16;

function ColorFromHex(&$im, $hex)
{
  global $test, $colormap;

  $hex2 = str_replace(' ', '', strtolower($hex));
  if (array_key_exists($hex2, $colormap)) {
    $c = $colormap[$hex2];
  }
  else {
    //$c = (int)hexdec($hex2);
    return false;
  }
  
  $r = ($c >> 16) & 0xff;
  $g = ($c >> 8) & 0xff;
  $b = ($c) & 0xff;
  
  if ($test) {
    echo "hex = $hex<br>\n";
    echo "dec = $c<br>\n";
    printf('RGB(%d,%d,%d) = %02X%02X%02X.png', $r, $g, $b, $r, $g, $b);
    echo "<br>\n";
  }
  return imagecolorallocate($im, $r, $g, $b);
}

$im = imagecreatetruecolor($xsize,$ysize);

if (isset($_GET['color1'])) {
  if (strpos($_GET['color1'], '/') !== FALSE) {
    list($hex1, $hex2) = explode('/', $_GET['color1']);
	} else {
		$hex1 = $_GET['color1'];
    $hex2 = '';
  }
	
	if ($hex1 == 'Unknown') {
		header("Location: assets/img/missingcolor.png");
		die();
	}
	
  $color1 = ColorFromHex($im, $hex1);
  if (isset($hex2)) {
    $color2 = ColorFromHex($im, $hex2);
  } else {
    $color2 = false;
  }
}
else {
  $color1 = false;
  $color2 = false;
}

$seconds_to_cache = 1 * 24 * 60 * 60; // 1 days
$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";

//$fn = sprintf('%02X%02X%02X.png', $r, $g, $b);
//header('Content-Disposition: Attachment;filename='.$fn);
if (!$test) {
  header("Content-type: image/png");
}
header("Expires: $ts");
header("Pragma: cache");
header("Cache-Control: max-age=$seconds_to_cache");

if ($color1 !== false) {
	if ($color2 !== false) {
		// 2 colors
		// make secondary color a little smaller than half
		imagefilledrectangle($im, 1,          1, $xsize-2, $ysize/2+1, $color1);
		imagefilledrectangle($im, 1, $ysize/2+2, $xsize-2, $ysize-2, $color2);
	} else {
		// 1 color
		imagefilledrectangle($im, 1, 1, $xsize-2, $ysize-2, $color1);
	}
} else {
	// no colors
	$color1 = imagecolorallocate($im, 0xFF, 0, 0);
	$color2 = imagecolorallocate($im, 0, 0xFF, 0);
	imagefilledrectangle($im, 1,          1, $xsize/2-1, $ysize-2, $color1);
	imagefilledrectangle($im, $xsize/2, 1, $xsize-2, $ysize-2, $color2);
}

if (!$test) {
	imagepng($im);
}
imagedestroy($im);

//phpinfo();
