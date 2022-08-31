<?php

ini_set('display_errors', TRUE);
error_reporting(E_ALL);

// bring in composer autoloader.

$ProjectRoot = dirname(__FILE__, 2);
require(sprintf('%s/vendor/autoload.php', $ProjectRoot));

// spool up atlantis.

define('Atlantis', new Nether\Atlantis\Engine($ProjectRoot));
Atlantis->Run();
