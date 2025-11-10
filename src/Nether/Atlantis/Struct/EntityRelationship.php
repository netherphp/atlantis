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

	#[Common\Meta\Date('2023-07-31')]
	static Common\Datastore
	$Types;

	#[Common\Meta\Date('2023-07-31')]
	static public function
	Register(?string $Type=NULL, ?string $Class=NULL):
	void {

		if(!isset(static::$Types))
		static::$Types = new Common\Datastore;

		////////

		if(!class_exists($Class))
		throw new Exception("class not found: {$Class}");

		if($Type === NULL) {
			if(!is_a($Class, Atlantis\Prototype::class, TRUE))
			throw new Exception(sprintf('%s not %s', $Class, Atlantis\Prototype::class));

			if(!defined(sprintf('%s::EntType', $Class)))
			throw new Exception(sprintf('%s has no EntType', $Class));

			$Type = $Class::EntType;
		}

		////////

		static::$Types[$Type] = $Class;
		return;
	}

	static public function
	TypeClass(string $Type):
	string {

		if(!isset(static::$Types))
		static::$Types = new Common\Datastore;

		////////

		if(!isset(static::$Types[$Type]))
		throw new Exception("ERI Type not registered: {$Type}");

		////////

		return static::$Types[$Type];
	}

	static public function
	ClassType(string $Class):
	string {

		if(!isset(static::$Types))
		static::$Types = new Common\Datastore;

		////////

		$Found = static::$Types->Distill(
			fn(string $C)
			=> $C === $Class
		);

		if(!$Found->Count())
		throw new Exception("ERI Class not registered: {$Class}");

		////////

		return $Found->Keys()[0];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeVarChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	public string
	$ParentType;

	#[Database\Meta\TypeVarChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	public string
	$ParentUUID;

	#[Database\Meta\TypeVarChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	public string
	$ChildType;

	#[Database\Meta\TypeVarChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	public string
	$ChildUUID;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public ?Atlantis\Prototype
	$Parent = NULL;

	public ?Atlantis\Prototype
	$Child = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('C_RE_ID')) {
			$Class = static::TypeClass($Args->Input['ChildType']);
			$this->Child = $Class::FromPrefixedDataset($Args->Input, 'C_RE_');
		}

		return;
	}

	public function
	DescribeForPublicAPI():
	array {

		$Data = parent::DescribeForPublicAPI();
		$Data['Parent'] = NULL;
		$Data['Child'] = NULL;

		if(isset($this->Parent))
		$Data['Parent'] = $this->Parent->DescribeForPublicAPI();

		if(isset($this->Child))
		$Data['Child'] = $this->Child->DescribeForPublicAPI();

		return $Data;
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
			->Where('`ParentUUID`=:UUID OR `ChildUUID`=:UUID')
		);

		$Result = $SQL->Query([
			':UUID'=> $UUID
		]);

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		return;
	}

	static public function
	DeleteByParentUUID(string $UUID):
	void {

		$DBM = new Database\Manager;

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->FromMetaDelete(static::class)
			->Where('`ParentUUID`=:UUID')
		);

		$Result = $SQL->Query([
			':UUID'=> $UUID
		]);

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		return;
	}

	static public function
	DeleteByChildUUID(string $UUID):
	void {

		$DBM = new Database\Manager;

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->FromMetaDelete(static::class)
			->Where('`ChildUUID`=:UUID')
		);

		$Result = $SQL->Query([
			':UUID'=> $UUID
		]);

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		return;
	}

	static public function
	DeleteByPair(string $UUID1, string $UUID2):
	void {

		$DBM = new Database\Manager;

		////////

		if(!$UUID1)
		throw new Exception('Missing or Invalid UUID1');

		if(!$UUID2)
		throw new Exception('Missing or Invalid UUID2');

		////////

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->FromMetaDelete(static::class)
			->Where('(`ParentUUID`=:UUID1 AND `ChildUUID`=:UUID2) OR (`ParentUUID`=:UUID2 AND `ChildUUID`=:UUID1)')
		);

		$Result = $SQL->Query([
			':UUID1' => $UUID1,
			':UUID2' => $UUID2
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

		// parent profiles.
		$Input['ParentType'] ??= NULL;
		$Input['ParentUUID'] ??= NULL;

		// child profiles.
		$Input['ChildType'] ??= NULL;
		$Input['ChildUUID'] ??= NULL;

		// either parent or child.
		$Input['EntityUUID'] ??= NULL;
		$Input['EntityType'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['ParentType'] !== NULL) {
			$Class = static::TypeClass($Input['ParentType']);

			$Class::JoinMainTables($SQL, 'Main', 'ParentUUID', 'P');
			$Class::JoinMainFields($SQL, 'P');

			$Class::JoinExtendTables($SQL, 'P', 'P');
			$Class::JoinExtendFields($SQL, 'P');

			$SQL->Where('Main.ParentType LIKE :ParentType');
			unset($Class);
		}

		if($Input['ParentUUID'] !== NULL)
		$SQL->Where('Main.ParentUUID=:ParentUUID');

		////////

		if($Input['ChildType'] !== NULL) {
			$Class = static::TypeClass($Input['ChildType']);

			$Class::JoinMainTables($SQL, 'Main', 'ChildUUID', 'C');
			$Class::JoinMainFields($SQL, 'C');

			$Class::JoinExtendTables($SQL, 'C', 'C');
			$Class::JoinExtendFields($SQL, 'C');

			$SQL->Where('Main.ChildType LIKE :ChildType');
			unset($Class);
		}

		if($Input['ChildUUID'] !== NULL)
		$SQL->Where('Main.ChildUUID=:ChildUUID');

		////////

		if($Input['EntityUUID'] !== NULL && $Input['EntityType'] !== NULL) {
			if(is_array($Input['EntityUUID']))
			$SQL->Where('(Main.ParentType=:EntityType AND Main.ChildUUID IN(:EntityUUID)) OR (Main.ChildType=:EntityType AND Main.ParentUUID IN(:EntityUUID))');
			else
			$SQL->Where('(Main.ParentType=:EntityType AND Main.ChildUUID=:EntityUUID) OR (Main.ChildType=:EntityType AND Main.ParentUUID=:EntityUUID)');
		}

		if($Input['EntityUUID'] !== NULL) {
			if(is_array($Input['EntityUUID']))
			$SQL->Where('Main.ParentUUID IN(:EntityUUID) OR Main.ChildUUID IN(:EntityUUID)');
			else
			$SQL->Where('Main.ParentUUID=:EntityUUID OR Main.ChildUUID=:EntityUUID');
		}

		////////

		if($Input['Group'] !== NULL) {
			if($Input['Group'] === 'parent')
			$SQL->Group('Main.ParentUUID');

			if($Input['Group'] === 'child')
			$SQL->Group('Main.ChildUUID');
		}

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

	static public function
	InsertByPair(string $Type1, string $UUID1, string $Type2, string $UUID2):
	?static {

		$Data = [
			'ParentType' => $Type1,
			'ParentUUID' => $UUID1,
			'ChildType'  => $Type2,
			'ChildUUID'  => $UUID2
		];

		return static::Insert($Data);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-12-06')]
	static public function
	KeepTheOtherOne(self $ERI, string $UUID, bool $ObjFirst=TRUE):
	static|string {

		// if given a joined dataset we will have the full object and that
		// is what will be kept. otherwise it will return what it has which
		// is just the uuid.

		if($ERI->ParentUUID === $UUID) {
			if(isset($ERI->Child) && $ObjFirst)
			return $ERI->Child;

			return $ERI->ChildUUID;
		}

		if(isset($ERI->Parent) && $ObjFirst)
		return $ERI->Parent;

		return $ERI->ParentUUID;
	}

	#[Common\Meta\Date('2024-05-27')]
	static public function
	KeepTheOtherUUID(self $ERI, string $UUID):
	static|string {

		// if given a joined dataset we will have the full object and that
		// is what will be kept. otherwise it will return what it has which
		// is just the uuid.

		if($ERI->ParentUUID === $UUID)
		return $ERI->ChildUUID;

		return $ERI->ParentUUID;
	}

	#[Common\Meta\Date('2024-05-22')]
	static public function
	MapToParentUUID(self $ERI):
	string {

		return $ERI->ParentUUID;
	}

	#[Common\Meta\Date('2024-05-22')]
	static public function
	MapToChildUUID(self $ERI):
	string {

		return $ERI->ChildUUID;
	}

}
