<?php
/**
* Parses the src/platform.cpp file building a list of platforms and the scrapers,
* file masks, and aliases for each.  It then writes a platforms.json to replace the
* existing one for the JSON backed list+options instead of CPP hardcoded list+options
*/
$platforms = [];
$platformIdx = [];
preg_match_all('/^(QString|QStringList) Platform::get(?P<list>[^\(]*)\([^\)]*\)(?P<data>.*)^}/msuU', file_get_contents(__DIR__.'/../src/platform.cpp'), $matches);
foreach ($matches['list'] as $idx => $list) {
    $list = strtolower($list);
    $data = $matches['data'][$idx];
    // Platforms Scrapers Formats DefaultScraper Aliases
    if ($list == 'platforms') {
        preg_match_all('/^.*\s?\w[^\.]+\.append\("(?P<data>[^"]*)"\);/muU', $data, $listMatches);
        foreach ($listMatches['data'] as $platform) {
            if (!isset($platformIdx[$platform])) {
                $idxPlat = count($platformIdx);
                $platformIdx[$platform] = $idxPlat;
                $platforms[$idxPlat] = ['name' => $platform, 'scrapers' => [], 'formats' => [], 'aliases' => []];
            } else {
                $idxPlat = $platformIdx[$platform];
            }
        }
    } elseif (in_array($list, ['scrapers', 'formats', 'aliases'])) {
        preg_match_all('/platform == "(?P<platform>[^"]*)"\) \{(?P<data>[^\}]*)\}/msu', $data, $listMatches);
        foreach ($listMatches['platform'] as $idxList => $platform) {
            if (!isset($platformIdx[$platform])) {
                $idxPlat = count($platformIdx);
                $platformIdx[$platform] = $idxPlat;
                $platforms[$idxPlat] = ['name' => $platform, 'scrapers' => [], 'formats' => [], 'aliases' => []];
            } else {
                $idxPlat = $platformIdx[$platform];
            }
            $platData = $listMatches['data'][$idxList];
            preg_match_all('/^.*\s?\w[^\.]+\.append\("(?P<data>[^"]*)"\);/muU', $platData, $platMatches);
            foreach ($platMatches['data'] as $idxValue => $value) {
                if ($list == 'formats') {
                    if ($value != '') {
                        $values = explode(' ', trim($value));
                        foreach ($values as $value)
                            $platforms[$idxPlat][$list][] = $value;
                    }
                } else {
                    $platforms[$idxPlat][$list][] = $value;
                }
            }
        }
    } else {
        // do nothing
    }
}
$json = [];
foreach ($platforms as $platform)
    $json[] = json_encode($platform,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$json = "{\n\t\"platforms\":\n\t[\n\t\t".str_replace(['","', '", "scrapers"'], ['", "', '","scrapers"'], implode(",\n\t\t", $json))."\n\t]\n}";
echo $json;
file_put_contents(__DIR__.'/../platforms_new.json', $json);
