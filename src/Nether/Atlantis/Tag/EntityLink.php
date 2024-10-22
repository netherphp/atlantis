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

	const
	SortNewest = 'newest',
	SortOldest = 'oldest';

	static public array
	$TypeList = [];

	static public array
	$TypeInfo = [];

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

	#[Database\Meta\TableJoin('TagID', 'T1', TRUE)]
	public Entity
	$Tag;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(ConstructArgs $Args):
	void {

		if($Args->InputHas('T1_ID'))
		$this->Tag = Entity::FromPrefixedDataset($Args->Input, 'T1_');

		$this->OnReadyEntity($Args);

		return;
	}

	protected function
	OnReadyEntity(ConstructArgs $Args):
	void {

		try {
			$Prop = static::EntityProperty();
			$Table = static::EntityTableInfo($Prop->Type);
		}

		catch(Atlantis\Error\TagEntityPropertyNotFound $E) {
			// this allows for using the parent type as a generic to
			// to just fetch the relationship without any extensions.
			return;
		}

		////////

		if($Args->InputHas("{$Table->Alias}_{$Table->PrimaryKey}"))
		$this->{$Prop->Name} = ($Prop->Type)::FromPrefixedDataset(
			$Args->Input, "{$Table->Alias}_"
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-06-27')]
	#[Common\Meta\Info('Register this class with the framework to be known.')]
	static public function
	Register():
	void {

		// @todo 2024-03-15 populate typeinfo with more detailed info like
		// the keys and class names so that searches can happen either way.
		// also so the class map is only reflectioned once.

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

	#[Common\Meta\Date('2023-07-25')]
	#[Common\Meta\Info('Fetch the information about the property that has the TagEntityProperty attribute.')]
	static public function
	EntityProperty():
	PropertyInfo {

		$Prop = current(static::GetPropertiesWithAttribute(
			Atlantis\Meta\TagEntityProperty::class
		));

		if(!$Prop)
		throw new Atlantis\Error\TagEntityPropertyNotFound(static::class);

		return $Prop;
	}

	#[Common\Meta\Date('2023-07-25')]
	#[Common\Meta\Info('Fetch the table info for the specified class.')]
	static public function
	EntityTableInfo(string $Class):
	TableClassInfo {

		$Table = ($Class)::GetTableInfo();

		if(!$Table)
		throw new Database\Error\TableClassNotFound($Class);

		return $Table;
	}

	#[Common\Meta\Date('2023-07-25')]
	#[Common\Meta\Info('Fetch the table info for the Tag class.')]
	public function
	TagTableInfo():
	TableClassInfo {

		$Table = Atlantis\Tag\Entity::GetTableInfo();

		return $Table;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GetTypeByLinkClass(string $Class):
	?string {

		$Out = key(array_filter(
			static::$TypeList,
			fn(string $C)=> $C === $Class
		));

		if(!is_string($Out))
		return NULL;

		return $Out;
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

	static public function
	GetTypeByEntityClass(string $CName):
	string {

		$EntProp = NULL;

		////////

		foreach(static::$TypeList as $LinkType => $LinkClass) {
			try { $EntProp = ($LinkClass)::EntityProperty($LinkClass); }
			catch(Exception $E) {  }

			if($EntProp->Type === $CName)
			return $LinkType;
		}

		throw new Exception("no type found for $CName");

		return '';
	}

	static public function
	LinkType():
	?string {

		return static::GetTypeByLinkClass(static::class);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Type = (
			isset($Input['LinkClass'])
			? static::GetTypeByLinkClass($Input['LinkClass'])
			: static::GetTypeByLinkClass(static::class)
		);

		$Input['Type'] ??= $Type;
		$Input['TagID'] ??= NULL;
		$Input['EntityUUID'] ??= NULL;
		$Input['Sort'] ??= 'tag-name-az';

		$Input['TagAnd'] ??= NULL;

		$Input['Enabled'] ??= NULL;

		$Input['Resolvers'] ??= [
			fn($Row)=> static::GetTypeLinkClass($Row->Type)
		];

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

		static::FindExtendFilters_HandleTagAnd($SQL, $Input);

		return;
	}

	static protected function
	FindExtendFilters_HandleTagAnd(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if(!is_array($Input['TagAnd']))
		return;

		$Table   = Entity::GetTableInfo();
		$TagIter = NULL;
		$TagID = NULL;
		$TagKey = NULL;

		// this is literally stupid as hell i think there is a much
		// better way we used in the past with an IN() and a count
		// followup. there was a gotcha with that though and im
		// still having a hard time with things after covid. it does
		// however, work.

		foreach($Input['TagAnd'] as $TagIter => $TagID) {
			$TagKey = "PTLA{$TagIter}";
			$Input[$TagKey] = $TagID;

			$SQL
			->Join("{$Table->Name} {$TagKey} ON {$TagKey}.ID=Main.TagID")
			->Where("{$TagKey}.ID=:{$TagKey}");
		}

		$SQL->Group('Main.EntityUUID');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
			break;
			case 'tag-name-az':
				$SQL->Sort('T1.Name', $SQL::SortAsc);
			break;
			case 'tag-name-za':
				$SQL->Sort('T1.Name', $SQL::SortDesc);
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

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

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

		$Result = parent::Insert($Input);

		////////

		return $Result;
	}

	static public function
	Rawcert(iterable $Input):
	?static {

		$Input = new Common\Datastore($Input);
		$Input->BlendLeft([
			'TimeCreated' => time()
		]);

		////////

		if(!$Input['Type'])
		throw new Exception('no Type specified');

		if(!$Input['TagID'])
		throw new Exception('no TagID specified');

		if(!$Input['EntityUUID'])
		throw new Exception('no EntityUUID specified');

		$Result = parent::Insert($Input);

		////////

		return $Result;
	}

	static public function
	InsertByPair(int $TagID, string $EntityUUID):
	?static {

		return static::Insert([
			'TagID'      => $TagID,
			'EntityUUID' => $EntityUUID
		]);
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
