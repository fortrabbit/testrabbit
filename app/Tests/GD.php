<?php

namespace App\Tests;

class GD implements Test
{
    public function execute(): Result
    {
        $success = true;
        $message = '';

        try {

            $message .=  '<pre>';
            $message .= print_r(gd_info(), true);
            $message .=  '</pre>';

            $paths = glob('imagick/*');
            $created = [];
            $message .= '<hr>';
            $formats = ['webp','jpeg'];
            if(function_exists('imageavif')) $formats[] = 'avif';

            foreach ($paths as $path) {

                if (is_dir($path)) continue;

                $start = microtime(true);

                try {

                    $bytes = filesize($path);
                    $sourceFormat = substr($path, strrpos($path, '.')+1);
                    switch ($sourceFormat) {
                        case 'jpg':
                        case 'jpeg':
                            $im = imagecreatefromjpeg($path);
                            break;
                        case 'png':
                            $im = imagecreatefrompng($path);
                            break;
                        case 'webp':
                            $im = imagecreatefromwebp($path);
                            break;
                        case 'heic':
                            $message .= $path . ' HEIC format not supported<br>';
                            continue 2;
                        case 'avif':
                            $message .= $path . ' AVIF format not supported<br>';
                            continue 2;
                        default:
                            $message .= $path . ' uknown format not supported<br>';
                            continue 2;
                    }

                    $im = imagescale( $im, 1000, 1600 );

                    $format = $formats[array_rand($formats)];
                    $new = config('gd.tempLocation') . uniqid('img.', true) . '.' . $format;
                    switch ($format) {
                        case 'jpeg':
                            $success = imagejpeg($im, $new);
                            break;
                        case 'webp':
                            $success = imagewebp($im, $new);
                            break;
                        case 'avif':
                            $success = imageavif($im, $new);
                            break;
                        default:
                            throw new \Exception('Unexpected image format');
                    }
                    $created[] = $new;

                } catch(\Exception $e) {
                    $message .= $e->getMessage();
                }

                $message .= sprintf(
                    '%s <b>%s</b> to <b>%s</b> done in %f ms<br>',
                    $path,
                    $this->human_filesize($bytes),
                    $format,
                    (microtime(true) - $start)
                );

            }

            // Show images
            $message .= '<div style="display:flex">';
            foreach ($created as $ii) {
                $message .= '<li style="height:40vh;flex-grow:1;list-style-type:none">';
                $message .= '<img src="' . $ii . '" alt="' . $ii . '" style="object-fit:cover;max-height:100%;mix-width:100%;vertical-align:bottom">';
                $message .= '</li>';
            }
            $message .= '</div>';

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
