<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;
use Nether\Storage;

interface UploadHandlerInterface {

	public function
	OnUploadFinalise(Atlantis\Engine $App, string $UUID, string $Name, string $Type, Storage\File $File):
	void;
	/*//
	given the UUID for this file upload, the original name the file had on
	user's system, type of file we are trying to upload, and the file itself.

	the type can be used to short circuit if you even want to care about
	processing this upload and means nothing outside the series of form
	transactions during upload.

	most common use for this will be the move the file to another location
	for use in other things outside of the built-in media tracking system.

	each library that registers will be given a chance to respond to handling
	this file upload as long as it remains to exist. once it is moved no
	other libraries will be asked.
	//*/

}
