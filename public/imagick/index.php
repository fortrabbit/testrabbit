<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include('Imagick.php');

function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

if (isset($_GET['memory'])) {
	$memory = (int) $_GET['memory'] * 1024 * 1024;
	Imagick::setResourceLimit(Imagick::RESOURCETYPE_MEMORY, $memory);
	Imagick::setResourceLimit(Imagick::RESOURCETYPE_MAP, $memory*2);
	Imagick::setResourceLimit(Imagick::RESOURCETYPE_AREA, $memory*2);

}


$limits = [
	'RESOURCETYPE_MEMORY' => human_filesize(Imagick::getResourceLimit(Imagick::RESOURCETYPE_MEMORY)),
	'RESOURCETYPE_MAP' => human_filesize(Imagick::getResourceLimit(Imagick::RESOURCETYPE_MAP)),
	'RESOURCETYPE_AREA' => number_format(Imagick::getResourceLimit(Imagick::RESOURCETYPE_AREA)),
	'RESOURCETYPE_DISK' => Imagick::getResourceLimit(Imagick::RESOURCETYPE_DISK),
	'RESOURCETYPE_FILE' => Imagick::getResourceLimit(Imagick::RESOURCETYPE_FILE),
];

echo '<pre>';
print_r($limits);
echo '</pre>';

$loops = ($_GET['loops']) ?? 1;
$useXLarge = ($_GET['xlarge']) ?? 0;
$xLargeLimit = 10 * 1024 * 1024;

// explicit file
if (isset($_GET['file'])) {
	$file = $_GET['file'];
	$paths = ["src/$file"];
} 
// dir scan
else {
	$paths = glob('src/*');
}

$created = [];

foreach (range(1, $loops) as $c) {

	echo '<hr>';

	foreach ($paths as $path) {

		$bytes = filesize($path);

		if ($bytes > $xLargeLimit && !$useXLarge) {
			continue;
		}
		
		$start = microtime(true);
		$i = new Craft2\Imagick\Imagick($path);


		try {
			$new = 'tmp/' . uniqid('img.', true);
			$created[] = $new;
			$i->thumbnailImage(250, 400, true, false);
			$i->writeImage(__DIR__ . '/' . $new);
		} catch(\Exception $e) {
			echo $e->getMessage();
		} 

		echo sprintf(
				'%s <b>%s</b> done in %f ms<br>', 
				$path,
				(string) human_filesize($bytes),
				(microtime(true) - $start)
		);

	}
}

foreach ($created as $ii) {
	echo '<img src="'.$ii.'">';
}

phpinfo();



//header("Content-Type: image/jpg");
//echo $i->getImageBlob();