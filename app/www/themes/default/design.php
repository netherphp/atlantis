<?php

/**
 * @var Nether\Surface\Engine $Surface
 */

// this file if exists gets loaded when the surface engine is done
// getting itself ready.

($Surface)
->AddStyleURL('/themes/default/lib/css/swiper-bundle.min.css')
->AddScriptURL('/themes/default/lib/js/swiper-bundle.min.js')
//->AddScriptURL('https://cdn.jsdelivr.net/npm/swiper@10/swiper-element-bundle.min.js')
->Set('Theme.Page.Wrapper', 'design/page-wrapper');
