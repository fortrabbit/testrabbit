<?php header('Content-type: text/plain'); ?>
<?php
require __DIR__.'/../vendor/autoload.php';

$memcachedClass = '\Memcached';
if (class_exists($memcachedClass)) {
    $mc = new Memcached();
    foreach (['127.0.0.1', 'memcachecluster.frbit.com'] as $e) {
        try {
            $port = 11211;
            $host = $e;
            $mc->addServer($host, $port);
            $mc->set('message', 'Hola Mundo, mucho memcached, obrigado.');
            if ($hola = $mc->get('message')) {
                echo "Memcached; message: $hola\n";
                return;
            }
            echo "Memcached error; no response from $host:$port\n";
        } catch (\Exception $e) {
            echo "error from memcached; {$e->getMessage()}\n";
        }
    }
} else {
    throw new ErrorException(sprintf('class not found: %s', $memcachedClass));
}
