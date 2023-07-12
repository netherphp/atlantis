<?php

namespace Nether\Atlantis;

use Nether\Common;
use Nether\Database;

#[Common\Meta\DateAdded('2023-02-15')]
#[Common\Meta\Info('Adds core object features that anything built using the framework should expect to have.')]
class Prototype
extends Database\Prototype {

	////////////////////////////////////////////////////////////////
	//// DATABASE FIELDS ///////////////////////////////////////////

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'UUID' ])]
	public string
	$UUID;

	////////////////////////////////////////////////////////////////
	//// LOCAL FIELDS //////////////////////////////////////////////

	protected Database\Struct\PrototypeFindResult
	$TagLinks;

	////////////////////////////////////////////////////////////////
	//// OVERRIDE Database\Prototype ///////////////////////////////

	#[Common\Meta\DateAdded('2023-02-15')]
	public function
	Drop():
	static {

		// remove entries from nfk tag table.
		Tag\EntityLink::DeleteByEntity($this->UUID);

		// remove entries from nfk obj relationship table.
		Struct\EntityRelationship::DeleteByUUID($this->UUID);

		// bye.
		parent::Drop();

		return $this;
	}

	#[Common\Meta\DateAdded('2023-02-15')]
	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if(isset($Input['UUID'])) {
			if(is_array($Input['UUID']))
			$SQL->Where('Main.UUID IN(:UUID)');

			else
			$SQL->Where('Main.UUID=:UUID');
		}

		return;
	}

	#[Common\Meta\DateAdded('2023-02-15')]
	static public function
	Insert(iterable $Input):
	?static {

		$Input = Common\Datastore::FromStackBlended($Input, [
			'UUID' => Common\UUID::V7()
		]);

		return parent::Insert($Input);
	}

	////////////////////////////////////////////////////////////////
	//// LOCAL METHODS /////////////////////////////////////////////

	#[Common\Meta\DateAdded('2023-05-09')]
	#[Common\Meta\Info('Returns a dataset that is reasonable to be considered public information about this object. Classes should override, call the parent version, then append to the dataset returned.')]
	public function
	DescribeForPublicAPI():
	array {

		$Props = new Common\Datastore(static::GetPropertiesWithAttribute(
			Common\Meta\PropertyListable::class
		));

		$Vals = $Props->Map(function(Common\Prototype\PropertyInfo $P) {

			$Attr = $P->GetAttribute(Common\Meta\PropertyListable::class);
			/** @var Common\Meta\PropertyListable $Attr */

			if(isset($Attr->MethodName))
			if(method_exists($this->{$P->Name}, $Attr->MethodName))
			return $this->{$P->Name}->{$Attr->MethodName}();

			if(isset($this->{$P->Name}))
			return $this->{$P->Name};

			return NULL;
		});

		return $Vals->GetData();
	}

	#[Common\Meta\DateAdded('2023-03-07')]
	#[Common\Meta\Info('Fetch the dataset of tag links from the database.')]
	public function
	FetchTagLinks():
	Database\Struct\PrototypeFindResult {

		$Result = Tag\EntityLink::Find([
			'EntityUUID' => $this->UUID,
			'Limit'      => 0
		]);

		return $Result;
	}

	#[Common\Meta\DateAdded('2023-03-07')]
	#[Common\Meta\Info('Get the dataset of tag links from the database. Uses local instance cache.')]
	public function
	GetTagLinks():
	Database\Struct\PrototypeFindResult {

		if(!isset($this->TagLinks))
		$this->TagLinks = $this->FetchTagLinks();

		return $this->TagLinks;
	}

	#[Common\Meta\DateAdded('2023-03-07')]
	#[Common\Meta\Info('Get the dataset of tag entities from the database. Uses local instance cache.')]
	public function
	GetTags():
	Database\Struct\PrototypeFindResult {

		$Links = ($this
			->GetTagLinks()
			->Map(fn(Tag\EntityLink $Link)=> $Link->Tag)
		);

		return $Links;
	}

	////////////////////////////////////////////////////////////////
	//// LOCAL STATIC API //////////////////////////////////////////

	#[Common\Meta\DateAdded('2023-05-23')]
	#[Common\Meta\Info('Fetch an object by UUID.')]
	static public function
	GetByUUID(string $UUID):
	?static {

		return parent::GetByField('UUID', $UUID);
	}

	////////////////////////////////////////////////////////////////
	//// FILTER/MAPPER CALLABLES ///////////////////////////////////

	#[Common\Meta\DateAdded('2023-07-07')]
	static public function
	MapForPublicAPI(self $Inst):
	array {

		return $Inst->DescribeForPublicAPI();
	}

}
