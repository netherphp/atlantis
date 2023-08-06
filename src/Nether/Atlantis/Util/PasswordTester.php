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
		->SetMinLength(Atlantis\Library::Get(Atlantis\Key::ConfPassMinLen))
		->SetRequireAlphaLower(Atlantis\Library::Get(Atlantis\Key::ConfPassReqAlphaLower))
		->SetRequireAlphaUpper(Atlantis\Library::Get(Atlantis\Key::ConfPassReqAlphaUpper))
		->SetRequireNumeric(Atlantis\Library::Get(Atlantis\Key::ConfPassReqNumeric))
		->SetRequireSpecial(Atlantis\Library::Get(Atlantis\Key::ConfPassReqSpecial));

		return;
	}

}
