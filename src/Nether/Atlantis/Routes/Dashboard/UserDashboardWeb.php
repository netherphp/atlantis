<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

use Nether\Object\Datastore;
use Nether\Atlantis\Dashboard\SidebarGroup;

class UserDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	PageDashboard():
	void {

		$SidebarItems = new Datastore;
		$MainItems = new Datastore;

		////////

		$this->App->Flow(
			'Atlantis.Dashboard.SidebarItems',
			[ 'Items'=> $SidebarItems ]
		);

		//$this->App->Flow(
		//	'Atlantis.Dashboard.Items',
		//	[ 'Items'=> $MainItems ]
		//);

		$SidebarItems->Sort(
			fn(SidebarGroup $A, SidebarGroup $B)
			=> $B->Priority <=> $A->Priority
		);

		//$MainItems->Sort(
		//	fn(SidebarGroup $A, SidebarGroup $B)
		//	=> $B->Priority <=> $A->Priority
		//);

		($this->App->Surface)
		->Wrap('dashboard/index', [
			'SidebarItems' => $SidebarItems,
			'MainItems'    => $MainItems
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/dashboard/settings/email')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Atlantis\Meta\UserActivationFlow]
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

				$this->App->SetLocalData(
					'EmailUpdated',
					($this->User->Activated ? 1 : 2)
				);

				$this->User->Update([
					'Email'     => $Confirm->Email,
					'Activated' => 1
				]);

				$Confirm->Drop();
			}

			$this->Goto('/dashboard/settings/email');
		}

		////////

		$Sent = $this->App->YoinkLocalData('EmailConfirmSent');
		$Updated = $this->App->YoinkLocalData('EmailUpdated');

		($this->App->Surface)
		->Wrap('dashboard/email', [
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
		->Wrap('dashboard/password', [
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
		->Wrap('dashboard/auth', []);

		return;
	}

}
