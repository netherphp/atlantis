<?php

/**
 * @var Nether\Surface\Engine $Surface
 */

// this file if exists gets loaded when the surface engine is done
// getting itself ready.

($Surface)
->AddStyleURL('/themes/default/lib/css/swiper-bundle.min.css')
->AddScriptURL('/themes/default/lib/js/swiper-bundle.min.js')
->Set('Theme.Header.Contain', FALSE)
->Set('Theme.SiteMenu.Icons', TRUE)
->Set('Theme.SiteMenu.Icons.NoIcon', 'mdi mdi-circle-medium')
->Set('Theme.SiteMenu.Icons.Next', 'mdi mdi-chevron-double-right')
->Set('Theme.SiteMenu.ItemArea', 'sitemenu-main/item')
->Set('Theme.Page.Wrapper', 'design/page-wrapper');
