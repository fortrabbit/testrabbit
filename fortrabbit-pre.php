<?php
// Delete vendor folder to get a clean install
if (is_dir("vendor")) {
    shell_exec("rm -rf vendor");
}

// Run composer with our own options
$composerExtraOptions = "--no-dev --no-scripts -vvv";
echo shell_exec(
    // these are the default arguments used by fortrabbits automated composer run
    "php /usr/local/bin/composer install --prefer-dist --no-interaction --no-ansi --no-progress " .
        $composerExtraOptions
);

// Deleting composer.lock will disable fortrabbit's default composer run
unlink("composer.lock");
