<?php

namespace Nether\Atlantis\Error;

use Exception;

class CertLookupFailure
extends Exception {

	public function
	__Construct(string $Domain) {
		parent::__Construct("ssl cert lookup failure for {$Domain}");
		return;
	}

}