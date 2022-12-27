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

            $paths = glob('imagick/*.jpg');
            $created = [];

                $message .= '<hr>';

                foreach ($paths as $path) {

                    if (is_dir($path)) continue;

                    $start = microtime(true);

                    try {

                        $im = imagecreatefromjpeg($path);
                        $im = imagescale( $im, 1000, 1600 );
                        $bytes = filesize($path);

                        $new = config('gd.tempLocation') . uniqid('img.', true) . '.webp';
                        $success = imagewebp($im, $new);
                        $created[] = $new;

                    } catch(\Exception $e) {
                        $message .= $e->getMessage();
                    }

                    $message .= sprintf(
                        '%s <b>%s</b> done in %f ms<br>',
                        $path,
                        $this->human_filesize($bytes),
                        (microtime(true) - $start)
                    );

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
