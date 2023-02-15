<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

#[Database\Meta\TableClass('TagLinks', 'TL')]
#[Database\Meta\MultiFieldIndex(['TagID','EntityUUID' ], Unique: TRUE)]
#[Database\Meta\InsertIgnore]
#[Database\Meta\InsertReuseUnique]
class TagLink
extends Database\Prototype {

	const
	LinkType = NULL;

	static private array
	$TypeList = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Tags', 'ID', Delete: 'CASCADE')]
	public int
	$TagID;

	#[Database\Meta\TypeChar(Size: 36, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$EntityUUID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeChar(Size: 8, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$Type;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public Tag
	$Tag;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('T_ID'))
		$this->Tag = Tag::FromPrefixedDataset($Args->Input, 'T_');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	RegisterType(string $Class):
	void {

		self::$TypeList[$Class::LinkType] = $Class;

		return;
	}

	static public function
	GetTypeLinkClass(string $Type):
	?string {

		if(!array_key_exists($Type, self::$TypeList))
		return NULL;

		return self::$TypeList[$Type];
	}

	static public function
	GetTypeEntityClass(string $Type):
	?string {

		$Info = static::GetTypeEntityInfo($Type);

		return $Info->EntityClass;
	}

	static public function
	GetTypeEntityInfo(string $Type):
	object {

		$LinkClass = static::GetTypeLinkClass($Type);

		if(!$LinkClass)
		throw new Exception("no class found for {$Type}");

		////////

		$Prop = ($LinkClass)::GetPropertyInfo('Entity');

		if(!$Prop)
		throw new Exception("no Entity prop found on {$LinkClass}");

		////////

		if(!class_exists($Prop->Type))
		throw new Exception("class does not exist {$LinkClass}");

		////////

		return (object)[
			'LinkType'    => $Type,
			'LinkClass'   => $LinkClass,
			'EntityClass' => $Prop->Type
		];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendTables(Database\Verse $SQL, Common\Datastore $Input):
	void {

		Tag::JoinMainTables($SQL, 'Main', 'TagID');
		Tag::JoinMainFields($SQL);

		return;
	}

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['Type'] ??= NULL;
		$Input['TagID'] ??= NULL;
		$Input['EntityUUID'] ??= NULL;
		$Input['Sort'] ??= 'tag-name-az';

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['Type'] !== NULL)
		$SQL->Where('`Main`.`Type`=:Type');

		if($Input['TagID'] !== NULL)
		$SQL->Where('`Main`.`TagID`=:TagID');

		if($Input['EntityUUID'] !== NULL)
		$SQL->Where('`Main`.`EntityUUID`=:EntityUUID');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'tag-name-az':
				$SQL->Sort('T.Name', $SQL::SortAsc);
			break;
			case 'tag-name-za':
				$SQL->Sort('T.Name', $SQL::SortDesc);
			break;
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DeleteByPair(int $TagID, string $EntityUUID):
	void {

		$DBM = new Database\Manager;

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->FromMetaDelete(static::class)
			->Where('`TagID`=:TagID AND `EntityUUID`=:EntityUUID')
			->Limit(1)
		);

		$Result = $SQL->Query([
			':TagID'      => $TagID,
			':EntityUUID' => $EntityUUID
		]);

		return;
	}

	static public function
	DeleteByEntity(string $EntityUUID):
	void {

		$DBM = new Database\Manager;

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->FromMetaDelete(static::class)
			->Where('`EntityUUID`=:EntityUUID')
		);

		$Result = $SQL->Query([
			':EntityUUID' => $EntityUUID
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Input = new Common\Datastore($Input);
		$Input->BlendLeft([
			'TimeCreated' => time(),
			'Type'        => static::LinkType
		]);

		////////

		if(!$Input['Type'])
		throw new Exception('no Type specified');

		if(!$Input['TagID'])
		throw new Exception('no TagID specified');

		if(!$Input['EntityUUID'])
		throw new Exception('no EntityUUID specified');

		////////

		return parent::Insert($Input);
	}
}
