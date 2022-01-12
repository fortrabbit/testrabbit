<?php

namespace App\Tests;

class Extension implements Test
{
    public function execute(): Result
    {
        $message = '';
        $success = false;
        list($missing, $expected, $iniExpected) = $this->discoverMissingExtensions();

        if (count($missing)) {
            $oops = [];
            $extNames = [];
            foreach (get_loaded_extensions() as $e) {
                $extNames[strtolower($e)] = $e;
            }

            list($settings, $soFiles) = $this->generateIni();

            $possibleDynamicMatches = [];
            foreach (array_keys($missing) as $ext) {
                $v = phpversion($extNames[$ext] ?? "");
                $oops[] = "$ext - " . ($v ? "available version: $v" : "not loaded");

                foreach ($soFiles as $path) {
                    if (false !== strpos($path, $ext)) {
                        $possibleDynamicMatches[] = $path;
                    }
                }
            }

            $message .= sprintf("PHP: %s - MISSING EXTENSIONS:\n  %s\n", phpversion(), join("\n  ", $oops));
            if (count($possibleDynamicMatches)) {
                $message .= sprintf("PHP: %s - possible matches:\n  %s\n", phpversion(), join("\n  ", $possibleDynamicMatches));
            }

            if (count($iniExpected)) {
                $disabled = [];
                foreach ($iniExpected as $ext => $file) {
                    if (in_array($ext, $missing)) {
                        $disabled[] = "$ext - $file";
                    }
                }
                if (count($disabled)) {
                    $message .= sprintf("PHP: %s - EXPECTED EXTENSIONS:\n  %s\n", phpversion(), join("\n  ", $disabled));
                }
            }

        } else {
            $success = true;
            $message .= sprintf("PHP: %s - Required extensions have been detected, all is good! ✅\n%s\n",
                phpversion(),
                wordwrap(join(' ', array_keys($expected)), 74)
            );
        }

        return new Result($success, $message);
    }

    private function discoverMissingExtensions(): array
    {
        $removed_in['7.4.0'] = ['wddx', 'phalcon'];

        $expected = [
            'bcmath' => 1,
            'blackfire' => 1,
            'calendar' => 1,
            'ctype' => 1,
            'curl' => 1,
            'dba' => 1,
            'exif' => 1,
            'fpm' => 1,
            'ftp' => 1,
            'gd' => 1,
            'geoip' => 1,
            'gmp' => 1,
            'gnupg' => 1,
            'igbinary' => 1,
            'imagick' => 1,
            'imap' => 1,
            'intl' => 1,
            'ldap' => 1,
            'mbstring' => 1,
            'memcached' => 1,
            'mongodb' => 1,
            'newrelic' => 1,
            'oauth' => 1,
            'opcache' => 1,
            'openssl' => 1,
            'pcntl' => 1,
            'pdo' => 1,
            'pgsql' => 1,
            'phalcon' => 1,
            'phar' => 1,
            'posix' => 1,
            'redis' => 1,
            'shmop' => 1,
            'soap' => 1,
            'sockets' => 1,
            'sqlite3' => 1,
            'ssh2' => 1,
            'sodium'=> 1,
            'sysvmsg' => 1,
            'sysvsem' => 1,
            'sysvshm' => 1,
            'tidy' => 1,
            'wddx' => 1,
            'xsl' => 1,
            'yaml' => 1,
            'zip' => 1,
        ];

        foreach ($removed_in as $v => $extensions) {
            if (version_compare($v, phpversion(), 'le')) {
                foreach ($extensions as $deprecated) {
                    unset($expected[$deprecated]);
                }
            }
        }

        $loaded = [];
        foreach (get_loaded_extensions() as $i => $e) {
            $loaded[strtolower($e)] = true;
        }

        if (php_sapi_name() == 'fpm-fcgi') {
            unset($expected['pcntl']);
        }

        if (function_exists('opcache_get_status')) {
            $loaded['opcache'] = 1; // Zend modules
        }

        $haveFpmBin = array_sum(
            [ // Detect fpm as a module (if php-fpm binary is installed)
                is_file(dirname(realpath(@$_SERVER['_']))) . '/../bin/php-fpm' ? 1 : 0,
                is_file(dirname(realpath(@$_SERVER['_']))) . '/../sbin/php-fpm' ? 1 : 0,
            ]
        );
        if ($haveFpmBin) {
            $loaded['fpm'] = 1;
        }

        $confirmed = array_intersect_key($expected, $loaded);
        $missing = array_diff_key($expected, $confirmed);
        ksort($confirmed);
        ksort($missing);

        list ($ini, ) = $this->findExpectedByInis();

        return [$missing, $expected, $ini];
    }


    /**
     * Enable all extensions
     *
     * @param string $version
     * @param string $soPattern
     * @param array $zend
     * @return array [ list or extensions, sofiles[] ]
     */
    private function generateIni(
        string $version = '7.4',
        string $soPattern = '/opt/php/x.y/lib/php/????????/*.so',
        array $zend = [
            'opcache',
            'xdebug'
        ]
    ): array {
        $list = [];
        $soPattern = str_replace('x.y', $version, $soPattern);
        $soFiles = [];
        foreach (glob($soPattern) as $ini) {
            if (!is_file($ini) || is_link($ini)) {
                continue;
            }
            $soFiles[] = realpath($ini);
            $re = sprintf("/^(%s)$/", join('|', $zend));
            $base = basename($ini, '.so');
            if (preg_match($re, $base)) {
                $list[] = sprintf("zend_extension=%s", realpath($ini));
            } else {
                $list[] = "extension=$base.so";
            }
        }
        sort($list);

        return [ join(PHP_EOL, $list) ?: '', $soFiles ];
    }

    private function findExpectedByInis(): array {
        $enabled = [];
        $files =  array_map('trim', array_merge([php_ini_loaded_file()], explode(",", php_ini_scanned_files())));

        foreach ($files as $i => $path) {
            $ini = is_readable($path) ? file_get_contents($path) : '';
            preg_match_all('/^(zend_)?extension\s*=\s*[\'"]*([\w.\-]+)[\'"]*\s*/', $ini, $allMatches, PREG_SET_ORDER );

            foreach ($allMatches as $matches) {
                if (count($matches) === 0) {
                    continue;
                }
                if ($matches[2] === 'modulename') {
                    continue;
                }
                if ($matches[1] === 'zend_') {
                    $enabled[basename($matches[2], '.so')] = "zend_extension $path";
                }
                if (empty($matches[1])) {
                    $enabled[basename($matches[2], '.so')] = "extension $path";
                }
            }
        }
        return [$enabled, $files];
    }
}
