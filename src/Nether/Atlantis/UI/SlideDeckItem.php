<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

################################################################################
################################################################################

class SlideDeckItem
extends Common\Prototype {

	public string
	$UUID;

	public ?string
	$ImageURL;

	public ?string
	$ClickURL;

	public ?string
	$Text;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if(!isset($this->UUID))
		$this->UUID = Common\UUID::V7();

		return;
	}

};
