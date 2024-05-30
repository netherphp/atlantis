<?php

namespace Nether\Atlantis\Tag;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Nether\Atlantis\Plugin\Interfaces\Tag\ExtendGetPageURLInterface;
use Exception;

#[Database\Meta\TableClass('Tags', 'T')]
class Entity
extends Atlantis\Prototype
implements
	Atlantis\Packages\ExtraDataInterface {

	use
	Atlantis\Packages\CoverImage,
	Atlantis\Packages\ExtraData;

	////////////////////////////////////////////////////////////////
	//// DATABASE FIELDS ///////////////////////////////////////////

	#[Database\Meta\TypeChar(Size: 8, Default: 'tag', Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	public string
	$Type;

	#[Database\Meta\TypeChar(Size: 8, Default: 'normal', Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	public string
	$Subtype;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
	public int
	$TimeCreated = 0;

	#[Database\Meta\TypeIntTiny(Unsigned: TRUE, Default: 1, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
	public int
	$Enabled;

	#[Database\Meta\TypeVarChar(Size: 100, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'SlottableKey' ])]
	#[Common\Meta\PropertyListable]
	public string
	$Alias;

	#[Database\Meta\TypeVarChar(Size: 100, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	#[Common\Meta\PropertyListable]
	public string
	$Name;

	#[Database\Meta\TypeText]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	#[Common\Meta\PropertyListable]
	public ?string
	$Details;

	////////////////////////////////////////////////////////////////
	//// LOCAL FIELDS //////////////////////////////////////////////

	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	#[Common\Meta\PropertyListable('DescribeForPublicAPI')]
	public Common\Date
	$DateCreated;

	public Atlantis\Media\File
	$CoverImage;

	////////////////////////////////////////////////////////////////
	//// OVERRIDE Atlantis\Prototype ///////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('UP_ID'))
		$this->CoverImage = Atlantis\Media\File::FromPrefixedDataset(
			$Args->Input, 'UP_'
		);

		if(isset($this->Details))
		$this->Details = Atlantis\Util::TrimHTML($this->Details, TRUE);

		return;
	}

	public function
	DescribeForPublicAPI():
	array {

		$Data = parent::DescribeForPublicAPI();
		$Data['CoverImageURL'] = $this->GetCoverImageURL();

		return $Data;
	}

	////////////////////////////////////////////////////////////////
	//// OVERRIDE Database\Prototype Finds /////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		($Input)
		->Define('Alias', NULL)
		->Define('Type', [ 'tag', 'site' ])
		->Define('NameLike', NULL)
		->Define('ParentID', NULL);

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		if($Input['Type'] !== NULL) {
			if(is_iterable($Input['Type']))
			$SQL->Where('Main.Type IN(:Type)');
			else
			$SQL->Where('Main.Type=:Type');
		}

		if($Input['Alias'] !== NULL) {
			if(is_array($Input['Alias'])) {
				if(!count($Input['Alias']))
				$Input['Alias'] = 'null-null-null-null-null';

				$SQL->Where('Main.Alias IN(:Alias)');
			}

			else
			$SQL->Where('Main.Alias=:Alias');
		}

		if($Input['NameLike'] !== NULL) {
			$SQL->Where('Main.Name LIKE :NameLikeLike');
			$Input['NameLikeLike'] = "%%{$Input['NameLike']}%%";
		}

		static::FindExtendFilters_ParentID($SQL, $Input);

		return;
	}

	static protected function
	FindExtendFilters_ParentID(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if(!$Input['ParentID'])
		return;

		////////

		$Parent = Atlantis\Tag\Entity::GetByID($Input['ParentID']);

		if(!$Parent)
		return;

		////////

		$Table = Atlantis\Tag\EntityLink::GetTableInfo();
		$Input[':ParentUUID'] = $Parent->UUID;

		$SQL
		->Join("{$Table->Name} TPL ON (Main.ID=TPL.TagID)")
		->Where('TPL.EntityUUID=:ParentUUID');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'name-az':
			case 'title-az':
				$SQL->Sort('Main.Name', $SQL::SortAsc);
			break;

			case 'name-za':
			case 'title-za':
				$SQL->Sort('Main.Name', $SQL::SortDesc);
			break;

			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
			break;

			case 'oldest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortAsc);
			break;
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	//// OVERRIDE Database\Prototype Inserts ///////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Input = Common\Datastore::FromStackBlended($Input, [
			'TimeCreated' => Common\Date::CurrentUnixtime(),
			'UUID'        => Common\UUID::V7(),
			'Alias'       => NULL,
			'Name'        => NULL,
			'Type'        => NULL
		]);

		////////

		if(!$Input['Name'])
		throw new Exception('tag needs a name');

		if(!$Input['Alias'])
		$Input['Alias'] = Common\Filters\Text::PathableKey($Input['Name']);

		if(!$Input['Alias'])
		$Input['Alias'] = Common\UUID::V7();

		if(!$Input['Type'])
		$Input['Type'] = 'tag';

		////////

		return parent::Insert($Input);
	}

	static public function
	InsertIfMissing(iterable $Input):
	?static {

		if(!$Input['Alias'])
		throw new Exception('tag needs a alias');

		if(!$Input['Type'])
		throw new Exception('tag needs a type');

		if(!$Input['Name'])
		$Input['Name'] = $Input['Alias'];

		////////

		$Old = static::GetByField('Alias', $Input['Alias']);

		if($Old)
		return $Old;

		////////

		return static::Insert($Input);
	}

	////////////////////////////////////////////////////////////////
	//// OVERRIDE Common\Prototype /////////////////////////////////

	static public function
	GetByAlias(string $Alias):
	?static {

		return static::GetByField('Alias', $Alias);
	}

	static public function
	New(string $Type=NULL, string $Name=NULL, string $Alias=NULL):
	static {

		return new static([
			'Type'  => $Type,
			'Name'  => $Name,
			'Alias' => $Alias
		]);
	}

	////////////////////////////////////////////////////////////////
	//// LOCAL METHODS /////////////////////////////////////////////

	public function
	_FetchPhotos():
	Database\ResultSet {

		// @todo 2024-05-30 delete this, the EntityPhoto class, and the
		// TagPhotos table if renaming this broke nothing.

		return EntityPhoto::Find([
			'EntityID' => $this->ID,
			'Limit'    => 0
		]);
	}

	public function
	HasDefinedURL():
	bool {

		return $this->ExtraData->HasKey('URL');
	}

	public function
	GetDefinedURL():
	?string {

		if(!$this->ExtraData->HasKey('URL'))
		return NULL;

		return $this->ExtraData->Get('URL');
	}

	public function
	GetPageURL(?Atlantis\Engine $App=NULL):
	string {

		$Slug = NULL;
		$URL = NULL;
		$Tokens = NULL;

		// if this tag has a defined url then that is where it goes.

		if($this->HasDefinedURL())
		return $this->GetDefinedURL();

		// give plugins a chance to determine a url.

		if($App !== NULL) {
			$URL = static::ExtendGetPageURL($App, $this);

			if($URL)
			return $URL;
		}

		// @todo 2024-05-30 this slug choice should probably be done as
		// a plugin instead of being in here.

		$Slug = match($this->Type) {
			'topic' => $this->Alias,
			default => "tag/{$this->Alias}"
		};

		$Tokens = [
			':Alias:' => $this->Alias
		];

		////////

		foreach($Tokens as $Key => $Val)
		$Slug = str_replace($Key, $Val, $Slug);

		////////

		return (string)Atlantis\WebURL::FromString($Slug);
	}

	////////////////////////////////////////////////////////////////
	// COLLECTION FILTERS/SORTS ////////////////////////////////////

	static public function
	FilterValidType(mixed $Input):
	string {

		$Input = Common\Filters\Text::Trimmed($Input);

		return match($Input) {
			'tag', 'topic', 'site'
			=> $Input,

			default
			=> 'tag'
		};
	}

	static public function
	KeepThoseTyped(Entity $Tag, ?string $Type='tag'):
	bool {

		var_dump($Type);

		return $Tag->Type === $Type;
	}

	static public function
	KeepThoseTypedSite(Entity $Tag):
	bool {

		return static::KeepThoseTyped($Tag, 'site');
	}

	static public function
	SortByType(self $A, self $B):
	int {

		// -1 left, 0 no change, 1 right

		if($A->Type !== $B->Type) {
			if($A->Type === 'admin')
			return -1;

			if($B->Type === 'admin')
			return 1;

			if($A->Type === 'tag' || $A->Type === 'topic')
			return -1;

			if($B->Type === 'tag' || $B->Type === 'topic')
			return 1;
		}

		return $A->Name <=> $B->Name;
	}

	////////////////////////////////////////////////////////////////
	// PLUGIN HELPERS //////////////////////////////////////////////

	static public function
	ExtendGetPageURL(Atlantis\Engine $App, self $Tag):
	?string {

		$Plugins = $App->Plugins->GetInstanced(ExtendGetPageURLInterface::class);
		$URL = NULL;
		$Plug = NULL;

		if($Plugins->Count())
		foreach($Plugins as $Plug) {
			/** @var ExtendGetPageURLInterface $Plug */
			$URL = $Plug->GetPageURL($Tag);

			if($URL)
			return $URL;
		}

		return NULL;
	}

}
