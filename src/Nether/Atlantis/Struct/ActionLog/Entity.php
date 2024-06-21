<?php

namespace Nether\Atlantis\Struct\ActionLog;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

#[Database\Meta\TableClass('ActionLog')]
class Entity
extends Atlantis\Prototype
implements Atlantis\Interfaces\ExtraDataInterface {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Users', 'ID', Delete: NULL)]
	public int
	$UserID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$Time;

	#[Database\Meta\TypeVarChar(Size: 64)]
	public string
	$ActionName;

	#[Database\Meta\TypeVarChar(Size: 64)]
	public string
	$ActionValue;

	use
	Atlantis\Packages\ExtraData;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct() {

		return;
	}

};
