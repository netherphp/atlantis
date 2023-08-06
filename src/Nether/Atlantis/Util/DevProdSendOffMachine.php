<?php

namespace Nether\Atlantis\Util;

use Nether\Atlantis;
use Nether\Common;

use Exception;

#[Common\Meta\Date('2023-08-05')]
#[Common\Meta\Info('Handles making the decision to sending users away from development servers.')]
class DevProdSendOffMachine {

	protected Atlantis\Engine
	$App;

	protected int
	$Enabled;

	public function
	__Construct(Atlantis\Engine $App) {

		$this->App = $App;
		$this->Enabled = (int)$App->Config[Atlantis\Key::ConfDevProdSendOff];

		return;
	}

	public function
	ShouldSendOff():
	bool {

		if($this->Enabled === 0)
		return FALSE;

		if(!$this->App->IsDev())
		return FALSE;

		// right now the only way i have to get unit tests to pass is to
		// make them lie regarding framework functions that may result in
		// the use of exit() because phpunit explodes.

		if(defined('UNIT_TEST_GO_BRRRT'))
		return FALSE;

		// if this router is part of the user auth flow then do not send
		// anybody off so that they can ne logged in for further testing.

		$Handler = $this->App->Router->GetCurrentHandler();
		$Info = ($Handler->Class)::GetMethodInfo($Handler->Method);

		if($Info->HasAttribute(UserActivationFlow::class))
		return FALSE;

		////////

		if($this->Enabled === 1) {
			// send off non-admins.
			if($this->App->User && $this->App->User->IsAdmin())
			return FALSE;
		}

		elseif($this->Enabled === 2) {
			// send off non-users.
			if($this->App->User)
			return FALSE;
		}

		elseif($this->Enabled === 3) {
			// send off non-users or users missing permission.
			if($this->App->User)
			if($this->App->User->HasAccessTypeOrAdmin(Atlantis\Key::AccessDeveloper))
			return FALSE;
		}

		////////

		return TRUE;
	}

	public function
	GetURL():
	string {

		$URL = $this->App->Router->Request->GetURL();

		$Rewriter = match(TRUE) {
			(is_callable(Atlantis\Library::Get(Atlantis\Key::ConfDevLinkRewriter)))
			=> Atlantis\Library::Get(Atlantis\Key::ConfDevLinkRewriter),

			(str_contains($URL, '://dev.'))
			=> fn(string $U)=> preg_replace('#://dev\.#', '://', $U),

			default
			=> throw new Exception('no URL rewriter defined for sendoff')
		};

		return $Rewriter($URL);
	}

}
