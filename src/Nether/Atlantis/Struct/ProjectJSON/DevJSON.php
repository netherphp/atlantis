<?php

namespace Nether\Atlantis\Struct\ProjectJSON;

use Nether\Common;

class DevJSON
extends Common\Prototype {

	#[Common\Meta\PropertyFactory('FromArray', 'Shove')]
	public array|DevJSON\ShoveJSON
	$Shove = [];

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {


		return;
	}

};
