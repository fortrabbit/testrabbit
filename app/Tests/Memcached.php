<?php

namespace App\Tests;

class Memcached implements Test
{
    public function execute(): Result
    {
        $success = false;
        $message = '';
        $memcachedClass = '\Memcached';
        if (class_exists($memcachedClass)) {
            $mc = new $memcachedClass;
            foreach (['memcachecluster.frbit.com'] as $e) {
                try {
                    $port = 11211;
                    $host = $e;
                    $mc->addServer($host, $port);
                    $mc->set('message', 'Successfully retrieved message! ✅');
                    if ($hola = $mc->get('message')) {
                        $message .= "Memcached; message: $hola\n";
                        $success = true;
                    } else {
                        $message .= "Memcached error; no response from $host:$port\n";
                    }
                } catch (\Exception $e) {
                    $message .= "error from memcached; {$e->getMessage()}\n";
                }
            }
        } else {
            $message .= sprintf('class not found: %s', $memcachedClass);
        }

        return new Result($success, $message);
    }

    public function appType(): string
    {
        return self::APP_PRO;
    }
}
