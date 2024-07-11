<?php

namespace Nether\Atlantis\Struct\ProjectJSON;

use Nether\Common;

class PermJSON
extends Common\Datastore
implements
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON {

	use
	Common\Package\ToJSON;

	protected function
	OnReady():
	void {

		$this->Remap(
			fn(array|PermJSON\PathModeJSON $D)=>
			is_array($D) ? new PermJSON\PathModeJSON($D) : $D
		);

		return;
	}

	public function
	ToArray():
	array {

		return $this->Values()->Export();
	}

	public function
	HasAnything():
	bool {

		return ($this->Count() > 0);
	}

};
