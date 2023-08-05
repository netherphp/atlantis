<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use JsonSerializable;

class SocialData
extends Common\Prototype
implements
	IteratorAggregate,
	JsonSerializable {

	const Icons = [
		'Website'   => 'mdi-web',
		'Facebook'  => 'si-facebook',
		'Instagram' => 'si-instagram',
		'Threads'   => 'si-threads',
		'TikTok'    => 'si-tiktok',
		'Twitter'   => 'si-twitter',
		'YouTube'   => 'si-youtube'
	];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyObjectify]
	protected Common\Datastore
	$Data;

	////////////////////////////////////////////////////////////////
	// implements JsonSerializable /////////////////////////////////

	public function
	JsonSerialize():
	mixed {

		return $this->Data->GetData();
	}

	////////////////////////////////////////////////////////////////
	// implements IteratorAggregate ////////////////////////////////

	public function
	GetIterator():
	ArrayIterator {

		// return an iterator that will only process the data if it
		// actually gets ran over.

		$Keepers = (
			$this->Data
			->Distill(fn(string $URL)=> $URL !== NULL)
			->GetData()
		);

		return new ArrayIterator(array_map(
			(fn(mixed $Key)=> $this->Get($Key)),
			array_combine(
				array_keys($Keepers),
				array_keys($Keepers)
			)
		));
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Has(string $Key):
	bool {

		if(!$this->Data->HasKey($Key))
		return FALSE;

		return isset($this->Data[$Key]);
	}

	public function
	Get(string $Key):
	mixed {

		return $this->Data[$Key];
	}

	public function
	Set(string $Key, mixed $Val):
	static {

		$this->Data[$Key] = $Val;
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetIconStyleClass(string $Key):
	?string {

		$Icon = NULL;

		if(array_key_exists($Key, static::Icons))
		$Icon = static::Icons[$Key];

		////////

		if($Icon && str_starts_with($Icon, 'si-'))
		$Icon = "si {$Icon}";

		elseif($Icon && str_starts_with($Icon, 'mdi-'))
		$Icon = "mdi {$Icon}";

		////////

		return $Icon;
	}

	public function
	SetData(array $Data):
	static {

		$this->Data->SetData($Data);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromJSON(string $JSON):
	static {

		$Data = json_decode($JSON, TRUE);

		if(!$Data || !is_array($Data))
		$Data = [];

		////////

		$Output = new static;
		$Output->SetData($Data);

		return $Output;
	}

}
