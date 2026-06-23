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

	public ?string
	$UUID = NULL;

	public ?string
	$ImageURL = NULL;

	public ?string
	$LinkURL = NULL;

	public ?string
	$Text = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if(!isset($this->UUID) || !$this->UUID)
		$this->UUID = Common\UUID::V7();

		return;
	}

};
