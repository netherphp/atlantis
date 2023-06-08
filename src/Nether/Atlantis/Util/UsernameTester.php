<?php

namespace Nether\Atlantis\Util;

use Exception;
use Nether\Common\Struct\DatafilterItem;
use Nether\Atlantis\Error\User\AliasInvalidTooShort;
use Nether\Atlantis\Error\User\AliasInvalidFirstLast;
use Nether\Atlantis\Error\User\AliasInvalidChars;
use Nether\Atlantis\Error\User\AliasInvalidDogpiling;

class UsernameTester {

	public function
	IsOK(string $Input):
	bool {

		return FALSE;
	}

	static public function
	IsValid(string $Input):
	bool {

		$Valid = TRUE;

		try { static::TryValid($Input); }
		catch(Exception $Error) { $Valid = FALSE; }

		return $Valid;
	}

	static public function
	TryValid(string $Input):
	bool {

		if(strlen($Input) < 2)
		throw new AliasInvalidTooShort;

		if(preg_match('/[^a-zA-Z0-9-_\.]/', $Input))
		throw new AliasInvalidChars;

		if(preg_match('/[-\.]{2,}/', $Input))
		throw new AliasInvalidDogpiling;

		if(!preg_match('/^[a-zA-Z]/', $Input))
		throw new AliasInvalidFirstLast;

		if(preg_match('/[-\.]$/', $Input))
		throw new AliasInvalidFirstLast;

		return TRUE;
	}

	static public function
	ValidUsernameFilter(string|DatafilterItem $Input):
	?string {

		if($Input instanceof DatafilterItem)
		$Input = $Input->Value;

		if(static::IsValid($Input))
		return $Input;

		return NULL;
	}

}
