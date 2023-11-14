<?php

namespace Nether\Atlantis\Struct;

use Nether\Common;

class NetworkJSON
extends Common\Prototype {

	protected array|Common\Datastore
	$Data;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if(!isset($this->Data))
		$this->Data = [];

		if(is_array($this->Data))
		$this->Data = Common\Datastore::FromArray($this->Data);

		($this->Data)
		->Remap(fn(array $I)=> new NetworkItem($I));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Get(string $Site):
	?NetworkItem {

		if($this->Data->HasKey($Site))
		return $this->Data[$Site];

		return NULL;
	}

	public function
	GetSocials():
	Common\Datastore {

		$Output = $this->Data->Map(
			fn(NetworkItem $I)=> $I->Social
		);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromJSON(string $JSON):
	static {

		$Data = json_decode($JSON, TRUE);

		if(!is_array($Data))
		throw new Common\Error\RequiredDataMissing('Valid JSON', 'object');

		////////

		$Output = new static([ 'Data'=> $Data ]);

		return $Output;
	}

	static public function
	FromFile(string $Filename):
	static {

		if(!file_exists($Filename))
		throw new Common\Error\FileNotFound($Filename);

		if(!is_readable($Filename))
		throw new Common\Error\FileUnreadable($Filename);

		$JSON = file_get_contents($Filename);
		if(!$JSON) $JSON = [];

		return static::FromJSON($JSON);
	}

}
