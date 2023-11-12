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

		foreach(['Web', 'Cert', 'Dev'] as $Prop) {
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

		$JSON = $this->ToJSON();
		$Bytes = file_put_contents($this->Filename, $JSON);

		if($Bytes === FALSE)
		throw new Exception('failed to write (permissions?)');

		if($Bytes !== mb_strlen($JSON))
		throw new Exception('written did not match expected');

		return TRUE;
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
	FromFile(string $Filename, bool $Create=TRUE):
	static {

		if(!file_exists($Filename))
		if($Create && is_writable(dirname($Filename)))
		file_put_contents($Filename, '{}');

		////////

		$Output = static::FromJSON(
			file_get_contents($Filename) ?: '{}'
		);

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
