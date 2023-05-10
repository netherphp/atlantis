<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;
use Nether\Storage;

use Nether\Common\Datastore;

interface UploadHandlerInterface {

	public function
	OnUploadFinalise(Atlantis\Engine $App, string $UUID, string $Name, Storage\File $File):
	void;

}
