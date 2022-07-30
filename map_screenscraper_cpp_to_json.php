<?php
/**
* Parses the src/screenscraper.cpp file building a list of platforms with thie r corrisponding screenscraper id
* It then writes a screenscraper.json to replace the existing one for the JSON backed list+options instead of CPP hardcoded list
*/
$platforms = [];
preg_match_all('/if\s*\(\s*platform\s*==\s*"(?P<platform>[^"]*)"\s*\)\s*\{\n\s*return\s+"(?P<id>[^"]*)"\s*;/msuU', file_get_contents(__DIR__.'/src/screenscraper.cpp'), $matches);
foreach ($matches['platform'] as $idx => $platform) {
    $id = $matches['id'][$idx];
    $platforms[] = json_encode(['name' => $platform, 'id' => is_numeric($id) ? (int)$id : $id], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
usort($platforms, function ($a, $b) {
    return json_decode($a, true)['name'] <=> json_decode($b, true)['name'];
});
$json = "{\n\t\"platforms\":\n\t[\n\t\t".implode(",\n\t\t", $platforms)."\n\t]\n}";
echo $json;
file_put_contents('screenscraper_new.json', $json);
