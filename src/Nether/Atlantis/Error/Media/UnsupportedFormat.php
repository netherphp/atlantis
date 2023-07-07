<?php

namespace Nether\Atlantis\Error\Media;

use Exception;

class UnsupportedFormat
extends Exception {

	public function
	__Construct(string $Fmt) {
		parent::__Construct(sprintf(
			'%s is not supported yet',
			strtoupper($Fmt)
		));

		return;
	}

}
