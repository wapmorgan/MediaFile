<?php

use wapmorgan\MediaFile\Exceptions\FileAccessException;
use wapmorgan\MediaFile\MediaFile;
require __DIR__.'/vendor/autoload.php';
define('REPEATITIONS', 100);

$files = glob(__DIR__.'/fixtures/{video,audio}/*', GLOB_BRACE);

class_exists('getID3');
$id3 = new getID3;
class_exists('wapmorgan\MediaFile\MediaFile');

$times = array(
    'mediafile' => array(),
    'getid3' => array(),
);

echo 'Repeatitions: '.REPEATITIONS.PHP_EOL;

echo sprintf('%20s | %10s | %10s | %10s', 'File', 'getID3', 'MediaFile', 'Speed gain').PHP_EOL;

foreach ($files as $file) {
    $start = microtime(true);
    try {
        for ($i = 0; $i < REPEATITIONS; $i++) {
            $info = MediaFile::open($file);
        }
        $times['mediafile'][$file] = microtime(true) - $start;
    } catch (FileAccessException $e) {
        continue;
    }

    $start = microtime(true);
    for ($i = 0; $i < REPEATITIONS; $i++) {
        $info = $id3->analyze($file);
    }
    $times['getid3'][$file] = microtime(true) - $start;

    echo sprintf('%20s | %10.3f | %10.3f | %5.2fx', basename($file), $times['getid3'][$file], $times['mediafile'][$file], $times['getid3'][$file] / $times['mediafile'][$file]).PHP_EOL;
}
// var_dump($times);
