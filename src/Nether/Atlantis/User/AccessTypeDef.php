<?php

namespace Nether\Atlantis\User;

use Atlantis;

class AccessTypeDef {

	public string
	$Key;

	public int
	$Value;

	public ?string
	$Info;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Key, int $Value, ?string $Info=NULL) {

		$this->Key = $Key;
		$this->Value = $Value;
		$this->Info = $Info;

		return;
	}

}
