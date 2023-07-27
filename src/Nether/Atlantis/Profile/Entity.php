<?php

namespace Nether\Atlantis\Profile;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

#[Database\Meta\TableClass('Profiles', 'PRO')]
class Entity
extends Atlantis\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeVarChar(Size: 100)]
	public string
	$Alias;

	#[Database\Meta\TypeVarChar(Size: 100)]
	public string
	$Title;

	#[Database\Meta\TypeText]
	public string
	$Details;

}
