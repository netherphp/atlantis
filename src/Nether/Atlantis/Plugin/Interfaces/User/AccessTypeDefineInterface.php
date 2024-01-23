<?php

namespace Nether\Atlantis\Plugin\Interfaces\User;

use Nether\Common;

interface AccessTypeDefineInterface {

	#[Common\Meta\Info('Return a list of Atlantis\User\AccessTypeDef objects.')]
	public function
	Get():
	iterable;

};
