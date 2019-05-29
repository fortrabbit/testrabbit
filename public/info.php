<?php

# Check for argon2 password hashing
print '<pre>';
if (defined('PASSWORD_ARGON2ID')) {
    echo "We have PASSWORD_ARGON2ID! (probably PHP 7.3)\n";
} elseif (defined('PASSWORD_ARGON2I')) {
    echo "We have PASSWORD_ARGON2I! (probably PHP 7.2)\n";
} else {
    echo "No argon2 support (probably PHP 7.1)\n";
}
print '</pre>';

phpinfo();
