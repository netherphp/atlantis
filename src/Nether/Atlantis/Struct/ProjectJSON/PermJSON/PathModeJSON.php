<?php

namespace Nether\Atlantis\Struct\ProjectJSON\PermJSON;

use Nether\Atlantis;
use Nether\Common;

class PathModeJSON
extends Common\Prototype
implements
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON {

	use
	Common\Package\ToJSON;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public string
	$Path;

	public int
	$Mode;

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
			'Path' => $this->Path,
			'Mode' => $this->Mode
		];

		return $Out;
	}

};