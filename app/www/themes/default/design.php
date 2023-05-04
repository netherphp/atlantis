<?php

/**
 * @var Nether\Surface\Engine $Surface
 */

// this file if exists gets loaded when the surface engine is done
// getting itself ready.

($Surface)
->AddScriptURL('https://www.google.com/recaptcha/api.js')
->Set('Theme.Page.Wrapper', 'design/page-wrapper');
