<?php

namespace Nether\Atlantis;
use Nether;

use Nether\Object\Datastore;

class ProtectedWeb
extends PublicWeb {

	public function
	OnReady(?Datastore $Input):
	void {

		parent::OnReady($Input);

		if(!$this->CanUserAccess()) {
			if(!$this->HasUser())
			$this->Goto('/login', TRUE);

			////////

			$this->Response->SetCode(403);
			$this->Quit(403);
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	CanUserAccess():
	bool {

		$Handler = $this->App->Router->GetCurrentHandler();
		$MethodInfo = static::GetMethodInfo($Handler->Method);
		$AccessTypes = new Datastore;
		$UserTypes = NULL;
		$Key = NULL;
		$Val = NULL;
		$Can = TRUE;

		////////

		// find the access types this route demands.

		foreach($MethodInfo->Attributes as $Val)
		if($Val instanceof Nether\Atlantis\Meta\RouteAccessType)
		$AccessTypes->Shove($Val->Key, $Val);

		////////

		// find the access types this user has.

		if($this->HasUser())
		$UserTypes = $this->App->User->GetAccessTypes();

		foreach($AccessTypes as $Key => $Val) {
			$Can = match($Key) {
				'Session'
				=> $this->HasUser(),

				'Admin'
				=> $this->IsUserAdmin($Val->Value),

				default
				=> (
					$this->HasUser()
					&& $UserTypes->HasKey($Key)
					&& $Val->WillAccept($UserTypes[$Key]->Value)
				)
			};

			if(!$Can)
			return FALSE;
		}

		return TRUE;
	}

}
