<?php

namespace Nether\Atlantis\Dashboard;

class AtlantisAdminSidebar
extends SidebarGroup {

	public int
	$Priority = -10;

	public function
	__Construct() {
		parent::__Construct('Admin');

		($this->Items)
		->Push(new SidebarGroupItem('Manage Users', '/ops/users/list', 'mdi-account-group'));

		return;
	}

}