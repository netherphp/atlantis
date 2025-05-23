<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

use Nether\Atlantis\Routes\User\AuthApple;
use Nether\Atlantis\Routes\User\AuthDiscord;
use Nether\Atlantis\Routes\User\AuthGitHub;
use Nether\Atlantis\Routes\User\AuthGoogle;

class UserDashboardAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/dashboard/alias', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	HandleChangeAlias():
	void {

		($this->Data)
		->Alias(Common\Filters\Text::SlottableKey(...));

		////////

		$Existing = User\Entity::GetByAlias($this->Data->Alias);

		if($Existing) {
			if($Existing->ID === $this->User->ID)
			$this->Quit(1, 'Yeah OK.');

			$this->Quit(2, 'Account Alias already taken.');
		}

		////////

		$this->User->Update([
			'Alias' => $this->Data->Alias
		]);

		$this->SetGoto('/dashboard/settings/alias?updated=1');

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/dashboard/email', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	HandleChangeEmail():
	void {

		($this->Data)
		->Email(Common\Filters\Text::Email(...))
		->Confirm(Common\Filters\Text::Base64Decode(...));

		if(!$this->Data->Email)
		$this->Quit(1, 'Invalid Email address');

		// forget old attempts.

		Atlantis\Struct\EmailUpdate::DropForEntityID($this->User->ID);

		////////

		if($this->Config[User\Library::ConfConfirmEmailChange])
		$this->HandleChangeEmail_ConfirmEmailUpdate();
		else
		$this->HandleChangeEmail_DoEmailUpdate();

		return;
	}

	protected function
	HandleChangeEmail_DoEmailUpdate():
	void {

		$this->User->Update([
			'Email' => $this->Data->Email
		]);

		$this->App->SetLocalData('EmailUpdated', TRUE);

		$this
		->SetGoto('/dashboard/settings/email')
		->SetPayload([
			'ConfirmID' => NULL,
			'Email'     => $this->User->Email
		]);

		return;
	}

	protected function
	HandleChangeEmail_ConfirmEmailUpdate():
	void {

		$Confirm = Atlantis\Struct\EmailUpdate::Insert([
			'EntityID' => $this->User->ID,
			'Email'    => $this->Data->Email,
			'Code'     => Atlantis\Struct\EmailUpdate::Generate()
		]);

		$Confirm->Send();

		$this->App->SetLocalData('EmailConfirmSent', TRUE);

		$this
		->SetGoto('/dashboard/settings/email')
		->SetPayload([
			'ConfirmID' => $Confirm->ID,
			'Email'     => $Confirm->Email
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/dashboard/password', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	HandleChangePassword():
	void {

		($this->Data)
		->Password0(Common\Filters\Text::TrimmedNullable(...))
		->Password1(Common\Filters\Text::TrimmedNullable(...))
		->Password2(Common\Filters\Text::TrimmedNullable(...));

		////////

		if($this->User->PHash !== NULL)
		if(!$this->User->ValidatePassword($this->Data->Password0))
		$this->Quit(1, 'The old password was incorrect');

		if(!$this->Data->Password1 || !$this->Data->Password2)
		$this->Quit(2, 'You did not successfully enter the new password twice.');

		if($this->Data->Password1 !== $this->Data->Password2)
		$this->Quit(3, 'You did not successfully enter the new password twice.');

		////////

		$Tester = new Atlantis\Systems\PasswordTester\Tool;

		if(!$Tester->IsOK($this->Data->Password1))
		$this->Quit(4, sprintf(
			'The new password is not complex enough. %s',
			$Tester->GetDescription()
		));

		$this->User->UpdatePassword($this->Data->Password1);
		$this->User->TransmitSession();
		$this->App->SetLocalData('PasswordUpdated', TRUE);
		$this->SetGoto('/dashboard/settings/password');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/dashboard/auth', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	HandleAuthBegin():
	void {

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/dashboard/auth', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	HandleAuthDelete():
	void {

		($this->Data)
		->AuthType(Common\Filters\Text::Trimmed(...));

		$Field = match($this->Data->AuthType) {
			AuthApple::GetAuthKey()   => AuthApple::AuthField,
			AuthDiscord::GetAuthKey() => AuthDiscord::AuthField,
			AuthGitHub::GetAuthKey()  => AuthGitHub::AuthField,
			AuthGoogle::GetAuthKey()  => AuthGoogle::AuthField,
			default   => NULL
		};

		if($Field === NULL)
		$this->Quit(1, 'Invalid auth type');

		$this->User->Update([ $Field => NULL ]);

		// log auth del

		$this->SetGoto('/dashboard/settings/auth');

		return;
	}

}