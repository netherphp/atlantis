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
		->Push(new SidebarGroupItem('Change Email', '/dashboard/settings/email', 'mdi-email'))
		->Push(new SidebarGroupItem('Change Password', '/dashboard/settings/password', 'mdi-key'))
		->Push(new SidebarGroupItem('Manage Login Methods', '/dashboard/settings/auth', 'mdi-link'))
		->Push(new SidebarGroupItem('Log Out', '/logout', 'mdi-hand-wave'));

		return;
	}

}