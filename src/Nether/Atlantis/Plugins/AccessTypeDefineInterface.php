<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;

use Nether\Object\Datastore;

interface AccessTypeDefineInterface {

	// add keys with suggested default value.

	public function
	OnAccessTypeDefine(Atlantis\Engine $App, Datastore $List):
	void;

}
