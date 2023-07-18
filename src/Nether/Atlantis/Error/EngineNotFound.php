<?php

namespace Nether\Atlantis\Error;

use Exception;

class EngineNotFound
extends Exception {

	public function
	__Construct() {
		parent::__Construct('No Engine was found in that pile');
		return;
	}

}
