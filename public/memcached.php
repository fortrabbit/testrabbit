<?php header('Content-type: text/plain'); ?>
<?php
require __DIR__.'/../vendor/autoload.php';

$memcachedClass = '\Memcached';
if (class_exists($memcachedClass)) {
    $mc = new Memcached();
    foreach (['memcachecluster.frbit.com'] as $e) {
        try {
            $port = 11211;
            $host = $e;
            $mc->addServer($host, $port);
            $mc->set('message', 'Successfully retrieved message! ✅');
            if ($hola = $mc->get('message')) {
                echo "Memcached; message: $hola\n";
            } else {
                echo "Memcached error; no response from $host:$port\n";
            }
        } catch (\Exception $e) {
            echo "error from memcached; {$e->getMessage()}\n";
        }
    }
} else {
    throw new ErrorException(sprintf('class not found: %s', $memcachedClass));
}
