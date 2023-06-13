<?php

namespace App\Tests;

use \Imagick;

class ImagickTest implements Test
{
    public function execute(): Result
    {
        $success = true;
        $message = '';
        $errorLog = '';
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
            //$message .= print_r(Imagick::queryFormats());
            $message .=  '</pre>';

            $loops = ($_GET['loops']) ?? 1;
            $useXLarge = ($_GET['xlarge']) ?? 0;
            $xLargeLimit = 10 * 1024 * 1024;

            $paths = glob('imagick/*');
            $errorLog .= join("\n", $paths);
            $created = [];
            $supportedSourceFormats = ['jpeg','jpg','png','webp'];
            $targetFormats = ['jpeg','webp'];
            if (config('fortrabbit.platform') == PLATFORM_NEW) {
                $supportedSourceFormats = ['jpeg','jpg','png','webp','avif','heic'];
                $targetFormats = ['jpeg','webp','avif','heic'];
            }

            foreach (range(1, $loops) as $c) {

                $message .= '<hr>';

                foreach ($paths as $path) {
                    if (is_dir($path)) continue;

                    $bytes = filesize($path);
                    if ($bytes > $xLargeLimit && !$useXLarge) {
                        continue;
                    }

                    $sourceFormat = substr($path, strrpos($path, '.')+1);
                    if (!in_array($sourceFormat, $supportedSourceFormats)) {
                        $message .= $path . ' ' . $sourceFormat . ' format skipped<br>';
                        continue;
                    }

                    $targetFormat = $targetFormats[array_rand($targetFormats)];
                    $start = microtime(true);
                    $actualFormat = '???';

                    try {
                        $img = new Imagick($path);
                        $new = config('imagick.tempLocation') . uniqid('img.', true) . '.' . $targetFormat;
                        $created[] = $new;
                        $img->thumbnailImage(1000, 1600, true, false);
                        $fullPath = __DIR__ . '/../../public/' . $new;
                        $img->writeImage($fullPath);
                        $img->destroy();

                        $check = new Imagick();
                        $check->pingImage($fullPath);
                        $actualFormat = $check->getImageFormat();
                        $check->destroy();

                        if ($actualFormat != strtoupper($targetFormat)) {
                            $success = false;
                            $message .= 'format mismatch! ';
                        }
                    } catch(\Exception $e) {
                        $success = false;
                        $message .= $e->getMessage();
                    }

                    $message .= sprintf(
                        '%s <b>%s</b> to <b>%s</b> is actually <b>%s</b> done in %f ms<br>',
                        $path,
                        $this->human_filesize($bytes),
                        $targetFormat,
                        $actualFormat,
                        (microtime(true) - $start)
                    );

                }
            }
            // Show images
            $message .= '<div style="display:flex">';
            foreach ($created as $ii) {
                $message .= '<li style="height:40vh;flex-grow:1;list-style-type:none">';
                $message .= '<img src="' . $ii . '" style="object-fit:cover;max-height:100%;mix-width:100%;vertical-align:bottom">';
                $message .= '</li>';
            }
            $message .= '</div>';
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . $errorLog;
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
