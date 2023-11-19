<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;
use Nether\Common;

interface AccessTypeDefineInterface {

	#[Common\Meta\Info('Return a list of Atlantis\User\AccessTypeDef objects.')]
	public function
	Get():
	iterable;

}
