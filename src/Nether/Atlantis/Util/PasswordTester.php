<?php

namespace Nether\Atlantis\Util;

use Nether\Atlantis;
use Nether\Common;

class PasswordTester
extends Common\PasswordTester {

	public function
	__Construct(...$Argv) {

		parent::__Construct(...$Argv);

		$this
		->SetMinLength(Atlantis\Library::Get(Atlantis\Library::ConfPassMinLen))
		->SetRequireAlphaLower(Atlantis\Library::Get(Atlantis\Library::ConfPassReqAlphaLower))
		->SetRequireAlphaUpper(Atlantis\Library::Get(Atlantis\Library::ConfPassReqAlphaUpper))
		->SetRequireNumeric(Atlantis\Library::Get(Atlantis\Library::ConfPassReqNumeric))
		->SetRequireSpecial(Atlantis\Library::Get(Atlantis\Library::ConfPassReqSpecial));

		return;
	}

}
