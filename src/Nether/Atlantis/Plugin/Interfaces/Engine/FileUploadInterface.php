<?php

namespace Nether\Atlantis\Plugin\Interfaces\Engine;

use Nether\Atlantis;
use Nether\Storage;

interface FileUploadInterface {

	public function
	WillHandleUpload(string $Type, string $UUID, Atlantis\Media\File $Entity, ?Storage\File $File):
	bool;

	public function
	OnHandleUpload(string $Type, string $UUID, Atlantis\Media\File $Entity, ?Storage\File $File):
	void;

};
