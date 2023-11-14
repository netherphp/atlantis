<?php

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

class SiteMenu
extends Surface\Element {

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS: Surface\Element /////////////////////////////////

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
		'SiteMenu' => 'let sitemenu = new SiteMenu("{%SelectorID%}", { datakey: "{%DataKey%}" });'
	];

	#[Common\Meta\Date('2023-10-25')]
	public function
	TokenJSReady():
	Common\Datastore {

		$Output = parent::TokenJSReady();
		$Output['DataKey'] = $this->DataKey;

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public string
	$DataKey = 'sitemenu';

	#[Common\Meta\PropertyFactory('FromArray', 'Pages')]
	public array|Common\Datastore
	$Pages = [ ];

}
