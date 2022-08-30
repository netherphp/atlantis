<?php

ini_set('display_errors', TRUE);
error_reporting(E_ALL);

define('Config', require(sprintf('%s/conf/start.php', dirname(__FILE__, 2))));
define('Atlantis', new Nether\Atlantis\Engine(Config));

Atlantis->Run();
