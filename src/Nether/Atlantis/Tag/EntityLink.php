<?php

namespace Nether\Atlantis\Tag;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;
use Nether\Common\Prototype\ConstructArgs;
use Nether\Common\Prototype\PropertyInfo;
use Nether\Database\Struct\TableClassInfo;

#[Database\Meta\TableClass('TagLinks', 'TL')]
#[Database\Meta\MultiFieldIndex([ 'TagID', 'EntityUUID', 'Type' ], Unique: TRUE)]
#[Database\Meta\InsertIgnore]
#[Database\Meta\InsertReuseUnique]
class EntityLink
extends Atlantis\Prototype {
/*//
the typelist array is treated as a global singleton for registration purposes
while instances of this are the binding between an entity and a tag. this class
must be extended and have its ready and find methods overridden to join in the
required data in.
//*/

	static private array
	$TypeList = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Tags', 'ID', Delete: 'CASCADE')]
	public int
	$TagID;

	#[Database\Meta\TypeChar(Size: 36, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$EntityUUID;

	#[Database\Meta\TypeChar(Size: 36, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$Type;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$TimeCreated;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public Entity
	$Tag;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(ConstructArgs $Args):
	void {

		if($Args->InputHas('T_ID'))
		$this->Tag = Entity::FromPrefixedDataset($Args->Input, 'T_');

		$this->OnReadyEntity($Args);

		return;
	}

	protected function
	OnReadyEntity(ConstructArgs $Args):
	void {

		/** @var PropertyInfo $Prop */

		$Prop = current(static::GetPropertiesWithAttribute(
			Atlantis\Meta\TagEntityProperty::class
		));

		if(!$Prop)
		return;

		/** @var TableClassInfo $Table */

		$Table = ($Prop->Type)::GetTableInfo();

		if($Args->InputHas("{$Table->Alias}_{$Table->PrimaryKey}"))
		$this->{$Prop->Name} = ($Prop->Type)::FromPrefixedDataset(
			$Args->Input, "{$Table->Alias}_"
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	RegisterType():
	void {

		/** @var Common\Prototype\PropertyInfo $Prop */

		$Prop = current(static::GetPropertiesWithAttribute(
			Atlantis\Meta\TagEntityProperty::class
		));

		if(!$Prop)
		throw new Exception(sprintf(
			'%s missing property with %s attribute',
			static::class,
			Atlantis\Meta\TagEntityProperty::class
		));

		$Type = (
			$Prop
			->GetAttribute(Atlantis\Meta\TagEntityProperty::class)
			->LinkType
		);

		self::$TypeList[$Type] = static::class;

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

		$Prop = current(($LinkClass)::GetPropertiesWithAttribute(
			Atlantis\Meta\TagEntityProperty::class
		));

		if(!$Prop)
		throw new Exception("missing property with TagEntity attribute on {$LinkClass}");

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

	static public function
	JoinExtendTables(Database\Verse $SQL, string $JAlias='Main', ?string $TPre=NULL):
	void {

		$Table = static::GetTableInfo();
		$TPre = $Table->GetPrefixedAlias($TPre);
		$JAlias = $Table->GetPrefixedAlias($JAlias);

		Entity::JoinMainTables($SQL, $JAlias, 'TagID', $TPre);

		return;
	}

	static public function
	JoinExtendFields(Database\Verse $SQL, ?string $TPre = NULL):
	void {

		$Table = static::GetTableInfo();
		$TPre = $Table->GetPrefixedAlias($TPre);

		Entity::JoinMainFields($SQL, $TPre);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

		$Prop = current(static::GetPropertiesWithAttribute(
			Atlantis\Meta\TagEntityProperty::class
		));

		$Type = (
			$Prop
			->GetAttribute(Atlantis\Meta\TagEntityProperty::class)
			->LinkType
		);

		$Input = new Common\Datastore($Input);
		$Input->BlendLeft([
			'TimeCreated' => time(),
			'Type'        => $Type
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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	KeepTheEntity(self $Link):
	mixed {
	/*//
	@date 2023-06-23
	little helper to give remapper lists context.
	//*/

		$Prop = current(static::GetPropertiesWithAttribute(
			Atlantis\Meta\TagEntityProperty::class
		));

		if(!$Prop)
		throw new Exception('no TagEntityProperty found');

		return $Link->{$Prop->Name};
	}

	static public function
	KeepTheTag(self $Link):
	mixed {
	/*//
	@date 2023-06-23
	little helper to give remapper lists context.
	//*/

		return $Link->Tag;
	}

}