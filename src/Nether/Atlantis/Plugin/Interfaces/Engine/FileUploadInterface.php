<?php

namespace Nether\Atlantis\Plugin\Interfaces\Engine;

use Nether\Atlantis;
use Nether\Common;
use Nether\Storage;

interface FileUploadInterface {

	public function
	WillHandleUpload(string $Type, Atlantis\Media\File $Entity, Common\Datafilter $Data):
	bool;

	public function
	OnHandleUpload(string $Type, Atlantis\Media\File $Entity, Common\Datafilter $Data):
	void;

};
