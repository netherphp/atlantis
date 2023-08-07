<?php

namespace Nether\Atlantis\Util\Tests;

use Nether\Atlantis;
use Nether\User;

class TestUserDeveloper
extends TestUserNormal {

	public function
	__Construct() {

		parent::__Construct();

		$this->Admin = 0;

		$this->AccessTypes->Shove(
			Atlantis\Key::AccessDeveloper,
			new User\EntityAccessType([
				'Key'=> Atlantis\Key::AccessDeveloper, 'Value'=> 1
			])
		);

		return;
	}

}

