<?php

namespace Nether\Atlantis\Dashboard;

class AtlantisAdminSidebar
extends SidebarGroup {

	public int
	$Priority = -10;

	public function
	__Construct() {
		parent::__Construct('System');

		($this->Items)
		->Push(new SidebarGroupItem('System Admin', '/ops', 'mdi-account-hard-hat'))
		->Push(new SidebarGroupItem('API Tool', '/ops/api', 'mdi-account-hard-hat'))
		->Push(new SidebarGroupItem('Manage Users', '/ops/users/list', 'mdi-api'));

		return;
	}

}