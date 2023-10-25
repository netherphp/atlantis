<?php

namespace Nether\Atlantis\UI;

use Nether\Common;
use Nether\Surface;

class SiteMenuPage
extends Surface\Element {

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS: Surface\Element /////////////////////////////////

	public string
	$Area = 'elements/sitemenu/page';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetDataKey():
	string {

		if($this->Menu instanceof SiteMenu)
		return $this->Menu->DataKey;

		// digest uuids

		return 'unknownmenu';
	}

	public SiteMenu
	$Menu;

	public function
	SetMenu(SiteMenu $Menu):
	static {

		$this->Menu = $Menu;
		return $this;
	}

	public string
	$Title = '';

	public mixed
	$Content = '';

}
