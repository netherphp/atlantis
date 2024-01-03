<?php

namespace Nether\Atlantis\Plugin\Interfaces\ProfileView;

use Nether\Atlantis;
use Nether\Common;

interface AdminMenuSectionInterface {

	public function
	GetItemsForSection(Atlantis\Profile\Entity $Profile, string $Key, Common\Datastore $ExtraData):
	?Common\Datastore;

};

