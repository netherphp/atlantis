<?php

namespace Nether\Atlantis\Plugin\Interfaces\ProfileAPI;

use Nether\Atlantis;
use Nether\Common;

interface OnPatchInterface {

	public function
	OnPatch(Atlantis\Profile\Entity $Profile, Common\Datafilter $Data):
	void;

};
