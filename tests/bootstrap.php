<?php
define('FIXTURES_URL', 'https://github.com/wapmorgan/MediaFile/releases/download/0.0.1/fixtures.zip');
define('FIXTURES_DIR', __DIR__.'/../fixtures');

require_once __DIR__.'/../vendor/autoload.php';

// download fixtures if not present
if (!is_dir(FIXTURES_DIR)) {
	$tmp_file = tempnam(sys_get_temp_dir(), 'mediafile');

	echo 'Downloading fixtures ... be patient. It can take few minutes to complete.'.PHP_EOL;
	if (copy(FIXTURES_URL, $tmp_file) !== true)
		throw new Exception('Can\'t download fixtures ('.FIXTURES_URL.')');

	echo 'Downloaded fixtures: '.filesize($tmp_file).' byte(s)'.PHP_EOL;

	$zip = new ZipArchive();
	if ($zip->open($tmp_file) !== true)
		throw new Exception('Can\'t open downloaded fixtures as ZIP archive ('.$tmp_file.')');

	if ($zip->extractTo(FIXTURES_DIR) !== true)
		throw new Exception('Can\'t extract downloaded fixtures as ZIP archive ('.$tmp_file.')');

	echo 'Extracted fixtures: '.$zip->numFiles.' file(s)'.PHP_EOL;

	$zip->close();

	unlink($tmp_file);
}
