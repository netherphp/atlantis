<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

use Exception;

class SocialData
extends Common\Prototype {

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

