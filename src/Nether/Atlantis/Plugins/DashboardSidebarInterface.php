<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;

use Nether\Common\Datastore;

interface DashboardSidebarInterface {

	public function
	OnDashboardSidebar(Atlantis\Engine $App, Datastore $Sidebar):
	void;

}
