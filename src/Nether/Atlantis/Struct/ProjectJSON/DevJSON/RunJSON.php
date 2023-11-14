<?php

namespace Nether\Atlantis\Struct\ProjectJSON\DevJSON;

use Nether\Atlantis;
use Nether\Common;

class RunJSON
extends Common\Prototype
implements
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON {

	use
	Common\Package\ToJSON;

	#[Common\Meta\PropertyFactory('FromArray')]
	public array|Common\Struct\CommandList
	$Commands = [];

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Prototype /////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->Commands->Rehydrate();
		$this->Commands->Sort();

		return;
	}

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Interfaces\ToArray ////////////////////////

	public function
	ToArray():
	array {

		$Out = [
			'Commands' => $this->Commands->ToArray()
		];

		return $Out;
	}

};
