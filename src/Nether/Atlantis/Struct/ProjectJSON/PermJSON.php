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

		$this->Remap(fn($D)=> new PermJSON\PathModeJSON($D));

		return;
	}

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Interfaces\ToArray ////////////////////////

	//public function
	//ToArray():
	//array {

	//	return $this->ToArray();
	//}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasAnything():
	bool {

		return (FALSE || ($this->Count() > 0));
	}

};
