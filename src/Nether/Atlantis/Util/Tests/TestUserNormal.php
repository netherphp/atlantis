<?php

namespace Nether\Atlantis\Util\Tests;

use Nether\Common;
use Nether\User;

class TestUserNormal
extends User\EntitySession {

	public function
	__Construct() {

		parent::__Construct([
			'ID'          => 42,
			'Alias'       => 'geordi-laforge',
			'TimeCreated' => time(),
			'TimeBanned'  => 0,
			'Activated'   => 1,
			'Admin'       => 0
		]);

		$this->AccessTypes = new Common\Datastore;

		return;
	}

}

