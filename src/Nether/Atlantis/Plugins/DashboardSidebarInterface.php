<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;

use Nether\Object\Datastore;

interface DashboardSidebarInterface {

	public function
	OnDashboardSidebar(Atlantis\Engine $App, Datastore $Sidebar):
	void;

}
