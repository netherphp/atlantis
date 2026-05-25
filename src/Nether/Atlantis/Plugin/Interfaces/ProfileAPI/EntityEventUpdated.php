<?php

namespace Nether\Atlantis\Plugin\Interfaces\ProfileAPI;

use Nether\Atlantis;
use Nether\Common;

interface EntityEventUpdated {

	public function
	Updated(Atlantis\Engine $Atl, Atlantis\Profile\Entity $Profile, Common\Datastore $Patchset):
	void;

};
