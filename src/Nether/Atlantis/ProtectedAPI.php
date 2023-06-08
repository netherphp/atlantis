<?php

namespace Nether\Atlantis;
use Nether;

use Nether\Common\Datastore;
use Nether\Atlantis\Filter;
use Nether\Atlantis\Util;
use Nether\Atlantis\Library;

class ProtectedAPI
extends PublicAPI {
/*//
@date 2022-11-09
adds basic access protection to the api route which can be managed with the
various access attributes attached to the method and a user's assigned access
types.
//*/

	public function
	OnReady(?Datastore $Input):
	void {

		parent::OnReady($Input);

		if(!$this->CanUserAccess()) {
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
