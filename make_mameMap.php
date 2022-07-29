<?php
preg_match_all('/^(\S+)\s+"(.*)"$/mu', str_replace("\r\n", "\n", `/mnt/f/Consoles/MAME/mame.exe -listfull`), $matches);
$lines = '';
foreach ($matches[1] as $idx => $short) {
	$long = $matches[2][$idx];
	$lines .= '"'.$short.'";"'.$long.'"'."\n";
}
file_put_contents('mameMap.csv', $lines);

