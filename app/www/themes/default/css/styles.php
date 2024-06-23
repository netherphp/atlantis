<?php

$ScriptRoot = dirname($_SERVER['SCRIPT_FILENAME']);
$ProjectRoot = dirname($ScriptRoot, 4);

require(sprintf('%s/vendor/autoload.php', $ProjectRoot));
new Nether\Common\Library;

////////

(new Nether\Common\OneScript($ScriptRoot, 'styles.css'))
->AddFile('src/imports.css')
->AddFile('src/main.css')
->AddFile('src/ext-bootstrap.css')
->AddDir('src/ext-bootstrap')
->AddFile('src/ext-materialdesignicons.css')
->AddFile('src/ext-simpleicons.css')
->AddFile('src/ext-litepicker.css')
->AddFile('src/ext-grecaptcha.css')
->AddFile('src/ext-editorjs.css')
->AddFile('src/fx-text.css')
->AddDir('src/design')
->AddDir('src/elements')
->Print(TRUE);

