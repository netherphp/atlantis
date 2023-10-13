<?php

namespace Nether\Atlantis\Struct;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

use Nether\Atlantis;
use Nether\Common;

use JsonSerializable;
use Stringable;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////\

class AtlantisProjectWebServer
extends Common\Prototype
implements
	JsonSerializable,
	Stringable {

	const Types = [ 'apachectl' ];

	static public function
	Type(int $Key):
	?string {

		if(isset(static::Types[$Key]))
		return static::Types[$Key];

		return NULL;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public ?string
	$Type = NULL;

	public bool
	$Sudo = TRUE;

	public bool
	$HTTPS = FALSE;

	#[Common\Meta\PropertyFactory('FromArray', 'Domains')]
	public array|Common\Datastore
	$Domains = [ ];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	JsonSerialize():
	array {

		return $this->ToArray();
	}

	public function
	__ToString():
	string {

		return $this->ToString();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	ToArray():
	array {

		return [
			'Type'    => $this->Type,
			'Sudo'    => $this->Sudo,
			'HTTPS'   => $this->HTTPS,
			'Domains' => $this->Domains->GetData()
		];
	}

	public function
	ToArrayFlat():
	array {

		return array_map(
			fn(mixed $V)
			=> match(TRUE) {
				(is_object($V) || is_array($V))
				=> json_encode($V),

				default
				=> $V
			},
			$this->ToArray()
		);
	}

	public function
	ToJSON():
	string {

		return Common\Filters\Text::Tabbify(
			json_encode($this, JSON_PRETTY_PRINT)
		);
	}

	public function
	ToString():
	string {

		return $this->ToJSON();
	}

};
