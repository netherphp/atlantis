<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Atlantis;

use Nether\Object\Datastore;

interface SidebarInterface {

	public function
	OnDashboardSidebar(Atlantis\Engine $App, Datastore $Sidebar):
	void;

}
