<?php

namespace App\Tests;

use \Imagick;

class ImagickTest implements Test
{
    public function execute(): Result
    {
        $success = true;
        $message = '';
        try {
            $limits = [
                'RESOURCETYPE_MEMORY' => $this->human_filesize(Imagick::getResourceLimit(Imagick::RESOURCETYPE_MEMORY)),
                'RESOURCETYPE_MAP' => $this->human_filesize(Imagick::getResourceLimit(Imagick::RESOURCETYPE_MAP)),
                'RESOURCETYPE_AREA' => number_format(Imagick::getResourceLimit(Imagick::RESOURCETYPE_AREA)),
                'RESOURCETYPE_DISK' => Imagick::getResourceLimit(Imagick::RESOURCETYPE_DISK),
                'RESOURCETYPE_FILE' => Imagick::getResourceLimit(Imagick::RESOURCETYPE_FILE),
            ];

            $message .=  '<pre>';
            $message .= print_r($limits, true);
            $message .=  '</pre>';

            $loops = ($_GET['loops']) ?? 1;
            $useXLarge = ($_GET['xlarge']) ?? 0;
            $xLargeLimit = 10 * 1024 * 1024;

            $paths = glob('imagick/*');
            $created = [];

            foreach (range(1, $loops) as $c) {

                $message .= '<hr>';

                foreach ($paths as $path) {

                    if (is_dir($path)) continue;

                    $bytes = filesize($path);

                    if ($bytes > $xLargeLimit && !$useXLarge) {
                        continue;
                    }

                    $start = microtime(true);
                    $i = new Imagick($path);

                    try {
                        $new = 'imagick/tmp/' . uniqid('img.', true);
                        $created[] = $new;
                        $i->thumbnailImage(1000, 1600, true, false);
                        $i->writeImage(__DIR__ . '/../../public/' . $new . '.webp');
                    } catch(\Exception $e) {
                        $success = false;
                        $message .= $e->getMessage();
                    }

                    $message .= sprintf(
                        '%s <b>%s</b> done in %f ms<br>',
                        $path,
                        $this->human_filesize($bytes),
                        (microtime(true) - $start)
                    );

                }
            }

            foreach ($created as $ii) {
                $message .= '<img src="' . $ii . '.webp">';
            }
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return new Result($success, $message);
    }

    private function human_filesize($bytes, int $decimals = 2): string
    {
        $size = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    public function appType(): string
    {
        return self::APP_UNI;
    }
}
