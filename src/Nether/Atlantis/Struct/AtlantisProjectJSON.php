<?php

namespace Nether\Atlantis\Struct;

use Nether\Common;

class AtlantisProjectJSON
extends Common\Prototype {

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$Dirs;

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$Links;

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
	Read():
	static {

		if(isset($this->File['Dirs']))
		if(is_iterable($this->File['Dirs'])) {
			($this->Dirs)
			->SetData($this->File['Dirs'])
			->Remap(fn(object $D)=> new Common\Filesystem\Directory($D));
		}

		if(isset($this->File['Links']))
		if(is_iterable($this->File['Links'])) {
			($this->Links)
			->SetData($this->File['Links'])
			->Remap(fn(object $L)=> new Common\Filesystem\Symlink($L));
		}

		return $this;
	}

	public function
	Write():
	static {

		$this->File->Write();
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromFile(string $Filename):
	static {

		return new static([
			'File' => Common\Datastore::NewFromFile($Filename)
		]);
	}

}
