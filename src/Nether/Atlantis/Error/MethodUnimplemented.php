<?php

namespace Nether\Atlantis\Error;

use Exception;

class MethodUnimplemented
extends Exception {

	public function
	__Construct(string $Method) {

		parent::__Construct(sprintf(
			'%s is unimplemented',
			$Method
		));

		return;
	}

}
