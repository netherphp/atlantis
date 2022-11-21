<?php

namespace Nether\Atlantis\Error\User;

use Exception;

class AliasInvalidDogpiling
extends Exception {

	public function
	__Construct() {
		parent::__Construct('Dashes and Dots may not be repeated more than once in a row.', 2);
		return;
	}

}
