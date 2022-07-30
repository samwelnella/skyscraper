<?php
/**
* Parses the src/platform.cpp file building a list of platforms and the scrapers, 
* file masks, and aliases for each.  It then writes a platforms.json to replace the
* existing one for the JSON backed list+options instead of CPP hardcoded list+options
*/
$platforms = [];
preg_match_all('/^(QString|QStringList) Platform::get(?P<list>[^\(]*)\([^\)]*\)(?P<data>.*)^}/msuU', file_get_contents(__DIR__.'/src/platform.cpp'), $matches);
foreach ($matches['list'] as $idx => $list) {
    $list = strtolower($list);
    $data = $matches['data'][$idx];
    // Platforms Scrapers Formats DefaultScraper Aliases
    if ($list == 'Platforms') {
        preg_match_all('/^.*\s?\w[^\.]+\.append\("(?P<data>[^"]*)"\);/muU', $data, $listMatches);
        foreach ($listMatches['data'] as $platform)
            $platforms[$platform] = ['name' => $platform];
    } elseif (in_array($list, ['scrapers', 'formats', 'aliases'])) {
        preg_match_all('/platform == "(?P<platform>[^"]*)"\) \{(?P<data>[^\}]*)\}/msu', $data, $listMatches);
        foreach ($listMatches['platform'] as $idxPlat => $platform) {
            $platforms[$platform][$list] = [];
            $platData = $listMatches['data'][$idxPlat];
            preg_match_all('/^.*\s?\w[^\.]+\.append\("(?P<data>[^"]*)"\);/muU', $platData, $platMatches);
            foreach ($platMatches['data'] as $idxValue => $value)
                $platforms[$platform][$list][] = $value;
        }
    } else {
        // do nothing
    }
}
echo json_encode($platforms, JSON_PRETTY_PRINT).PHP_EOL;
file_put_contents('platforms_new.json', json_encode($platforms, JSON_PRETTY_PRINT));
