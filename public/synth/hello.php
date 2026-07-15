<?php

/**
 * /synth/hello.php — pure PHP-path constant (Apache→FPM, prepend, opcache).
 * NEW−OLD TTFB here IS the platform overhead. Deliberately no includes, no
 * JSON: nothing to measure inside, the wrapper is the measurement.
 */

echo 'ok';
