<?php

namespace Nether\Atlantis\Util;

use Nether\Atlantis;
use Nether\Common;

use Exception;

class FileUploadImporter {
/*//
presently this exists as lubricant to get things into atlantis's media
tracking system quickly. however the api is decent and maybe should be kept
after the core upload handling gets refactored to not suck.
//*/

	protected Atlantis\Engine
	$App;

	protected string
	$SourceFile;

	protected string
	$OriginalName;

	protected ?Atlantis\Media\File
	$File;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(Atlantis\Engine $App, string $SourceFile, ?string $OriginalName=NULL, bool $Autorun=TRUE) {

		$this->App = $App;
		$this->SourceFile = $SourceFile;
		$this->OriginalName = $OriginalName ?? basename($SourceFile);
		$this->File = NULL;

		if($Autorun)
		$this->Run();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetFileObject():
	?Atlantis\Media\File {

		return $this->File;
	}

	public function
	Run():
	void {

		$UUID = Common\UUID::V7();
		$Temp = "upl/tmp-{$UUID}.jpeg";
		$Storage = $this->App->Storage->Location('Temp');
		$File = NULL;

		////////

		if(isset($this->File))
		throw new Exception('already imported my dude');

		if(!$Storage)
		throw new Common\Error\RequiredDataMissing('Temp', 'storage');

		// copy the file to the application temp area so we can get
		// a file object for it.

		$Storage->Put($Temp, file_get_contents($this->SourceFile));
		$File = $Storage->GetFileObject($Temp);
		unlink($this->SourceFile);

		// scream at the framework to act like this was uploaded via the
		// currently existing api.

		$this->App->Library['Atlantis']->OnUploadFinalise(
			$this->App,
			$UUID,
			$this->OriginalName,
			'default',
			$File
		);

		// and fetch the final resulting object for the framework file.

		$this->File = Atlantis\Media\File::GetByUUID($UUID);
		$this->File->GenerateImageThumbnails();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromUploadItem(Atlantis\Engine $App, array $Item):
	static {

		if(!isset($Item['tmp_name']) || !isset($Item['name']))
		throw new Common\Error\FormatInvalid('row from the _FILES array');

		$Output = new static(
			App: $App,
			SourceFile: $Item['tmp_name'],
			OriginalName: $Item['name'],
			Autorun: TRUE
		);

		return $Output;
	}

	static public function
	FromUploadArray(Atlantis\Engine $App, array $Files):
	Common\Datastore {

		$Output = new Common\Datastore;
		$File = NULL;

		foreach($Files as $File)
		$Output->Push(static::FromUploadItem($App, $File));

		return $Output;
	}

};
