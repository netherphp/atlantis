<?php ##########################################################################
################################################################################

namespace Nether\Atlantis;

use Nether\Common;
use Nether\Database;

use ArrayAccess;

################################################################################
################################################################################

#[Common\Meta\Date('2023-02-15')]
#[Common\Meta\Info('Adds core object features that anything built using the framework should expect to have.')]
class Prototype
extends Database\Prototype
implements
	Interfaces\DescribeForPublicInterface,
	Plugin\Interfaces\Engine\AppInstanceStaticInterface {

	use
	Packages\AppInstanceStatic,
	Packages\TempData;

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

	protected Database\ResultSet
	$TagLinks;

	////////////////////////////////////////////////////////////////
	//// OVERRIDE Database\Prototype ///////////////////////////////

	#[Common\Meta\Date('2023-02-15')]
	public function
	Drop():
	static {

		// remove entries from nfk tag table.
		Tag\EntityLink::DeleteByEntity($this->UUID);

		// remove entries from nfk obj relationship table.
		Struct\EntityRelationship::DeleteByUUID($this->UUID);
		Struct\PrototypeIndex::DeleteByUUID($this->UUID);

		// bye.
		parent::Drop();

		return $this;
	}

	#[Common\Meta\Date('2023-08-09')]
	public function
	Patch(array|ArrayAccess $Input):
	array {

		// remember the orginal uuid in the event that was updated.

		$UUID = $this->UUID;

		// run the patch set.

		$Data = parent::Patch($Input);

		// add magic ExtraData/ExtraJSON support.

		if($this instanceof Interfaces\ExtraDataInterface)
		$Data = array_merge($Data, $this->PatchExtraData($Input));

		// if the uuid was updated then we should update the prototype
		// index as well.

		if(isset($Input['UUID']) && ($Input['UUID'] !== $this->UUID))
		Struct\PrototypeIndex::UpdateUUID($UUID, $this->UUID);

		return $Data;
	}

	#[Common\Meta\Date('2023-08-09')]
	#[Common\Meta\Info('Generate an array of ExtraData that was given merged with any old.')]
	public function
	PatchExtraData(array|ArrayAccess $Input):
	array {

		// fyi we are expecting assoc arrays from the input. so like...
		// { "ID": 42, "ExtraData[Something]": 1234 }
		// $_POST['ID'] and $_POST['ExtraData']['Something']

		$Output = [];

		//var_dump($Input);

		$Has = match(TRUE) {
			$Input instanceof ArrayAccess
			=> $Input->OffsetExists('ExtraData'),

			default
			=> array_key_exists('ExtraData', $Input)
		};

		if(!$Has || !is_array($Input['ExtraData']))
		return $Output;

		/** @var Packages\ExtraDataPackage $this */

		// if this object has extradata and we had extra data merge the
		// new data in and strip things that were emptied out.

		$ExtraData = (
			Common\Datastore::FromArray($this->ExtraData->GetData())
			->MergeRight($Input['ExtraData'])
			//->Filter(fn(mixed $D)=> !!$D)
		);

		// if the result has data then turn it to json otherwise null the
		// entire field out.

		$Output['ExtraJSON'] = match(TRUE) {
			$ExtraData->Count() > 0
			=> json_encode($ExtraData->GetData()),

			default
			=> NULL
		};

		return $Output;
	}

	#[Common\Meta\Date('2025-02-05')]
	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		parent::FindExtendOptions($Input);

		$Input->Define([
			'ID'        => NULL, // integer
			'UUID'      => NULL, // string<uuid>
			'Untagged'  => NULL, // boolean
			'RelatedTo' => NULL  // string<uuid>
		]);

		return;
	}

	#[Common\Meta\Date('2023-02-15')]
	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['ID'] !== NULL) {
			if(is_array($Input['ID']))
			$SQL->Where('Main.ID IN(:ID)');

			else
			$SQL->Where('Main.ID=:ID');
		}

		if($Input['UUID'] !== NULL) {
			if(is_array($Input['UUID'])) {
				if(!count($Input['UUID']))
				$Input['UUID'] = 'null-null-null-null-null';

				$SQL->Where('Main.UUID IN(:UUID)');
			}

			else {
				$SQL->Where('Main.UUID=:UUID');
			}
		}

		if($Input['Untagged'] !== NULL) {
			if($Input['Untagged'] === TRUE) {
				$TableTL = Tag\EntityLink::GetTableInfo();

				$SQL->Join(sprintf(
					'%s UTCHK ON UTCHK.EntityUUID=Main.UUID',
					$TableTL->Name
				));

				$SQL->Where('UTCHK.ID IS NULL');
			}
		}

		if($Input['RelatedTo'] !== NULL) {

			// in practice joining the table twice for each direction was
			// a lot faster than an OR clause in the join to support the
			// bidirectional relationship. by like an 8 second difference.

			($SQL)
			->Join(
				'EntityRelationshipIndex RT1 ON (RT1.ParentUUID=:RelatedTo AND RT1.ChildUUID=Main.UUID)',
				$SQL::JoinLeft
			)
			->Join(
				'EntityRelationshipIndex RT2 ON (RT2.ChildUUID=:RelatedTo AND RT2.ParentUUID=Main.UUID)',
				$SQL::JoinLeft
			)
			->Where(
				'(RT1.ParentUUID IS NOT NULL AND RT1.ChildUUID IS NOT NULL) OR '.
				'(RT2.ParentUUID IS NOT NULL AND RT2.ChildUUID IS NOT NULL)'
			)
			->Group('Main.ID');
		}

		return;
	}

	#[Common\Meta\Date('2023-02-15')]
	static public function
	Insert(iterable $Input):
	?static {

		$Opt = Common\Datastore::FromStackMerged(
			[ 'UUID'=> Common\UUID::V7() ],
			$Input
		);

		$Object = parent::Insert($Opt);
		$Const = sprintf('%s::EntType', $Object::class);

		if(defined($Const))
		$PIndex = Struct\PrototypeIndex::InsertFor($Object);

		return $Object;
	}

	////////////////////////////////////////////////////////////////
	//// LOCAL METHODS /////////////////////////////////////////////

	#[Common\Meta\Date('2023-05-09')]
	#[Common\Meta\Info('Returns a dataset that is reasonable to be considered public information about this object. Classes should override, call the parent version, then append to the dataset returned.')]
	public function
	DescribeForPublicAPI():
	array {

		$Props = new Common\Datastore(static::GetPropertiesWithAttribute(
			Common\Meta\PropertyListable::class
		));

		$PIndex = static::GetClassInfo();
		//print_r($PIndex->Pa);
		$Sorted = new Common\Datastore;

		$Vals = $Props->Map(function(Common\Prototype\PropertyInfo $P) use($Sorted) {

			$Attr = $P->GetAttribute(Common\Meta\PropertyListable::class);
			$Val = NULL;
			/** @var Common\Meta\PropertyListable $Attr */

			if(isset($Attr->MethodName))
			if(method_exists($this->{$P->Name}, $Attr->MethodName))
			$Val = $this->{$P->Name}->{$Attr->MethodName}(...$Attr->MethodArgs);

			if(is_object($this->{$P->Name}))
			if(method_exists($this->{$P->Name}, 'DescribeForPublicAPI'))
			$Val = $this->{$P->Name}->DescribeForPublicAPI(...$Attr->MethodArgs);

			if(isset($this->{$P->Name}))
			$Val = $this->{$P->Name};

			if($Val instanceof Common\Protostore)
			$Val = $Val->Export();

			if($Val) {
				$Sorted->MergeRight([ $P->Name=> $Val ]);
			}

			return NULL;
		});

		return $Sorted->Export();
	}

	#[Common\Meta\Date('2023-03-07')]
	#[Common\Meta\Info('Fetch the dataset of tag links from the database.')]
	public function
	FetchTagLinks():
	Database\ResultSet {

		// something needs to be done here so that it can be called via a
		// typed class rather than the base class.

		$Result = Tag\EntityLink::Find([
			'EntityUUID' => $this->UUID,
			'Limit'      => 0
		]);

		return $Result;
	}

	#[Common\Meta\Date('2023-03-07')]
	#[Common\Meta\Info('Get the dataset of tag links from the database. Uses local instance cache.')]
	public function
	GetTagLinks():
	Database\ResultSet {

		if(!isset($this->TagLinks))
		$this->TagLinks = $this->FetchTagLinks();

		return $this->TagLinks->Copy();
	}

	#[Common\Meta\Date('2023-03-07')]
	#[Common\Meta\Info('Get the dataset of tag entities from the database. Uses local instance cache.')]
	public function
	GetTags():
	Database\ResultSet {

		$Links = ($this
			->GetTagLinks()
			->Map(fn($Link)=> $Link->Tag)
		);

		return $Links;
	}

	public function
	GetTagsIndexedByID():
	Common\Datastore {

		$Output = new Common\Datastore;

		($this)
		->GetTagLinks()
		->Each(function(Tag\EntityLink $Link) use($Output) {
			$Output->Set($Link->Tag->ID, $Link->Tag);
			return;
		});

		return $Output;
	}

	public function
	GetTagsIndexedByAlias():
	Common\Datastore {

		$Output = new Common\Datastore;

		($this)
		->GetTagLinks()
		->Each(function(Tag\EntityLink $Link) use($Output) {
			$Output->Set($Link->Tag->Alias, $Link->Tag);
			return;
		});

		return $Output;
	}

	public function
	HasTagAlias(string $Alias):
	bool {

		$Tags = $this->GetTagsIndexedByAlias();

		if($Tags->HasKey($Alias))
		return TRUE;

		return FALSE;
	}

	#[Common\Meta\Date('2023-07-25')]
	#[Common\Meta\Info('Get a list of common attributes used with external systems.')]
	public function
	GetDataAttr(?iterable $More=NULL, bool $Prefix=FALSE):
	Common\Datastore {

		$Output = new Common\Datastore([
			'id'   => $this->ID,
			'uuid' => $this->UUID
		]);

		if(is_iterable($More))
		$Output->MergeRight($More);

		if($Prefix)
		$Output->RemapKeys(
			fn(string $K, string $V)
			=> [ "data-{$K}"=> $V ]
		);

		return $Output;
	}

	#[Common\Meta\Date('2023-07-25')]
	#[Common\Meta\Info('Get a list of common attributes used with external formatted as html data attributes.')]
	public function
	GetDataAttrForHTML(?iterable $More=NULL, bool $Prefix=TRUE):
	string {

		$Output = $this->GetDataAttr($More, $Prefix);

		$Output->RemapKeys(
			fn(string $K, string $V)
			=> [ $K=> sprintf('%s="%s"', $K, htmlentities($V)) ]
		);

		return $Output->Join(' ');
	}

	#[Common\Meta\Date('2023-07-28')]
	public function
	GetPageURL():
	string {

		throw new Error\MethodUnimplemented(__METHOD__);
		return '';
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2024-05-23')]
	public function
	FetchRelatedProfiles(?array $TagsAll=NULL):
	Database\ResultSet {

		$Filters = [
			'UseSiteTags' => FALSE
		];

		////////

		if($TagsAll)
		$Filters['TagsAll'] = $TagsAll;

		////////

		$UUID = Struct\EntityRelationship::Find([
			'EntityUUID' => $this->UUID,
			'EntityType' => Profile\Entity::EntType,
			'Remappers'  => [
				fn($I)=> Struct\EntityRelationship::KeepTheOtherOne($I, $this->UUID)
			]
		]);

		$Filters['UUID'] = $UUID->Export();

		////////

		$Output = Profile\Entity::Find($Filters);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	//// LOCAL STATIC API //////////////////////////////////////////

	static public function
	Empty():
	static {

		return new static([
			'UUID' => Common\Values::NullUUID
		]);
	}

	#[Common\Meta\Date('2023-05-23')]
	#[Common\Meta\Info('Fetch an object by UUID.')]
	static public function
	GetByUUID(string $UUID):
	?static {

		return parent::GetByField('UUID', $UUID);
	}

	#[Common\Meta\Date('2023-07-28')]
	#[Common\Meta\Info('Builds a dataset based on the PropertyPatchable attributing for this class.')]
	static public function
	DatasetFromInput(array|ArrayAccess $Input):
	array {

		$PropInfos = static::GetPropertiesWithAttribute(Common\Meta\PropertyPatchable::class);
		$Output = [];
		$Prop = NULL;
		$Info = NULL;

		foreach($PropInfos as $Prop => $Info) {
			/** @var Common\Prototype\PropertyInfo $Info */

			$Has = match(TRUE) {
				$Input instanceof ArrayAccess
				=> $Input->OffsetExists($Prop),

				default
				=> array_key_exists($Prop, $Input)
			};

			if(!$Has)
			continue;

			$Filters = $Info->GetAttributes(Common\Meta\PropertyFilter::class);

			if(!count($Filters))
			continue;

			////////

			$Output[$Prop] = array_reduce(
				$Filters,
				fn(mixed $Data, callable $Func)=> $Func($Data),
				$Input[$Prop]
			);
		}

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	//// FILTER/MAPPER CALLABLES ///////////////////////////////////

	#[Common\Meta\Date('2024-05-21')]
	static public function
	MapToID(self $Inst):
	string {

		return $Inst->ID;
	}

	#[Common\Meta\Date('2023-12-27')]
	static public function
	MapToUUID(self $Inst):
	string {

		return $Inst->UUID;
	}

	#[Common\Meta\Date('2023-07-07')]
	static public function
	MapForPublicAPI(self $Inst):
	array {

		return $Inst->DescribeForPublicAPI();
	}

	#[Common\Meta\Date('2023-07-27')]
	static public function
	TagCachePrime(self $Inst):
	void {

		$Inst->GetTagLinks();
		return;
	}

}
