<?php

namespace Nether\Atlantis\Dashboard;

class AtlantisAccountSidebar
extends SidebarGroup {

	public int
	$Priority = -5;

	public function
	__Construct() {
		parent::__Construct('Account');

		($this->Items)
		->Push(new SidebarGroupItem('Change Email', '/dashboard/settings/email'))
		->Push(new SidebarGroupItem('Change Password', '/dashboard/settings/password'))
		->Push(new SidebarGroupItem('Manage Connections', '/dashboard/settings/auth'));

		return;
	}

}