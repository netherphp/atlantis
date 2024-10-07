<?php

/**
 * @var Nether\Surface\Engine $Surface
 */

// this file if exists gets loaded when the surface engine is done
// getting itself ready. it only has access to the Surface instance itself
// not the entire scope that the application may have built, unlike the
// design.phtml file which can access all the things scoped in.

$Surface->QueueOnce('Atlantis.Config', function(Nether\Atlantis\Engine $App) {

	$PageThemeMode = match(TRUE) {
		(isset($_COOKIE['theme']))
		=> $_COOKIE['theme'],

		default
		=> 'dark'
	};

	$PageMainCSS = ($App->IsDev() ? 'css/styles.php' : 'css/styles.css');

	($App->Surface)
	->AddScriptURL('https://www.google.com/recaptcha/api.js')
	->AddScriptURL('/themes/default/lib/js/swiper-bundle.min.js')
	->AddStyleURL('https://fonts.googleapis.com/css2?family=Beiruti:wght@200..900&display=swap')
	->AddStyleURL('/themes/default/lib/css/swiper-bundle.min.css')
	->Define([

		// audited oct 2024
		'Page.Body.Classes'               => new Nether\Common\Datastore,
		'Page.Theme.Mode'                 => $PageThemeMode,
		'Page.Theme.FavIconURL'           => '/themes/default/gfx/favicon.ico',
		'Page.Theme.Header.Container'     => 'container',
		'Page.Theme.Header.LogoURL'       => '/themes/default/gfx/atlantis-word.png',
		'Page.Theme.Header.MenuBtn.Label' => 'Menu',

		// old
		'Theme.Page.MainCSS'          => $PageMainCSS,
		'Theme.SiteMenu.Icons'        => TRUE,
		'Theme.SiteMenu.Icons.NoIcon' => 'mdi mdi-circle-medium',
		'Theme.SiteMenu.Icons.Next'   => 'mdi mdi-chevron-double-right',
		'Theme.SiteMenu.ItemArea'     => 'sitemenu-main/item',
		'Theme.Page.Wrapper'          => 'design/page-wrapper'

	]);

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// include google recaptcha script if keys are defined.

	if($App->Config->IsTrue('Google.ReCaptcha.PublicKey'))
	if($App->Config->IsTrue('Google.ReCaptcha.PrivateKey'))
	$App->Surface->AddScriptURL('https://www.google.com/recaptcha/api.js');

	return;
});


