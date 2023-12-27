<?php

namespace Nether\Atlantis\Plugin\Interfaces;

use Nether\Atlantis;
use Nether\Common;

interface ProfileViewExtraDataInterface {

	public function
	GetExtraData(Atlantis\Profile\Entity $Profile):
	Common\Datastore;

};

