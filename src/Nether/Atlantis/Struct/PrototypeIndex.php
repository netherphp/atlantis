<?php

////////////////////////////////////////////////////////////////////////////////
namespace Nether\Atlantis\Struct; //////////////////////////////////////////////

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

#[Database\Meta\TableClass('PrototypeIndex')]
#[Database\Meta\InsertIgnore]
#[Database\Meta\InsertReuseUnique]
#[Database\Meta\MultiFieldIndex([ 'Type', 'UUID' ], Unique: TRUE)]
class PrototypeIndex
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36)]
	public string
	$UUID;

	#[Database\Meta\TypeChar(Size: 36)]
	public string
	$Type;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Opt = Common\Datastore::FromStackMerged(
			[ 'Type' => NULL, 'UUID' => NULL ],
			$Input
		);

		////////

		if(!$Opt['Type'])
		throw new Common\Error\RequiredDataMissing('Type', 'string');

		if(!$Opt['UUID'])
		throw new Common\Error\RequiredDataMissing('UUID', 'string');

		////////

		return parent::Insert($Opt);
	}

	static public function
	InsertByPair(string $Type, string $UUID):
	?static {

		return static::Insert([
			'Type' => $Type,
			'UUID' => $UUID
		]);
	}

	static public function
	InsertFor(Atlantis\Prototype $Object):
	?static {

		$Const = sprintf('%s::EntType', $Object::class);

		if(!defined($Const))
		throw new Common\Error\RequiredDataMissing(
			$Const, 'const<string>'
		);

		////////

		return static::InsertByPair(
			constant($Const),
			$Object->UUID
		);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DeleteByUUID(string $UUID):
	void {

		$DBM = new Database\Manager;

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->FromMetaDelete(static::class)
			->Where('`UUID`=:UUID')
		);

		$Result = $SQL->Query([ ':UUID'=> $UUID ]);

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		return;
	}

	static public function
	UpdateUUID(string $Old, string $New):
	void {

		$Row = static::GetByField('UUID', $Old);

		if($Row)
		$Row->Update([ 'UUID'=> $New ]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FetchTypeForUUID(string $UUID):
	?string {

		$PIndex = static::GetByField('UUID', $UUID);

		if(!$PIndex)
		return NULL;

		return $PIndex->Type;
	}

};
