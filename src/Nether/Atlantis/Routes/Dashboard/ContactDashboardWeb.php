<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class ContactDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/contact/list')]
	#[Atlantis\Meta\RouteAccessType(Atlantis\Key::AccessContactLogManage, 'eq', 1)]
	public function
	HandleList():
	void {

		$Items = Atlantis\Struct\ContactEntry::Find([
			'Page'  => 1,
			'Limit' => 25,
			'Sort'  => 'newest'
		]);

		($this->Surface)
		->Set('Page.Title', 'Contact Log')
		->Area('dashboard/contact/index', [
			'Items' => $Items
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/dashboard/contact/view/:MessageID:')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.ContactLog.Manage', 'eq', 1)]
	public function
	HandleView(int $MessageID):
	void {

		$Message = Atlantis\Struct\ContactEntry::GetByID($MessageID);

		if(!$Message)
		$this->Quit(1, "Message ID {$MessageID} not found.");

		////////

		($this->Surface)
		->Set('Page.Title', sprintf('Contact Log: Message #%d', $Message->ID))
		->Area('dashboard/contact/view', [
			'Message' => $Message
		]);

		return;
	}

}


