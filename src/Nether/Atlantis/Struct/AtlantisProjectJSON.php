<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

class AtlantisProjectJSON
extends Common\Prototype {

	public ?Atlantis\Struct\AtlantisProjectSSL
	$SSL = NULL;

	public ?Atlantis\Struct\AtlantisProjectWebServer
	$Web = NULL;

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$Dirs;

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$Links;

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$StepsForShove;

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$StepsForRelease;

	////////

	protected Common\Datastore
	$File;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->Read();

		return;
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

		if(isset($this->File['SSL']))
		if(isset($this->File['SSL']))
		$this->SSL = new Atlantis\Struct\AtlantisProjectSSL($this->File['SSL']);

		if(isset($this->File['Web']))
		if(isset($this->File['Web']))
		$this->Web = new Atlantis\Struct\AtlantisProjectWebServer($this->File['Web']);

		if(isset($this->File['Dirs']))
		if(isset($this->File['Dirs'])) {
			($this->Dirs)
			->SetData($this->File['Dirs'])
			->Remap(fn(array $D)=> new Common\Filesystem\Directory($D));
		}

		if(isset($this->File['Links']))
		if(isset($this->File['Links'])) {
			($this->Links)
			->SetData($this->File['Links'])
			->Remap(fn(array $L)=> new Common\Filesystem\Symlink($L));
		}

		if(isset($this->File['StepsForRelease']))
		if(isset($this->File['StepsForRelease'])) {
			($this->StepsForRelease)
			->SetData($this->File['StepsForRelease']);
		}

		if(isset($this->File['StepsForShove']))
		if(isset($this->File['StepsForShove'])) {
			($this->StepsForShove)
			->SetData($this->File['StepsForShove']);
		}

		return $this;
	}

	public function
	Sort():
	static {

		$this->Dirs->Sort(
			fn(Common\Filesystem\Directory $A, Common\Filesystem\Directory $B)
			=> $A->Path <=> $B->Path
		);

		$this->Links->Sort(
			fn(Common\Filesystem\Symlink $A, Common\Filesystem\Symlink $B)
			=> $A->Path <=> $B->Path
		);

		return $this;
	}

	public function
	Write():
	static {

		$this->Sort();

		////////

		if(isset($this->SSL))
		$this->File['SSL'] = $this->SSL->ToArray();

		if(isset($this->Web))
		$this->File['Web'] = $this->Web->ToArray();

		if(isset($this->Dirs) && $this->Dirs->Count())
		$this->File['Dirs'] = $this->Dirs->Values();

		if(isset($this->Links) && $this->Links->Count())
		$this->File['Links'] = $this->Links->Values();

		////////

		$this->File->Write();

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
			'File' => Common\Datastore::NewFromFile($Filename)
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
