<?php

namespace Nether\Atlantis\Plugin\Interfaces\Profile;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

interface ExtendFindSortsInterface {

	public function
	AddFindSorts(Database\Verse $SQL, Common\Datastore $Input):
	void;

};
