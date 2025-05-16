<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Systems\PasswordTester;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class Tool
extends Common\PasswordTester {

	static public function
	New(Atlantis\Engine $App):
	static {

		$Output = new static;
		$Output->SetMinLength($App->Config->Get(Atlantis\Key::ConfPassMinLen));
		$Output->SetRequireAlphaLower($App->Config->Get(Atlantis\Key::ConfPassReqAlphaLower));
		$Output->SetRequireAlphaUpper($App->Config->Get(Atlantis\Key::ConfPassReqAlphaUpper));
		$Output->SetRequireNumeric($App->Config->Get(Atlantis\Key::ConfPassReqNumeric));
		$Output->SetRequireSpecial($App->Config->Get(Atlantis\Key::ConfPassReqSpecial));

		return $Output;
	}

};
