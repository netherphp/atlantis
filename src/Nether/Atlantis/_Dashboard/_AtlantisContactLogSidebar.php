<?php

namespace Nether\Atlantis\Dashboard;

class AtlantisContactLogSidebar
extends SidebarGroup {

	public int
	$Priority = -5;

	public function
	__Construct() {
		parent::__Construct('Contact Us');

		($this->Items)
		->Push(new SidebarGroupItem('View Messages', '/dashboard/contact/list', 'mdi-email-multiple'));

		return;
	}

}