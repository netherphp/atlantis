<?php

namespace Nether\Atlantis\Error;

use Exception;

class Quit
extends Exception {

	public function
	__Construct(int $Code, string $Msg) {
		parent::__Construct($Msg, $Code);
		return;
	}

};
