<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

class NetworkJSON
extends Common\Prototype {

	static public function
	FromFile(string $Filename):
	static {

		$JSON = NULL;
		$Data = NULL;

		////////

		if(!file_exists($Filename))
		throw new Common\Error\FileNotFound($Filename);

		if(!is_readable($Filename))
		throw new Common\Error\FileUnreadable($Filename);

		if(!is_string($JSON = file_get_contents($Filename)))
		throw new Common\Error\RequiredDataMissing('JSON File Data');

		if(!is_object($Data = json_decode($JSON)))
		throw new Common\Error\RequiredDataMissing('Valid JSON Object');

		////////

		$Output = new static($Data);

		return $Output;
	}

}
