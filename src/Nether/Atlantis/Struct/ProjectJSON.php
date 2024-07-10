<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

use Exception;

class ProjectJSON
extends Common\Prototype
implements
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON,
	Common\Interfaces\ToString {

	use
	Common\Package\ToJSON,
	Common\Package\ToString;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-11-10')]
	#[Common\Meta\Info('Filename of the JSON file, not stored within it.')]
	public string
	$Filename;

	#[Common\Meta\Date('2023-11-08')]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFactory('FromArray')]
	public array|Common\Struct\DirectoryList
	$Dirs = [];

	#[Common\Meta\Date('2023-11-08')]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFactory('FromArray')]
	public array|Common\Struct\SymlinkList
	$Symlinks = [];

	#[Common\Meta\Date('2023-11-08')]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFactory('FromArray')]
	public array|ProjectJSON\WebJSON
	$Web = [];

	#[Common\Meta\Date('2023-11-08')]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFactory('FromArray')]
	public array|ProjectJSON\CertJSON
	$Cert = [];

	#[Common\Meta\Date('2023-11-08')]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFactory('FromArray')]
	public array|ProjectJSON\DevJSON
	$Dev = [];

	#[Common\Meta\Date('2024-03-28')]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFactory('FromArray')]
	public array|ProjectJSON\DatabaseJSON
	$DB = [];

	#[Common\Meta\Date('2024-06-09')]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFactory('FromArray')]
	public array|ProjectJSON\PermJSON
	$Perms = [];

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Interfaces\ToArray ////////////////////////

	#[Common\Meta\Date('2023-11-08')]
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

		foreach(['Perms', 'Web', 'Cert', 'Dev', 'DB'] as $Prop) {
			if($this->{$Prop}->HasAnything())
			$Out[$Prop] = $this->{$Prop}->ToArray();
		}

		return $Out;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-11-10')]
	public function
	Write():
	bool {

		$this->Prewrite();

		$JSON = $this->ToJSON();
		$Bytes = file_put_contents($this->Filename, $JSON);

		if($Bytes === FALSE)
		throw new Exception('failed to write (permissions?)');

		if($Bytes !== mb_strlen($JSON))
		throw new Exception('written did not match expected');

		return TRUE;
	}

	protected function
	Prewrite():
	void {

		// pivot the file creation magic to on write rather than on
		// read because idk why that did not make more sense to me during
		// the prototype phase.

		$Dir = dirname($this->Filename);
		$Lnk = NULL;

		// if we have a dir then there is nothing to do just continue
		// saving the file there. try to accept a symlink to a directory.

		if(file_exists($Dir)) {
			if(is_dir($Dir))
			return;

			if(is_link($Dir)) {
				$Lnk = readlink($Dir);

				if($Lnk && is_dir($Lnk))
				return;
			}

			throw new Exception('parent directory exists as a file?');
		}

		// try to make the directory we want to save to.

		if(!Common\Filesystem\Util::MkDir($Dir))
		throw new Exception('failed to create parent directory.');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-11-10')]
	#[Common\Meta\Info('Parse a JSON dump.')]
	static public function
	FromJSON(string $JSON):
	static {

		$Data = json_decode($JSON, TRUE);

		if(!is_array($Data))
		$Data = [];

		return new static($Data);
	}

	#[Common\Meta\Date('2023-11-09')]
	#[Common\Meta\Info('Load a JSON file from disk.')]
	static public function
	FromFile(string $Filename, bool $Create=FALSE):
	static {

		// @TODO 2024-04-01 remove the create stuff from here because
		// that is being moved to on write.

		$JSON = '';

		////////

		if(!file_exists($Filename))
		if($Create && is_writable(dirname($Filename)))
		file_put_contents($Filename, '{}');

		////////

		if(file_exists($Filename))
		$Output = static::FromJSON(
			file_get_contents($Filename) ?: '{}'
		);

		else
		$Output = new static;

		$Output->Filename = $Filename;

		////////

		return $Output;
	}

	#[Common\Meta\Date('2023-11-10')]
	#[Common\Meta\Info('Fetch all the config files in play from Atlantis.')]
	static public function
	FromApp(Atlantis\Engine $App):
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
