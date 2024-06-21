<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Email;
use Nether\User;

use Nether\Avenue\Meta\RouteHandler;

class UserSessionAPI
extends Atlantis\PublicAPI {

	public function
	OnReady(?Avenue\Struct\ExtraData $ExtraData):
	void {

		parent::OnReady($ExtraData);

		if(!User\Entity::HasDB())
		$this->ErrorDevQuit(
			'Missing Database Config',
			sprintf('No database for %s found', User\Entity::$DBA)
		);

		return;
	}

	#[RouteHandler('/api/user/session', Verb: 'LOGIN')]
	public function
	HandleLogin():
	void {

		($this->Request->Data)
		->Username(Common\Filters\Text::TrimmedNullable(...))
		->Password(Common\Filters\Text::StringNullable(...))
		->Goto(Common\Filters\Text::Base64Decode(...));

		////////

		if(!$this->Request->Data->Username)
		$this->Quit(1, 'Missing Field: Username');

		if(!$this->Request->Data->Password)
		$this->Quit(2, 'Missing Field: Password');

		////////

		$User = User\EntitySession::GetBy(
			$this->Request->Data->Username
		);

		if(!$User)
		$this->Quit(3, 'Invalid credentials');

		if(!$User->ValidatePassword($this->Request->Data->Password))
		$this->Quit(4, 'Invalid credentials');

		if($User->TimeBanned !== 0)
		$this->Quit(5, 'Account is banned');

		////////

		$User
		->TransmitSession()
		->UpdateTimeSeen()
		->UpdateRemoteAddr();

		$this
		->SetGoto($this->Request->Data->Goto ?: '/')
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

	#[RouteHandler('/api/user/session', Verb: 'ALIAS')]
	public function
	HandleSetAlias():
	void {

		($this->Data)
		->Alias(Common\Filters\Text::Trimmed(...));

		$User = User\EntitySession::Get();
		$Alias = $this->Data->Alias;

		////////

		if(!$User)
		$this->Quit(1, 'no session');

		////////

		try { Atlantis\Util\UsernameTester::TryValid($Alias); }

		catch(Atlantis\Error\User\AliasInvalidTooShort $Error) {
			$this->Quit(2, $Error->GetMessage());
		}

		catch(Atlantis\Error\User\AliasInvalidChars $Error) {
			$this->Quit(3, $Error->GetMessage());
		}

		catch(Atlantis\Error\User\AliasInvalidFirstLast $Error) {
			$this->Quit(4, $Error->GetMessage());
		}

		catch(Atlantis\Error\User\AliasInvalidDogpiling $Error) {
			$this->Quit(5, $Error->GetMessage());
		}

		////////

		$Old = User\Entity::GetByAlias($this->Data->Alias);

		if($Old !== NULL)
		$this->Quit(6, 'Username is taken');

		////////

		$User->Update([ 'Alias'=> $this->Data->Alias ]);

		$this
		->SetGoto('/')
		->SetPayload([
			'Alias' => $User->Alias
		]);

		return;
	}

	#[RouteHandler('/api/user/session', Verb: 'SENDCONFIRM')]
	public function
	HandleSendConfirmation():
	void {

		$FiveMinutesAgo = (time() - (Common\Values::SecPerMin * 5));
		$User = User\EntitySession::Get();
		$RecentlySent = FALSE;
		$Confirm = NULL;

		if(!$User)
		$this->Quit(1, 'no session');

		////////

		$Prev = Atlantis\Struct\EmailUpdate::Find([
			'EntityID' => $User->ID,
			'Limit'    => 1
		]);

		if($Prev->Count())
		if($Prev->Current()->TimeCreated > $FiveMinutesAgo)
		$RecentlySent = TRUE;

		////////

		if(!$RecentlySent) {
			Atlantis\Struct\EmailUpdate::DropForEntityID($User->ID);

			$Confirm = Atlantis\Struct\EmailUpdate::Insert([
				'EntityID' => $User->ID,
				'Email'    => $User->Email
			]);

			$Confirm->Send(TRUE);
		}

		$this
		->SetGoto('/login/activate')
		->SetPayload([
			'RecentlySent' => $RecentlySent
		]);

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
		->Email(Common\Filters\Text::Email(...));

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
		->Code(Common\Filters\Text::Base64Decode(...))
		->Password1(Common\Filters\Text::TrimmedNullable(...))
		->Password2(Common\Filters\Text::TrimmedNullable(...));

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

	#[RouteHandler('/api/user/create', Verb: 'POST')]
	public function
	HandleCreateAccount():
	void {

		($this->Data)
		->Email(Common\Filters\Text::Email(...))
		->Alias(Common\Filters\Text::TrimmedNullable(...))
		->Password1(Common\Filters\Text::StringNullable(...))
		->Password2(Common\Filters\Text::StringNullable(...))
		->Session(Common\Filters\Numbers::BoolType(...));

		$Captcha = new Atlantis\Util\Captcha($this->App);
		$RequireEmail = $this->Config[Atlantis\Key::ConfUserEmailActivate];
		$RequireAlias = $this->Config[Atlantis\Key::ConfUserRequireAlias];
		$RequireActivation = TRUE;
		$AllowSignup = $this->Config[Atlantis\Key::ConfUserAllowSignup];

		if(!$AllowSignup)
		$this->Quit(10, 'Not right now');

		$PasswordTester = new Atlantis\Util\PasswordTester;
		$User = NULL;
		$RemoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;

		////////

		if(!$this->App->Config[Email\Library::ConfOutboundVia]) {
			$RequireEmail = FALSE;
			$RequireActivation = FALSE;
		}

		////////

		if($Captcha->IsConfigured())
		if(!$Captcha->IsValid())
		$this->Quit(9, 'Failed to prove as human');

		if(!$this->Data->Email)
		$this->Quit(1, 'Invalid Email');

		if($RequireAlias)
		if(!$this->Data->Alias)
		$this->Quit(2, 'Invalid Username');

		if(!$this->Data->Password1 || !$this->Data->Password2)
		$this->Quit(3, 'Invalid Password');

		if($this->Data->Password1 !== $this->Data->Password2)
		$this->Quit(4, 'Did not enter the same password twice');

		if(!$PasswordTester->IsOK($this->Data->Password1))
		$this->Quit(5, sprintf(
			'Password is not complex enough: %s',
			$PasswordTester->GetDescription()
		));

		////////

		$User = User\Entity::GetByEmail($this->Data->Email);

		if($User instanceof User\Entity)
		$this->Quit(6, 'There is already an account using this email address.');

		if($RequireAlias) {
			$User = User\Entity::GetByAlias($this->Data->Alias);

			if($User instanceof User\Entity)
			$this->Quit(7, 'There is already an account with this username.');
		}

		////////

		$User = User\EntitySession::Insert([
			'Alias'      => $RequireAlias ? $this->Data->Alias : NULL,
			'Email'      => $this->Data->Email,
			'PHash'      => User\Entity::GeneratePasswordHash($this->Data->Password1),
			'RemoteAddr' => $RemoteAddr,
			'Activated'  => $RequireActivation ? 0 : 1
		]);

		if(!$User)
		$this->Quit(8, 'Unknown error occured creating account.');

		if($RequireEmail) {
			Atlantis\Struct\EmailUpdate::DropForEntityID($User->ID);

			$Confirm = Atlantis\Struct\EmailUpdate::Insert([
				'EntityID' => $User->ID,
				'Email'    => $User->Email,
				'Code'     => Atlantis\Struct\EmailUpdate::Generate()
			]);

			$Confirm->Send(TRUE);
		}

		// log user create

		////////

		if($this->Data->Session)
		$User->TransmitSession();

		////////

		$this->SetGoto('/');
		return;
	}

	#[RouteHandler('/api/user/create', Verb: 'CHECKALIAS')]
	public function
	HandleCheckUsername():
	void {

		($this->Data)
		->Alias(Atlantis\Util\UsernameTester::ValidUsernameFilter(...));

		if(!$this->Data->Alias)
		$this->Quit(1, 'Username is invalid.');

		$AConflict = User\Entity::GetByAlias($this->Data->Alias);

		if($AConflict)
		$this->Quit(2, 'Username is taken.');

		return;
	}

}
