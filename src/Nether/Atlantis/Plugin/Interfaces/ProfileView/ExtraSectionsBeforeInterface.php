<?php

namespace Nether\Atlantis\Plugin\Interfaces\ProfileView;

use Nether\Atlantis;
use Nether\Common;

interface ExtraSectionsBeforeInterface {

	// fill the datastore with things that can be printed, or if given a
	// callable it will call that with the curent Surface Area $__SCOPE array
	// passed in as the first argument.

	public function
	GetExtraSectionsBefore(Atlantis\Profile\Entity $Profile):
	Common\Datastore;

};

