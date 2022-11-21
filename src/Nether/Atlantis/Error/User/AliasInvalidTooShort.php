<?php

namespace Nether\Atlantis\Error\User;

use Exception;

class AliasInvalidTooShort
extends Exception {

	public function
	__Construct() {
		parent::__Construct('Must be at least 2 characters long.', 4);
		return;
	}

}
