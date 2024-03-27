<?php

////////////////////////////////////////////////////////////////////////////////
namespace Nether\Atlantis\Struct; //////////////////////////////////////////////

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

#[Database\Meta\TableClass('PrototypeRelations')]
#[Database\Meta\InsertIgnore]
#[Database\Meta\InsertReuseUnique]
#[Database\Meta\MultiFieldIndex([ 'PID1', 'PID2' ], Unique: TRUE)]
class PrototypeRelation
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('PrototypeIndex', 'ID')]
	public int
	$PID1;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('PrototypeIndex', 'ID')]
	public int
	$PID2;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Opt = Common\Datastore::FromStackMerged(
			[ 'PID1' => NULL, 'PID2' => NULL ],
			$Input
		);

		////////

		if(!$Opt['PID1'])
		throw new Common\Error\RequiredDataMissing('PID1', 'int');

		if(!$Opt['PID2'])
		throw new Common\Error\RequiredDataMissing('PID2', 'int');

		////////

		return parent::Insert($Opt);
	}

	static public function
	InsertByPair(int $PID1, int $PID2):
	?static {

		return static::Insert([
			'PID1' => $PID1,
			'PID2' => $PID2
		]);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

};
