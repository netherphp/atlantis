<?php

namespace Nether\Atlantis;
use Nether;

use Nether\Avenue;
use Nether\Common;

class ProtectedWeb
extends PublicWeb {

	public function
	OnWillConfirmReady(?Avenue\Struct\ExtraData $ExtraData):
	int {

		parent::OnWillConfirmReady($ExtraData);

		if(!$this->CanUserAccess()) {
			if(!$this->HasUser()) {
				$this->SetHeader('Location', Util::AppendGoto(
					'/login',
					$this->Request->GetURL()
				));

				return Avenue\Response::CodeFound;
			}

			return Avenue\Response::CodeForbidden;
		}

		return Avenue\Response::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	CanUserAccess():
	bool {

		$Handler = $this->App->Router->GetCurrentHandler();
		$MethodInfo = static::GetMethodInfo($Handler->Method);
		$AccessTypes = new Common\Datastore;
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
