<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class ContactDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/contact/list')]
	#[Atlantis\Meta\RouteAccessType('Nether.Contact.Log.View', 'eq', 1)]
	public function
	HandleList():
	void {

		$Items = Atlantis\Struct\ContactEntry::Find([
			'Page'  => 1,
			'Limit' => 25,
			'Sort'  => 'newest'
		]);

		($this->Surface)
		->Wrap('dashboard/contact/index', [
			'Items' => $Items
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/dashboard/contact/view/:MessageID:')]
	#[Atlantis\Meta\RouteAccessType('Nether.Contact.Log.View', 'eq', 1)]
	public function
	HandleView(int $MessageID):
	void {

		$Message = Atlantis\Struct\ContactEntry::GetByID($MessageID);

		if(!$Message)
		$this->Quit(1, "Message ID {$MessageID} not found.");

		////////

		($this->Surface)
		->Wrap('dashboard/contact/view', [
			'Message' => $Message
		]);

		return;
	}

}


