<?php

/**
 * PHPUnit bootstrap file.
 *
 * Set include path and initialize autoloader.
 *
 * PHP Version 5.6
 *
 * @package    PHPDraft\Core
 * @author     Sean Molenaar <sean@m2mobi.com>
 * @license    https://github.com/SMillerDev/Php-drafter/blob/master/LICENSE GPLv3 License
 */

$base = __DIR__ . '/..';

set_include_path(
    $base . '/src:' .
    $base . '/tests:' .
    $base . '/tests/statics:' .
    get_include_path()
);

// Load and setup class file autloader
require_once '../src/PHPDraft/Core/Autoloader.php';

$config = json_decode(file_get_contents($base."/config.json"));

if (defined('TEST_STATICS') === FALSE)
{
    define('TEST_STATICS', __DIR__ . '/statics');
}

?>