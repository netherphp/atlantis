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

		$SidebarItems = $this->FetchSidebarItems();
		$MainItems = $this->FetchMainItems();

		$SidebarItems->Sort(
			function(Atlantis\Dashboard\SidebarGroup $A, Atlantis\Dashboard\SidebarGroup $B) {
				if($A->Priority !== $B->Priority)
				return $B->Priority <=> $A->Priority;

				return $A->Title <=> $B->Title;
			}
		);

		$MainItems->Sort(
			function(Atlantis\Dashboard\Element $A, Atlantis\Dashboard\Element $B) {
				if($A->Priority !== $B->Priority)
				return $B->Priority <=> $A->Priority;

				return $A->Title <=> $B->Title;
			}
		);

		($this->App->Surface)
		->Wrap('dashboard/index', [
			'SidebarItems' => $SidebarItems,
			'MainItems'    => $MainItems
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	FetchSidebarItems():
	Common\Datastore {

		$Lib = NULL;
		$Items = new Common\Datastore;

		foreach($this->App->Library as $Lib) {
			if($Lib instanceof Atlantis\Plugins\DashboardSidebarInterface)
			$Lib->OnDashboardSidebar($this->App, $Items);
		}

		return $Items;
	}

	protected function
	FetchMainItems():
	Common\Datastore {

		$Lib = NULL;
		$Items = new Common\Datastore;

		foreach($this->App->Library as $Lib) {
			if($Lib instanceof Atlantis\Plugins\DashboardElementInterface)
			$Lib->OnDashboardElement($this->App, $Items);
		}

		return $Items;
	}

}
