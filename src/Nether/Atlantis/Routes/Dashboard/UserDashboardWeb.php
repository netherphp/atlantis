<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

use Nether\Atlantis\Plugin\Interfaces\Dashboard\InfoWidgetInterface;

class UserDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PageDashboard():
	void {

		$InfoWidgets = (
			($this->App->Plugins)
			->GetInstanced(InfoWidgetInterface::class)
			->Filter(fn(InfoWidgetInterface $W)=> $W->Allow())
			->Sort(
				fn(InfoWidgetInterface $A, InfoWidgetInterface $B)
				=> $A->GetSorting() <=> $B->GetSorting()
			)
		);

		($this->App->Surface)
		->Set('Page.Title', 'Dashboard')
		->PushInto('Page.Body.Classes', 'atl-page-dashboard')
		->Area('dashboard/index', [
			'InfoWidgets' => $InfoWidgets
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/dashboard/settings/alias')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Atlantis\Meta\UserActivationFlow]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PageAlias():
	void {

		($this->Data)
		->Updated(Common\Filters\Numbers::BoolType(...));

		($this->App->Surface)
		->PushInto('Page.Body.Classes', 'atl-page-dashboard')
		->Wrap('dashboard/alias', [
			'Updated' => $this->Data->Updated
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/dashboard/settings/email')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Atlantis\Meta\UserActivationFlow]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PageEmail():
	void {

		($this->Query)
		->Confirm(Common\Filters\Text::Base64Decode(...));

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
		->PushInto('Page.Body.Classes', 'atl-page-dashboard')
		->Wrap('dashboard/email', [
			'ConfirmReq' => $this->App->Config[User\Library::ConfConfirmEmailChange],
			'Updated'    => $Updated,
			'Sent'       => $Sent
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/dashboard/settings/password')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PagePassword():
	void {

		$Updated = $this->App->YoinkLocalData('PasswordUpdated');

		($this->App->Surface)
		->PushInto('Page.Body.Classes', 'atl-page-dashboard')
		->Wrap('dashboard/password', [
			'HasNoPassword' => ($this->User->PHash === NULL),
			'Updated'       => $Updated
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/dashboard/settings/auth')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PageAuth():
	void {

		($this->App->Surface)
		->PushInto('Page.Body.Classes', 'atl-page-dashboard')
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
