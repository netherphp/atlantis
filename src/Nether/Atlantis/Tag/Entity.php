<?php

namespace Nether\Atlantis\Tag;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

#[Database\Meta\TableClass('Tags', 'T')]
class Entity
extends Atlantis\Prototype
implements
	Atlantis\Packages\ExtraDataInterface {

	use
	Atlantis\Packages\ExtraData;

	////////////////////////////////////////////////////////////////
	//// DATABASE FIELDS ///////////////////////////////////////////

	#[Database\Meta\TypeChar(Size: 8, Default: 'tag', Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	public string
	$Type;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	public int
	$TimeCreated = 0;

	#[Database\Meta\TypeIntTiny(Unsigned: TRUE, Default: 1, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
	public int
	$Enabled;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: NULL)]
	#[Database\Meta\ForeignKey('Uploads', 'ID')]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntNullable' ])]
	#[Common\Meta\PropertyListable]
	public ?int
	$CoverImageID;

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

	#[Database\Meta\TableJoin('CoverImageID')]
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

		$Input['Alias'] ??= NULL;
		$Input['Type'] ??= 'tag';

		$Input['NameLike'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['Type'] !== NULL)
		$SQL->Where('Main.Type=:Type');

		if($Input['Alias'] !== NULL) {
			if(is_array($Input['Alias']))
			$SQL->Where('Main.Alias IN(:Alias)');

			else
			$SQL->Where('Main.Alias=:Alias');
		}

		if($Input['NameLike'] !== NULL) {
			$SQL->Where('Main.Name LIKE :NameLikeLike');
			$Input['NameLikeLike'] = "%%{$Input['NameLike']}%%";
		}

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

		////////

		if(!$Input['Alias'])
		$Input['Alias'] = Common\Filters\Text::PathableKey($Input['Name']);

		if(!$Input['Alias'])
		$Input['Alias'] = Common\UUID::V7();

		if(!$Input['Type'])
		$Input['Type'] = 'tag';

		////////

		return parent::Insert($Input);
	}

	////////////////////////////////////////////////////////////////
	//// OVERRIDE Common\Prototype /////////////////////////////////

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
	FetchPhotos():
	Database\ResultSet {

		return EntityPhoto::Find([
			'EntityID' => $this->ID,
			'Limit'    => 0
		]);
	}

	public function
	GetCoverImageURL(string $Size='md'):
	?string {

		if(!isset($this->CoverImage))
		return NULL;

		$URL = $this->CoverImage->GetPublicURL();
		$URL = str_replace('original.', "{$Size}.", $URL);

		return (string)Atlantis\WebURL::FromString($URL);
	}

	public function
	GetPageURL():
	string {

		$Output = Atlantis\Library::Get(Atlantis\Key::PageTagViewURL);
		$Key = NULL;
		$Val = NULL;

		if($this->Type === 'topic')
		$Output = '/:Alias:';

		$Tokens = [
			':Alias:' => $this->Alias
		];

		////////

		foreach($Tokens as $Key => $Val)
		$Output = str_replace($Key, $Val, $Output);

		////////

		return (string)Atlantis\WebURL::FromString($Output);
	}

}
