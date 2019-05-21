<?php

print '<pre>';

# Check for sodium core plugin
if (function_exists('sodium_version_string')) {
    print 'We have libsodium in core! ' . sodium_version_string() . "\n";
} else {
    print "libsodium not found in core\n";
}

# Check for sodium pecl plugin
if (defined('SODIUM_VERSION_STRING')) {
    echo 'We have SODIUM_VERSION_STRING! ' . SODIUM_VERSION_STRING . "\n";
} else {
    print "libsodium not found as pecl extension\n";
}

# Check for sodium pecl plugin
if (defined('SODIUM_LIBRARY_VERSION')) {
    echo 'We have SODIUM_LIBRARY_VERSION! ' . SODIUM_LIBRARY_VERSION . "\n";
} else {
    print "libsodium not found as pecl extension\n";
}


# Check for argon2 password hashing
if (defined('PASSWORD_ARGON2ID')) {
    echo "We have PASSWORD_ARGON2ID! (probably PHP 7.3)\n";
} elseif (defined('PASSWORD_ARGON2I')) {
    echo "We have PASSWORD_ARGON2I! (probably PHP 7.2)\n";
} else {
    echo "No argon2 support (probably PHP 7.1)\n";
}

print '</pre>';

phpinfo();
