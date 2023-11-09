<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

class ProjectJSON
extends Common\Prototype
implements
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON {

	use
	Common\Package\ToJSON;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyObjectify]
	public Common\Struct\DirectoryList
	$Dirs;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyObjectify]
	public Common\Struct\SymlinkList
	$Symlinks;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyObjectify]
	public Atlantis\Struct\AtlantisProjectWebServer
	$Web;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyObjectify]
	public Atlantis\Struct\AtlantisProjectSSL
	$SSL;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyObjectify]
	public Atlantis\Struct\ProjectJSON\DevJSON
	$Dev;

	////////

	protected Common\Datastore
	$File;

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Prototype /////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->Read();

		return;
	}

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Interfaces\ToArray ////////////////////////

	public function
	ToArray():
	array {

		$Out = [];
		$Prop = NULL;

		////////

		foreach(['Dirs', 'Symlinks'] as $Prop) {
			if($this->{$Prop}->Count())
			$Out[$Prop] = $this->{$Prop}->GetData();
		}

		foreach(['Web', 'SSL', 'Dev'] as $Prop) {
			if($this->{$Prop}->HasAnything())
			$Out[$Prop] = $this->{$Prop}->ToArray();
		}

		return $Out;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetFilename():
	?string {

		return $this->File->GetFilename();
	}

	public function
	SetFilename(string $Filename):
	static {

		$this->File->SetFilename($Filename);
		return $this;
	}

	public function
	Read():
	static {

		if(isset($this->File['Dirs']) && is_array($this->File['Dirs']))
		$this->Dirs = Common\Struct\DirectoryList::FromArray($this->File['Dirs']);

		if(isset($this->File['Symlinks']) && is_array($this->File['Symlinks']))
		$this->Symlinks = Common\Struct\SymlinkList::FromArray($this->File['Symlinks']);

		if(isset($this->File['Dev']) && is_array($this->File['Dev']))
		$this->Dev = ProjectJSON\DevJSON::FromArray($this->File['Dev']);

		if(isset($this->File['Web']) && is_iterable($this->File['Web']))
		$this->Web = AtlantisProjectWebServer::FromArray($this->File['Web']);

		if(isset($this->File['SSL']) && is_iterable($this->File['SSL']))
		$this->SSL = AtlantisProjectSSL::FromArray($this->File['SSL']);

		return $this;
	}

	public function
	Sort():
	static {

		/*
		$this->Dirs->Sort(
			fn(Common\Filesystem\Directory $A, Common\Filesystem\Directory $B)
			=> $A->Path <=> $B->Path
		);

		$this->Links->Sort(
			fn(Common\Filesystem\Symlink $A, Common\Filesystem\Symlink $B)
			=> $A->Path <=> $B->Path
		);
		*/

		return $this;
	}

	public function
	Write():
	static {

		$JSON = $this->ToJSON();
		$Filename = $this->File->GetFilename();
		$Bytes = file_put_contents($Filename, $JSON);

		// $this->File->Read();

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromFile(string $Filename, bool $Create=TRUE):
	static {

		if(!file_exists($Filename))
		if($Create && is_writable(dirname($Filename)))
		file_put_contents($Filename, '{}');

		////////

		return new static([
			'File' => Common\Datastore::FromFile($Filename)
		]);
	}

	static public function
	FromApp(Atlantis\Engine $App):
	static {

		$ProjectFile = $App->FromProjectRoot('atlantis.json');
		$EnvFile = $App->FromConfEnv('atlantis.json');

		// load in the base config

		$Data = Common\Datastore::FromStackMerged(
			file_exists($ProjectFile)
			? Common\Datastore::FromFile($ProjectFile)
			: [],

			file_exists($EnvFile)
			? Common\Datastore::FromFile($EnvFile)
			: []
		);

		$Output = new static([ 'File'=> $Data ]);

		return $Output;
	}

	static public function
	FromAppStacked(Atlantis\Engine $App):
	Common\Datastore {

		$Files = (
			Common\Datastore::FromArray([
				'App' => $App->FromProjectRoot('atlantis.json'),
				'Env' => $App->FromConfEnv('atlantis.json')
			])
			->Remap(
				fn(string $File)
				=> static::FromFile($File, TRUE)
			)
		);

		return $Files;
	}

}
