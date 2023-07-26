<?php

namespace Nether\Atlantis\Error;

use Exception;

class TagEntityPropertyNotFound
extends Exception {

	public function
	__Construct(string $Class) {
		parent::__Construct("no TagEntityProperty found on {$Class}");
		return;
	}

}
