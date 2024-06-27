<?php

namespace Nether\Atlantis\Packages;

use Nether\Database;
use Nether\Common;

#[Common\Meta\Date('2023-08-09')]
#[Common\Meta\Info('Bolt onto Prototype based classes to include the extra metadata fields. Remember to include the Interface as well on the class.')]
trait ExtraData {

	#[Database\Meta\TypeText]
	public ?string
	$ExtraJSON = '{}';

	#[Common\Meta\PropertyFactory('FromJSON', 'ExtraJSON')]
	#[Common\Meta\PropertyListable('DescribeForPublicAPI')]
	public ?Common\Protostore
	$ExtraData;

	public function
	HasExtraData(string $Key, bool $AndNotEmpty=FALSE):
	bool {

		if(!$AndNotEmpty)
		return $this->ExtraData->HasKey($Key);

		return (TRUE
			&& $this->ExtraData->HasKey($Key)
			&& !!$this->ExtraData->Get($Key)
		);
	}

	public function
	GetExtraData(string $Key):
	mixed {

		return $this->ExtraData->Get($Key);
	}

};
