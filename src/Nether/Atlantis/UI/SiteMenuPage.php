<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\UI;

use Nether\Common;
use Nether\Surface;

################################################################################
################################################################################

class SiteMenuPage
extends Surface\Element {

	public SiteMenu
	$Menu;

	public string
	$Title = '';

	public string
	$Subtitle = '';

	public mixed
	$Content = '';

	public mixed
	$Footer = '';

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetMenu(SiteMenu $Menu):
	static {

		$this->Menu = $Menu;

		return $this;
	}

}
