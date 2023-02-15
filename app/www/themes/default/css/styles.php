<?php

$ScriptRoot = dirname($_SERVER['SCRIPT_FILENAME']);
$ProjectRoot = dirname($ScriptRoot, 4);

require(sprintf('%s/vendor/autoload.php', $ProjectRoot));
new Nether\Common\Library;

////////

(new Nether\Common\OneScript($ScriptRoot, 'styles.css'))
->AddFile('src/imports.css')
->AddFile('src/ext-bootstrap.css')
->AddFile('src/ext-materialdesignicons.css')
->AddFile('src/main.css')
->AddDir('src/design')
->AddDir('src/elements')
->Print(TRUE);

