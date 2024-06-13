<?php

namespace Nether\Atlantis\Plugin\Interfaces\Profile;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

interface ExtendFindFiltersInterface {

	public function
	AddFindFilters(Database\Verse $SQL, Common\Datastore $Input):
	void;

};
