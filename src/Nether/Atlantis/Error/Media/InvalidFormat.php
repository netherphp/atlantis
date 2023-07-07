<?php

namespace Nether\Atlantis\Error\Media;

use Exception;

class InvalidUpload
extends Exception {

	public function
	__Construct(string $UUID) {
		parent::__Construct("invalid format {$UUID}");
		return;
	}

}
