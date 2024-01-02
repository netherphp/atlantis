<?php

namespace Nether\Atlantis\Plugin\Interfaces\ProfileView;

use Nether\Atlantis;
use Nether\Common;

interface ExtraDataInterface {

	public function
	GetExtraData(Atlantis\Profile\Entity $Profile):
	Common\Datastore;

};

