<?php

namespace Nether\Atlantis\Error\User;

use Exception;

class AliasInvalidChars
extends Exception {

	public function
	__Construct() {
		parent::__Construct('May only contain Letters, Numbers, Underscores, Dashes, and Dots.', 1);
		return;
	}

}
