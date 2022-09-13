<?php

namespace Nether\Atlantis\Error;

use Exception;

class EnvLockUnwritable
extends Exception {

	public function
	__Construct(string $Path) {
		parent::__Construct("{$Path} not writable");
		return;
	}

}
