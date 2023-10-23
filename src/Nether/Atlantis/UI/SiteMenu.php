<?php

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

class SiteMenu
extends Surface\Element {

	public string
	$Area = 'elements/sitemenu/main';

	public string
	$UUID = 'SiteMenu';

	public array|Common\Datastore
	$JSModules = [
		'SiteMenu' => '/share/nui/modules/sitemenu/offcanvas.js'
	];

	public array|Common\Datastore
	$JSReady = [
		'SiteMenu' => 'let sitemenu = new SiteMenu("{%SelectorID%}");'
	];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyFactory('FromArray', 'Pages')]
	public array|Common\Datastore
	$Pages = [ ];

}
