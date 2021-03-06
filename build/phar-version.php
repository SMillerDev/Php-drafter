#!/usr/bin/env php
<?php
if (!isset($argv[1]) || !isset($argv[2])) {
    exit(1);
}

file_put_contents(
    __DIR__ . '/phar/phpdraft/phpdraft',
    str_replace(
        "define('VERSION', '0');",
        "define('VERSION', '" . $argv[1] . "');",
        file_get_contents(__DIR__ . '/phar/phpdraft/phpdraft')
    )
);

if ($argv[2] == 'release') {
    print $argv[1];
} else {
    print $argv[2];
}
