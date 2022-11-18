<?php

namespace Nether\Atlantis\Routes\User;
use Nether;
use League;

use Nether\User\Library;
use Nether\Atlantis\PublicAPI;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Common\Datafilters;

class UserSessionAPI
extends PublicAPI {

	#[RouteHandler('/api/user/session', Verb: 'LOGIN')]
	#[RouteHandler('/api/user/session/login', Verb: 'POST')]
	public function
	HandleLogin():
	void {

		($this->Request->Data)
		->Username(Datafilters::TrimmedTextNullable(...))
		->Password(Datafilters::TypeStringNullable(...))
		->Goto(Datafilters::Base64Decode(...));

		////////

		if(!$this->Request->Data->Username)
		$this->Quit(1, 'Missing Field: Username');

		if(!$this->Request->Data->Password)
		$this->Quit(2, 'Missing Field: Password');

		////////

		$User = Nether\User\EntitySession::GetByAlias(
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
	#[RouteHandler('/api/user/session/logout', Verb: 'POST')]
	public function
	HandleLogout():
	void {

		$User = Nether\User\EntitySession::Get();
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

	#[RouteHandler('/api/user/session', Verb: 'STATUS')]
	#[RouteHandler('/api/user/session/status')]
	public function
	HandleStatus():
	void {

		$User = Nether\User\EntitySession::Get();
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

}
