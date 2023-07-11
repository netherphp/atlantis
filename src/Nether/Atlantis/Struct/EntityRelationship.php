<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

#[Database\Meta\TableClass('EntityRelationshipIndex', 'ERI')]
#[Database\Meta\MultiFieldIndex([ 'ParentUUID', 'ChildUUID' ], TRUE)]
#[Database\Meta\InsertIgnore]
#[Database\Meta\InsertReuseUnique]
class EntityRelationship
extends Atlantis\Prototype {

	#[Database\Meta\TypeVarChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	public string
	$ParentType;

	#[Database\Meta\TypeVarChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	public string
	$ParentUUID;

	#[Database\Meta\TypeVarChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	public string
	$ChildType;

	#[Database\Meta\TypeVarChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	public string
	$ChildUUID;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DeleteByUUID(string $UUID):
	void {

		$DBM = new Database\Manager;

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->FromMetaDelete(static::class)
			->Where('`ParentUUID`=:UUID OR `ChildUUID`=:UUID')
		);

		$Result = $SQL->Query([
			':UUID'=> $UUID
		]);

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['ParentType'] ??= NULL;
		$Input['ChildType'] ??= NULL;

		$Input['ParentUUID'] ??= NULL;
		$Input['ChildUUID'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['ParentType'] !== NULL)
		$SQL->Where('Main.ParentType=:ParentType');

		if($Input['ParentUUID'] !== NULL)
		$SQL->Where('Main.ParentUUID=:ParentUUID');

		if($Input['ChildType'] !== NULL)
		$SQL->Where('Main.ChildType=:ChildType');

		if($Input['ChildUUID'] !== NULL)
		$SQL->Where('Main.ChildUUID=:ChildUUID');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {

		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Input = new Common\Datastore($Input);

		$Input->BlendRight([
			'ParentType' => NULL,
			'ParentUUID' => NULL,
			'ChildType'  => NULL,
			'ChildUUID'  => NULL
		]);

		////////

		if(!$Input['ParentType'])
		throw new Exception('ParentType cannot be empty');

		if(!$Input['ChildType'])
		throw new Exception('ChildType cannot be empty');

		if(!$Input['ParentUUID'])
		throw new Exception('ParentUUID cannot be empty');

		if(!$Input['ChildUUID'])
		throw new Exception('ChildUUID cannot be empty');

		////////

		return parent::Insert($Input);
	}

}
