<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

class UserDashboardAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/dashboard/email', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	HandleChangeEmail():
	void {

		($this->Data)
		->Email(Common\Datafilters::Email(...))
		->Confirm(Common\Datafilters::Base64Decode(...));

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
		->Password0(Common\Datafilters::TrimmedTextNullable(...))
		->Password1(Common\Datafilters::TrimmedTextNullable(...))
		->Password2(Common\Datafilters::TrimmedTextNullable(...));

		////////

		if($this->User->PHash !== NULL)
		if(!$this->User->ValidatePassword($this->Data->Password0))
		$this->Quit(1, 'The old password was incorrect');

		if(!$this->Data->Password1 || !$this->Data->Password2)
		$this->Quit(2, 'You did not successfully enter the new password twice.');

		if($this->Data->Password1 !== $this->Data->Password2)
		$this->Quit(3, 'You did not successfully enter the new password twice.');

		////////

		$Tester = new Atlantis\Util\PasswordTester;

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
		->AuthType(Common\Datafilters::TrimmedText(...));

		var_dump($this->Data->AuthType);

		$Field = match($this->Data->AuthType) {
			'apple'   => 'AuthAppleID',
			'github'  => 'AuthGitHubID',
			'google'  => 'AuthGoogleID',
			'twitter' => 'AuthTwitterID',
			default   => NULL
		};

		if($Field === NULL)
		$this->Quit(1, 'Invalid auth type');

		$this->User->Update([ $Field => NULL ]);

		$this->SetGoto('/dashboard/settings/auth');

		return;
	}

}