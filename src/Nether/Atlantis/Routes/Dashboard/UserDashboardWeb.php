<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

class UserDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	PageDashboard():
	void {

		($this->App->Surface)
		->Wrap('user/dashboard/index');

		return;
	}

	#[Atlantis\Meta\RouteHandler('/dashboard/settings/email')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	PageEmail():
	void {

		($this->Query)
		->Confirm(Common\Datafilters::Base64Decode(...));

		////////

		// if a confirm code existed attempt to deal with it and then
		// redirect to the clean dashboard.

		if($this->Query->Exists('Confirm')) {
			$Confirm = Atlantis\Struct\EmailUpdate::Find([
				'EntityID' => $this->User->ID,
				'Code'     => $this->Query->Confirm,
				'Limit'    => 1
			]);

			if($Confirm->Total) {
				$Confirm = $Confirm->Current();

				$this->User->Update([
					'Email'=> $Confirm->Email
				]);

				$this->App->SetLocalData('EmailUpdated', TRUE);

				$Confirm->Drop();
			}

			$this->Goto('/dashboard/settings/email');
		}

		////////

		$Sent = $this->App->YoinkLocalData('EmailConfirmSent');
		$Updated = $this->App->YoinkLocalData('EmailUpdated');

		($this->App->Surface)
		->Wrap('user/dashboard/email', [
			'ConfirmReq' => $this->App->Config[User\Library::ConfConfirmEmailChange],
			'Updated'    => $Updated,
			'Sent'       => $Sent
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/dashboard/settings/password')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	PagePassword():
	void {

		$Updated = $this->App->YoinkLocalData('PasswordUpdated');

		($this->App->Surface)
		->Wrap('user/dashboard/password', [
			'HasNoPassword' => ($this->User->PHash === NULL),
			'Updated'       => $Updated
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/dashboard/settings/auth')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	PageAuth():
	void {

		($this->App->Surface)
		->Wrap('user/dashboard/auth', []);

		return;
	}

}
