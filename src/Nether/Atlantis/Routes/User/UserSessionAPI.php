<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

use Nether\Avenue\Meta\RouteHandler;

class UserSessionAPI
extends Atlantis\PublicAPI {

	#[RouteHandler('/api/user/session', Verb: 'LOGIN')]
	public function
	HandleLogin():
	void {

		($this->Request->Data)
		->Username(Common\Datafilters::TrimmedTextNullable(...))
		->Password(Common\Datafilters::TypeStringNullable(...))
		->Goto(Common\Datafilters::Base64Decode(...));

		////////

		if(!$this->Request->Data->Username)
		$this->Quit(1, 'Missing Field: Username');

		if(!$this->Request->Data->Password)
		$this->Quit(2, 'Missing Field: Password');

		////////

		$User = User\EntitySession::GetByAlias(
			$this->Request->Data->Username
		);

		if(!$User)
		$this->Quit(3, 'Invalid credentials');

		if(!$User->ValidatePassword($this->Request->Data->Password))
		$this->Quit(4, 'Invalid credentials');

		if($User->TimeBanned)
		$this->Quit(5, 'Account is banned');

		////////

		$User->TransmitSession();
		$User->UpdateTimeSeen();

		$this
		->SetGoto($this->Request->Data->Goto ?: NULL)
		->SetPayload([
			'ID'    => $User->ID,
			'Alias' => $User->Alias,
			'CData' => $User->GenerateSessionData()
		]);

		return;
	}

	#[RouteHandler('/api/user/session', Verb: 'LOGOUT')]
	public function
	HandleLogout():
	void {

		$User = User\EntitySession::Get();
		$Payload = [ 'ID' => NULL, 'Alias' => NULL, 'CData' => NULL ];

		if($User) {
			$User->DestroySession();
			$Payload['ID'] = $User->ID;
			$Payload['Alias'] = $User->Alias;
		}

		$this
		->SetGoto('/')
		->SetPayload($Payload);

		return;
	}

	#[RouteHandler('/api/user/session', Verb: 'GET')]
	public function
	HandleStatus():
	void {

		$User = User\EntitySession::Get();
		$Payload = [ 'ID' => NULL, 'Alias' => NULL, 'CData' => NULL ];

		if($User) {
			$Payload['ID'] = $User->ID;
			$Payload['Alias'] = $User->Alias;
			$Payload['CData'] = $User->GenerateSessionData();
		}

		$this
		->SetPayload($Payload);

		return;
	}

	#[RouteHandler('/api/user/reset', Verb: 'POST')]
	public function
	HandleForgot():
	void {

		($this->Data)
		->Email(Common\Datafilters::Email(...));

		if(!$this->Data->Email)
		$this->Quit(1, 'Invalid email address');

		////////

		// this is one of those cases where regardless of the outcome
		// we are going to pretend everything is ok to make it a little
		// less trivial to farm accounts using this.

		$this->App->SetLocalData('LoginResetSent', TRUE);
		$this->SetGoto('/login/reset');

		////////

		$User = User\EntitySession::GetByEmail($this->Data->Email);

		if(!$User)
		$this->Quit(0);

		////////

		Atlantis\Struct\LoginReset::DropForEntityID($User->ID);

		$Reset = Atlantis\Struct\LoginReset::Insert([
			'EntityID' => $User->ID,
			'Code'     => Atlantis\Struct\LoginReset::Generate()
		]);

		$Reset->Send();

		$this->Quit(0);
		return;
	}

	#[RouteHandler('/api/user/reset', Verb: 'RESET')]
	public function
	HandleReset():
	void {

		$Code = NULL;
		$User = NULL;
		$Reset = NULL;

		////////

		($this->Data)
		->Code(Common\Datafilters::Base64Decode(...))
		->Password1(Common\Datafilters::TrimmedTextNullable(...))
		->Password2(Common\Datafilters::TrimmedTextNullable(...));

		if(!$this->Data->Password1 || !$this->Data->Password2)
		$this->Quit(1, 'You did not successfully enter the new password twice.');

		if($this->Data->Password1 !== $this->Data->Password2)
		$this->Quit(2, 'You did not successfully enter the new password twice.');

		////////

		$Code = json_decode($this->Data->Code);

		if(!is_object($Code) || !property_exists($Code, 'ID') || !property_exists($Code, 'Code'))
		$this->Quit(3, 'Error parsing the recovery code');

		////////

		$Reset = Atlantis\Struct\LoginReset::GetByID($Code->ID);

		if(!$Reset)
		$this->Quit(4, 'Error parsing the recovery code');

		if($Reset->Code !== $Code->Code)
		$this->Quit(5, 'Error parsing the recovery code');

		////////

		$User = User\EntitySession::GetByID($Reset->EntityID);

		if(!$User)
		$this->Quit(6, 'Error parsing the recovery code');

		////////

		$Tester = new Atlantis\Util\PasswordTester;

		if(!$Tester->IsOK($this->Data->Password1))
		$this->Quit(7, sprintf(
			'The new password is not complex enough. %s',
			$Tester->GetDescription()
		));

		$User->UpdatePassword($this->Data->Password1);
		$User->TransmitSession();
		$Reset->Drop();

		$this->App->SetLocalData('PasswordUpdated', TRUE);
		$this->SetGoto('/dashboard/settings/password');

		return;
	}

}
