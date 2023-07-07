<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;

use Nether\Common\Datastore;

interface DashboardElementInterface {

	public function
	OnDashboardElement(Atlantis\Engine $App, Datastore $Main):
	void;

}
