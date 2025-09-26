<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class SocialData
extends Common\Prototype
implements
	IteratorAggregate,
	JsonSerializable {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	Website   = 'Website',
	Facebook  = 'Facebook',
	Instagram = 'Instagram',
	LinkedIn  = 'LinkedIn',
	Threads   = 'Threads',
	TikTok    = 'TikTok',
	Twitter   = 'Twitter',
	YouTube   = 'YouTube',
	Bluesky   = 'Bluesky';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	Icons = [
		self::Website   => 'mdi-web',
		self::Facebook  => 'si-facebook',
		self::Instagram => 'si-instagram',
		self::LinkedIn  => 'si-linkedin',
		self::Threads   => 'si-threads',
		self::TikTok    => 'si-tiktok',
		self::Twitter   => 'si-twitter',
		self::YouTube   => 'si-youtube',
		self::Bluesky   => 'si-bluesky'
	];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyFactory('FromArray', 'Data')]
	protected array|Common\Datastore
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

		$Keepers = $this->GetArray();

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
	HasAnyItems():
	bool {

		return count($this->GetArray()) !== 0;
	}

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
	GetByList(...$Argv):
	Common\Datastore {

		$Output = new Common\Datastore;
		$Arg = NULL;

		foreach($Argv as $Arg)
		if($this->Has($Arg))
		$Output[$Arg] = $this->Get($Arg);

		return $Output;
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
	GetArray():
	array {

		$Keepers = (
			$this->Data
			->Distill(fn(?string $URL)=> $URL !== NULL)
			->GetData()
		);

		return $Keepers;
	}

	public function
	GetCount():
	int {

		return $this->Data->Count();
	}

	public function
	GetIconStyleClass(string $Key):
	?string {

		$Icon = 'mdi-link-variant';

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
	GetBtnStyleClass(string $Key):
	?string {

		$Prefab = sprintf(
			'btn-%s',
			Common\Filters\Text::SlottableKey($Key)
		);

		return $Prefab;
	}

	public function
	GetFilteredURL(string $Key, string $URL):
	string {

		$Prefab = "{$Key}URL";

		////////

		if(method_exists(Common\Filters\Links::class, $Prefab))
		return call_user_func(
			[ Common\Filters\Links::class, $Prefab ],
			$URL
		);

		////////

		return $URL;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromArray(iterable|object $Data):
	static {

		$Output = new static([ 'Data'=> $Data ]);

		return $Output;
	}

	static public function
	FromJSON(string $JSON):
	static {

		$Data = json_decode($JSON, TRUE);

		if(!$Data || !is_array($Data))
		$Data = [];

		////////

		return static::FromArray($Data);
	}

}
