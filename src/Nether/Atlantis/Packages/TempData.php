<?php

namespace Nether\Atlantis\Packages;

use Nether\Database;
use Nether\Common;

#[Common\Meta\Date('2024-06-27')]
trait TempData {

	// this is to emulate the ExtraData property used on some objects but
	// with the excepton that it does not get saved to database. it mainly
	// should be used for cramming in denormalisation data for optimisations
	// and whatnot.

	#[Common\Meta\PropertyFactory('FromArray')]
	#[Common\Meta\PropertyListable('DescribeForPublicAPI')]
	public array|Common\Datastore
	$TempData = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasTempData(string $Key, bool $AndNotEmpty=FALSE):
	bool {

		if(!$AndNotEmpty)
		return $this->TempData->HasKey($Key);

		return (TRUE
			&& $this->TempData->HasKey($Key)
			&& !!$this->TempData->Get($Key)
		);
	}

	public function
	GetTempData(string $Key):
	mixed {

		return $this->TempData->Get($Key);
	}

};
