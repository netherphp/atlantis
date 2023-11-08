<?php

namespace Nether\Atlantis\Struct\ProjectJSON\DevJSON;

use Nether\Atlantis;
use Nether\Common;

class RunJSON
extends Common\Prototype {

	#[Common\Meta\PropertyFactory('FromArray')]
	public array|Common\Struct\CommandList
	$Commands = [];

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->Commands->Commandify();
		$this->Commands->Sort();

		return;
	}

};
