<?php

namespace Nether\Atlantis\Struct\ProjectJSON\DevJSON;

use Nether\Common;

class ShoveJSON
extends Common\Prototype {

	#[Common\Meta\PropertyFactory('FromArray', 'Steps')]
	public array|Common\Datastore
	$Steps = [];

};
