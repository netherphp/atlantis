<?php

namespace Nether\Atlantis\Struct\ProjectJSON;

use Nether\Common;

class DevJSON
extends Common\Prototype
implements
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON {

	use
	Common\Package\ToJSON;

	#[Common\Meta\PropertyFactory('FromArray')]
	public array|DevJSON\RunJSON
	$Run = [];

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Prototype /////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {


		return;
	}

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Interfaces\ToArray ////////////////////////

	public function
	ToArray():
	array {

		$Out = [
			'Run' => $this->Run->ToArray()
		];

		return $Out;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasAnything():
	bool {

		return (FALSE || ($this->Run->Commands->Count() > 0));
	}

};
