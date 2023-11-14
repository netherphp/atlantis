<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;
use Nether\Common;

interface AdminMenuVideoThirdPartyInterface {

	public function
	GetItems(Atlantis\Media\VideoThirdParty $Video):
	Common\Datastore;

}
