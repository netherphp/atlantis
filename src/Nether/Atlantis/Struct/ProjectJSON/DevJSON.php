<?php

namespace Nether\Atlantis\Struct\ProjectJSON;

use Nether\Common;

class DevJSON
extends Common\Prototype {

	#[Common\Meta\PropertyFactory('FromArray')]
	public array|DevJSON\ShoveJSON
	$Shove = [];

	#[Common\Meta\PropertyFactory('FromArray')]
	public array|DevJSON\RunJSON
	$Run = [];

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {


		return;
	}

};
