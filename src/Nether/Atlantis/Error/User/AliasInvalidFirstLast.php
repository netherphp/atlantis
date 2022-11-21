<?php

namespace Nether\Atlantis\Error\User;

use Exception;

class AliasInvalidFirstLast
extends Exception {

	public function
	__Construct() {
		parent::__Construct('Must begin with a Letter, and may not end with Dashes or Dots.', 3);
		return;
	}

}
