<?php

namespace Nether\Atlantis\Profile;

use Nether\Atlantis;
use Nether\Blog;
use Nether\Common;
use Nether\Database;

use ArrayAccess;
use Exception;
use Nether\Atlantis\Plugin\Interfaces\Profile\ExtendFindOptionsInterface;
use Nether\Atlantis\Plugin\Interfaces\Profile\ExtendFindFiltersInterface;
use Nether\Atlantis\Plugin\Interfaces\Profile\ExtendFindSortsInterface;
use Nether\Atlantis\Plugin\Interfaces\Profile\ExtendGetPageURLInterface;
use Nether\Atlantis\Plugin\Interfaces\Profile\ExtendGetTitleInterface;

#[Database\Meta\TableClass('Profiles', 'PRO')]
class Entity
extends Atlantis\Prototype
implements Atlantis\Interfaces\ExtraDataInterface {

	const
	EntType = 'Profile.Entity';

	use
	Atlantis\Packages\ExtraData;

	#[Database\Meta\TypeChar(Size: 36, Nullable: TRUE, Default: NULL)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\Info('If set, defines the UUID of an object this is a meta-profile for.')]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'UUID' ])]
	public string
	$ParentUUID;

	// this needs to go away. it was an artifact of the civil uid that
	// was invented for dealing with a google api.
	#[Database\Meta\TypeVarChar(Size: 128)]
	#[Database\Meta\FieldIndex]
	public string
	$CUID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntTiny(Unsigned: TRUE, Default: 0)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
	public int
	$Enabled = 1;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Uploads', 'ID')]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntNullable' ])]
	public ?int
	$CoverImageID;

	#[Database\Meta\TypeVarChar(Size: 100)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public string
	$Alias;

	#[Database\Meta\TypeVarChar(Size: 100)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$Title;

	#[Database\Meta\TypeVarChar(Size: 120)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$AddressStreet1;

	#[Database\Meta\TypeVarChar(Size: 120)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$AddressStreet2;

	#[Database\Meta\TypeVarChar(Size: 50)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$AddressCity;

	#[Database\Meta\TypeVarChar(Size: 3)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$AddressState;

	#[Database\Meta\TypeVarChar(Size: 24)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$AddressPostalCode;

	#[Database\Meta\TypeText]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public string
	$Details;

	#[Database\Meta\TypeText]
	public string
	$SocialJSON;

	////////

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	public Common\Date
	$DateCreated;

	#[Database\Meta\TableJoin('CoverImageID')]
	public Atlantis\Media\File
	$CoverImage;

	#[Common\Meta\PropertyFactory('FromJSON', 'SocialJSON')]
	#[Common\Meta\PropertyListable]
	public Atlantis\Struct\SocialData
	$SocialData;

	////////////////////////////////////////////////////////////////
	//// LOCAL FIELDS //////////////////////////////////////////////

	protected Common\Datastore
	$RelCache;

	protected int
	$RelVal = 0;

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Atlantis\Prototype ///////////////////////////////

	#[Common\Meta\Date('2023-07-04')]
	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('UP_ID'))
		$this->CoverImage = Atlantis\Media\File::FromPrefixedDataset($Args->Input, 'UP_');

		//if(!isset($this->CoverImage))
		//if($this->CoverImageID)
		//$this->CoverImage = Atlantis\Media\File::GetByID($this->CoverImageID);

		return;
	}

	public function
	DescribeForPublicAPI():
	array {

		$Output = array_merge(parent::DescribeForPublicAPI(), [
			'TitleFull' => $this->GetTitle(),
			'PageURL'   => $this->GetPageURL()
		]);

		return $Output;
	}

	public function
	GetExcerpt(int $Len=100):
	string {

		$Output = preg_replace('#<[Bb][Rr] ?/?>#', ' ', $this->Details);
		$Bits = explode(' ', strip_tags($Output), ($Len + 1));
		$Output = join(' ', array_slice($Bits, 0, $Len));

		if(count($Bits) > $Len)
		if(!str_ends_with($Output, '.'))
		$Output .= '...';

		return $Output;
	}

	public function
	GetCoverImageStyleBG():
	string {

		return sprintf(
			'background-image: url(%s);',
			$this->GetCoverImageURL('md')
		);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetDetails():
	string {

		return $this->Details;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetTitle():
	string {

		$Title = (NULL
			?? static::ExtendGetTitle($this)
			?? $this->Title
		);

		return $Title ?: '';
	}

	static public function
	ExtendGetTitle(self $Profile):
	?string {

		$Plugins = static::$AppInstance->Plugins->GetInstanced(
			ExtendGetTitleInterface::class
		);

		$Title = NULL;
		$Plug = NULL;

		if($Plugins->Count())
		foreach($Plugins as $Plug) {
			/** @var ExtendGetTitleInterface $Plug */
			$Title = $Plug->GetTitle($Profile);

			if($Title)
			return $Title;
		}

		return NULL;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetPageURL(?Atlantis\Engine $App=NULL):
	string {

		$Slug = NULL;
		$URL = NULL;
		$App ??= static::$AppInstance;

		// give plugins a chance to determine the url.

		if($App !== NULL) {
			$URL = static::ExtendGetPageURL($App, $this);

			if($URL !== NULL)
			return Atlantis\Util::RewriteURL($URL);
		}

		// otherwise fall back to default behaviour.

		$Slug = sprintf('/profile/%s', $this->Alias);
		$URL = new Atlantis\WebURL($Slug);

		return $URL->Get();
	}

	static public function
	ExtendGetPageURL(Atlantis\Engine $App, self $Profile):
	?string {

		$Plugins = $App->Plugins->GetInstanced(ExtendGetPageURLInterface::class);
		$URL = NULL;
		$Plug = NULL;

		if($Plugins->Count())
		foreach($Plugins as $Plug) {
			/** @var ExtendGetPageURLInterface $Plug */
			$URL = $Plug->GetPageURL($Profile);

			if($URL)
			return $URL;
		}

		return NULL;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// we might move the address fields to a trait.

	#[Common\Meta\Date('2023-12-01')]
	public function
	IsAddressFull():
	bool {

		return (TRUE
			&& $this->AddressStreet1 !== NULL
			&& $this->AddressCity !== NULL
			&& $this->AddressState !== NULL
			&& $this->AddressPostalCode !== NULL
		);
	}

	#[Common\Meta\Date('2023-12-01')]
	public function
	IsAddressBarelyMappable():
	bool {

		return (TRUE
			&& isset($this->AddressCity) && trim($this->AddressCity)
			&& isset($this->AddressState) && trim($this->AddressState)
		);
	}

	#[Common\Meta\Date('2023-12-01')]
	public function
	IsAddressMappable():
	bool {

		return (TRUE
			&& isset($this->AddressStreet1) && trim($this->AddressStreet1)
			&& isset($this->AddressCity) && trim($this->AddressCity)
			&& isset($this->AddressState) && trim($this->AddressState)
		);
	}

	public function
	GetAddresssLines():
	Common\Datastore {

		$Output = new Common\Datastore;

		if($this->AddressStreet1)
		$Output->Push($this->AddressStreet1);

		if($this->AddressStreet2)
		$Output->Push($this->AddressStreet2);

		////////

		if($this->AddressCity && $this->AddressState && $this->AddressPostalCode) {
			$Output->Push(sprintf(
				'%s, %s %s',
				$this->AddressCity,
				$this->AddressState,
				$this->AddressPostalCode
			));
		}

		elseif($this->AddressState && $this->AddressPostalCode) {
			$Output->Push(sprintf(
				'%s %s',
				$this->AddressState,
				$this->AddressPostalCode
			));
		}

		return $Output;
	}

	public function
	GetAddressConcat():
	string {

		return $this->GetAddresssLines()->Join(' ');
	}

	public function
	GetMapURL():
	string {

		$Output = sprintf(
			'https://maps.google.com/?q=%s',
			urlencode($this->GetAddresssLines()->Join(' '))
		);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasDetails():
	bool {

		// this is to handle the cases where you blank out the description
		// editor but it still is <div><br></div> which is common for those
		// html editors.

		if(strlen($this->Details) < 32)
		return trim(strip_tags($this->Details)) !== '';

		return TRUE;
	}

	public function
	HasAdminNotes():
	bool {

		if(!$this->HasExtraData('AdminNotes'))
		return FALSE;

		////////

		$Notes = $this->GetExtraData('AdminNotes');

		// this is to handle the front end sending us a text null when it
		// was holding a real null. TrimmedNullable was updated to consider
		// that so there must be another.

		if(strip_tags($Notes) === 'null')
		return FALSE;

		// this is to handle the cases where you blank out the description
		// editor but it still is <div><br></div> which is common for those
		// html editors.

		if(strlen($Notes) < 32)
		return trim(strip_tags($Notes)) !== '';

		////////

		return TRUE;
	}

	public function
	HasGeoCoords():
	bool {

		$Coord = $this->GetExtraData('GeoCoord');
		$Vec = NULL;

		////////

		if(is_array($Coord))
		$Vec = Common\Units\Vec2::FromVectorkin($Coord);

		////////

		if($Vec && $Vec->X !== 0 && $Vec->Y !== 0)
		return TRUE;

		////////

		return FALSE;
	}

	public function
	GetGeoCoords():
	?Common\Units\Vec2 {

		$Coord = $this->GetExtraData('GeoCoord');
		$Vec = NULL;

		////////

		if(is_array($Coord))
		$Vec = Common\Units\Vec2::FromVectorkin($Coord);

		////////

		if($Vec && $Vec->X !== 0 && $Vec->Y !== 0)
		return $Vec;

		////////

		return NULL;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetSearchRelVal():
	int {

		return $this->RelVal;
	}

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Database\Prototype ///////////////////////////////

	public function
	Patch(array|ArrayAccess $Input):
	array {

		$Output = parent::Patch($Input);
		$SocialData = [];

		////////

		$Key = TRUE;
		$Kon = TRUE;

		foreach(Atlantis\Struct\SocialData::Icons as $Key => $Kon) {
			if(isset($Input["SocialData{$Key}"]))
			$SocialData[$Key] = Common\Filters\Text::TrimmedNullable(
				$Input["SocialData{$Key}"]
			);
		}

		if(count($SocialData))
		$Output['SocialJSON'] = json_encode($SocialData);

		////////

		return $Output;
	}

	public function
	GetCoverImageURL(string $Size='md'):
	?string {

		$URL = NULL;

		//var_dump($URL);

		if(isset($this->CoverImage)) {
			$URL = $this->CoverImage->GetPublicURL($Size);

			foreach($this->CoverImage->ExtraFiles as $FName => $FInfo) {
				if(str_starts_with($FName, "{$Size}.")) {
					// no surprise if this craters the windows tests.
					// you'll be back.
					$URL = sprintf('%s/%s', dirname($URL), $FName);
					break;
				}
			}
		}

		return (string)(new Atlantis\WebURL($URL ?? '/share/atlantis/gfx/misc/no-image.png'));
	}

	public function
	HasCoverImage():
	bool {


		//if(!isset($this->CoverImage))
		//if($this->CoverImageID)
		//$this->CoverImage = Atlantis\Media\File::GetByID($this->CoverImageID);

		return isset($this->CoverImage);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input->Define([
			'ParentUUID'  => FALSE,
			'AliasPrefix' => NULL
		]);

		////////

		$Input['UseSiteTags'] ??= TRUE;
		$Input['TagsAll'] ??= NULL;
		$Input['TagsAny'] ??= NULL;

		$Input['Search'] ??= NULL;
		$Input['SearchTitle'] ??= TRUE;
		$Input['SearchDetails'] ??= FALSE;

		$Input['SearchLocation'] ??= FALSE;

		$Input->Define('Enabled', 1);

		$Input['ProfileID'] ??= NULL;

		$Input['Alias'] ??= NULL;

		$Input['AddressState'] ??= NULL;

		$Input['Cleanup'] ??= NULL;
		$Input['TagID'] ??= NULL;
		$Input['SubtagID'] ??= NULL;

		static::FindExtendOptionsFromPlugins($Input);

		return;
	}

	static protected function
	FindExtendOptionsFromPlugins(Common\Datastore $Input):
	void {

		/** @var ?Engine $App */

		$App = defined('Atlantis') ? constant('Atlantis') : NULL;

		if(!$App)
		$App = static::AppInstanceGet();

		if(!$App)
		throw new Common\Error\RequiredDataMissing('Magic Atlantis');

		////////

		($App->Plugins)
		->GetInstanced(ExtendFindOptionsInterface::class)
		->Each(
			fn(ExtendFindOptionsInterface $P)
			=> $P->AddFindOptions($Input)
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		////////

		if($Input['ProfileID'] !== NULL) {
			if(is_array($Input['ProfileID']))
			$SQL->Where('Main.ID IN (:ProfileID)');
		}

		if($Input['Enabled'] !== NULL) {
			if(is_int($Input['Enabled']))
			$SQL->Where('Main.Enabled=:Enabled');
		}

		////////

		if($Input['ParentUUID'] === FALSE)
		$SQL->Where('Main.ParentUUID IS NULL');

		elseif(is_string($Input['ParentUUID']))
		$SQL->Where('Main.ParentUUID=:ParentUUID');

		////////

		if($Input['TagID'] !== NULL)
		static::FindExtendFilters_ByEntityFields_ByTagID($SQL, $Input);

		if($Input['SubtagID'] !== NULL)
		static::FindExtendFilters_ByEntityFields_ByTagID2($SQL, $Input);

		if($Input['Alias'] !== NULL) {
			if(is_array($Input['Alias'])) {
				$SQL->Where('Main.Alias IN(:Alias)');
			}
		}

		if($Input['AliasPrefix'] !== NULL) {
			$Input[':AliasPrefixLike'] = sprintf('%s%%', $Input['AliasPrefix']);
			$SQL->Where('Main.Alias LIKE :AliasPrefixLike');
		}

		////////

		// this is a porting of the tags and code from the blog post
		// which should eventually replace TagID, SubtagID, and likely alter
		// the api for use site tags a little.

		if($Input['UseSiteTags'] === TRUE)
		static::FindExtendFilters_ByEntityFields_UseSiteTags($SQL, $Input);

		if($Input['TagsAll'] !== NULL)
		static::FindExtendFilters_ByEntityFields_TagsAll($SQL, $Input);

		if($Input['TagsAny'] !== NULL)
		static::FindExtendFilters_ByEntityFields_TagsAny($SQL, $Input);

		////////

		static::FindExtendFilters_SearchBasicRel($SQL, $Input);

		////////

		if($Input['AddressState'] !== NULL)
		$SQL->Where('Main.AddressState LIKE :AddressState');

		////////

		if($Input['Cleanup'] === TRUE) {
			// these are stupid problems i created for myself with
			// solutions that just take me time to loop back around.
			// this should be replaced with an option that checks if
			// parent uuid is null because these are meta profiles.
			$SQL->Where('Main.Alias NOT LIKE "video-profile-%"');
		}

		////////

		if($Input['Mappable'] === TRUE) {
			$SQL->Where('JSON_EXISTS(Main.ExtraJSON, "$.GeoCoord")');
		}

		static::FindExtendFiltersFromPlugins($SQL, $Input);

		return;
	}

	static protected function
	FindExtendFiltersFromPlugins(Database\Verse $SQL, Common\Datastore $Input):
	void {

		/** @var ?Engine $App */

		$App = defined('Atlantis') ? constant('Atlantis') : NULL;

		if(!$App)
		$App = static::AppInstanceGet();

		if(!$App)
		throw new Common\Error\RequiredDataMissing('Magic Atlantis');

		////////

		($App->Plugins)
		->GetInstanced(ExtendFindFiltersInterface::class)
		->Each(
			fn(ExtendFindFiltersInterface $P)
			=> $P->AddFindFilters($SQL, $Input)
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendFilters_SearchBasicRel(Database\Verse $SQL, Common\Datastore $Input):
	void {

		$InputFields = NULL;
		$InputFieldCalc = NULL;
		$InputBinds = NULL;
		$InputText = NULL;
		$InputWords = NULL;
		$InputRegex = NULL;

		////////

		// determine if a search value was input with bailing out as
		// the default.

		if(!is_string($Input['Search']) || !$Input['Search'])
		return;

		///////

		// determine where to search and bail if the answer ended up
		// being nowhere.

		$InputFields = new Common\Datastore;

		if($Input['SearchTitle'])
		$InputFields->Push('Main.Title');

		if($Input['SearchDetails'])
		$InputFields->Push('Main.Details');

		if(!$InputFields->Count())
		return;

		////////

		// turn the input into a by-word regex. this creates an or search
		// with the words.

		$InputText = preg_replace('/[\h\s]{2,}/', ' ', $Input['Search']);
		$InputWords = Common\Datastore::FromArray(explode(' ', $InputText));
		$InputRegex = $InputWords->Join('|');

		// bake each word into individual numbered bound parameters.

		$InputBinds = $InputWords->MapKeys(fn(int $K, string $V)=> [
			sprintf(':SearchRelCheck%d', ($K+1))
			=> "{$V}"
		]);

		// bake each word into a stack of inline math that my friend said
		// would fit right in at home in every sap report ever.

		$InputFieldCalc = $InputBinds->MapKeyValue(fn(string $K)=> sprintf(
			'CASE WHEN Main.Title RLIKE %1$s THEN %2$s ELSE 0 END+'.
			'CASE WHEN Main.Details RLIKE %1$s THEN %3$s ELSE 0 END',
			$K,
			($InputFields->HasValue('Main.Title') ? 1 : 0),
			($InputFields->HasValue('Main.Details') ? 1 : 0)
		));

		////////

		($Input)
		->Shove(':SearchRegEx', "($InputRegex)")
		->MergeRight($InputBinds);

		($SQL)
		->Where(
			$InputFields
			->Map(fn(string $F)=> "{$F} REGEXP :SearchRegEx")
			->Join(' OR ')
		)
		->Fields(sprintf('(%s) AS RelVal', $InputFieldCalc->Join('+')))
		->Sort('RelVal', $SQL::SortDesc);

		return;
	}

	static protected function
	FindExtendFilters_ByEntityFields_UseSiteTags(Database\Verse $SQL, Common\Datastore $Input):
	void {

		$SiteTags = Atlantis\Util::FetchSiteTags();
		$TableLink = EntityTagLink::GetTableInfo();
		$TLK = 0;
		$Tag = NULL;

		if(!$SiteTags->Count())
		return;

		////////

		$Input['SiteTags'] = $SiteTags->GetData();

		foreach($SiteTags as $Tag) {
			$TLK += 1;

			$Input[":SiteTagID{$TLK}"] = $Tag->ID;

			$SQL
			->Join(sprintf(
				'%1$s TQ%2$d ON Main.UUID=TQ%2$d.EntityUUID',
				$TableLink->Name,
				$TLK
			))
			->Where(sprintf(
				'TQ%1$d.TagID IN (:SiteTagID%1$d)',
				$TLK
			));
		}

		$SQL
		->Group('Main.ID');

		return;
	}

	static protected function
	FindExtendFilters_ByEntityFields_TagsAll(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if(!is_iterable($Input['TagsAll']))
		return;

		$TLink = EntityTagLink::GetTableInfo();

		$GenTrainAnd = (function() use($SQL, $Input, $TLink) {

			// this method generates a logical and restriction upon the
			// main table by joining each tag over and over and honestly
			// it is unclear if this is going to be a good idea or not.

			$Key = 0;
			$ID = NULL;
			$TableQA = NULL;
			$FieldQA = NULL;

			foreach($Input['TagsAll'] as $ID) {
				$Key += 1;

				$TableQA = "TQA{$Key}";
				$FieldQA = ":TagQA{$Key}";

				$SQL->Join(sprintf(
					'%s ON %s=%s',
					$TLink->GetAliasedTable($TableQA),
					$SQL::MkQuotedField('Main', 'UUID'),
					$SQL::MkQuotedField($TableQA, 'EntityUUID')
				));

				$SQL->Where(sprintf(
					'%s=%s',
					$SQL::MkQuotedField($TableQA, 'TagID'),
					$FieldQA
				));

				$Input[$FieldQA] = $ID;
			}

			return;
		});

		$GenTrainAnd();

		return;
	}

	static protected function
	FindExtendFilters_ByEntityFields_TagsAny(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if(!is_iterable($Input['TagsAny']))
		return;

		$TLink = EntityTagLink::GetTableInfo();

		$GenBasicOr = (function() use($SQL, $Input, $TLink) {

			// this result set ends up being that of a logical or and
			// i have yet to find a way to make it very useful.

			$TableQA = "TQOR";

			$SQL->Join(sprintf(
				'%s ON %s=%s',
				$TLink->GetAliasedTable($TableQA),
				$SQL::MkQuotedField('Main', 'UUID'),
				$SQL::MkQuotedField($TableQA, 'EntityUUID')
			));

			$SQL->Where(sprintf(
				'%s IN(:TagsAny)',
				$SQL::MkQuotedField($TableQA, 'TagID')
			));

			$SQL->Group('Main.ID');

			if($Input['TagID'] instanceof Common\Datastore)
			$Input['TagID'] = $Input['TagID']->GetData();

			return;
		});

		$GenBasicOr();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Deprecated('2023-12-01')]
	static protected function
	FindExtendFilters_ByEntityFields_ByTagID(Database\Verse $SQL, Common\Datastore $Input):
	void {

		$TableTag = Atlantis\Tag\EntityLink::GetTableInfo();

		$Input['TagID'] = match(TRUE) {
			is_array($Input['TagID'])
			=> $Input['TagID'],

			default
			=> [ $Input['TagID'] ]
		};

		////////

		$SQL->Join(sprintf(
			'%s RET on RET.EntityUUID=Main.UUID',
			$TableTag->Name
		));

		$SQL->Where('RET.TagID IN(:TagID) AND RET.TagID IS NOT NULL');


		return;
	}

	#[Common\Meta\Deprecated('2023-12-01')]
	static protected function
	FindExtendFilters_ByEntityFields_ByTagID2(Database\Verse $SQL, Common\Datastore $Input):
	void {

		$TableTag = Atlantis\Tag\EntityLink::GetTableInfo();

		$Input['SubtagID'] = match(TRUE) {
			is_array($Input['SubtagID'])
			=> $Input['SubtagID'],

			default
			=> [ $Input['SubtagID'] ]
		};

		////////

		$SQL->Join(sprintf(
			'%s RET2 on RET2.EntityUUID=Main.UUID',
			$TableTag->Name
		));

		$SQL->Where('RET2.TagID IN(:SubtagID)');

		//Common\Dump::Var($SQL, TRUE);
		//Common\Dump::Var($Input, TRUE);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {

			case 'name-az':
			case 'title-az':
				$SQL->Sort('Main.Title', $SQL::SortAsc);
				break;

			case 'name-za':
			case 'title-za':
				$SQL->Sort('Main.Title', $SQL::SortDesc);
				break;

			case 'state-az':
				$SQL->Sort('Main.AddressState', $SQL::SortAsc);
				break;

			case 'state-za':
				$SQL->Sort('Main.AddressState', $SQL::SortDesc);
				break;

			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
				break;
		}

		static::FindExtendSortsFromPlugins($SQL, $Input);

		return;
	}

	static protected function
	FindExtendSortsFromPlugins(Database\Verse $SQL, Common\Datastore $Input):
	void {

		/** @var ?Engine $App */

		$App = defined('Atlantis') ? constant('Atlantis') : NULL;

		if(!$App)
		$App = static::AppInstanceGet();

		if(!$App)
		throw new Common\Error\RequiredDataMissing('Magic Atlantis');

		////////

		($App->Plugins)
		->GetInstanced(ExtendFindSortsInterface::class)
		->Each(
			fn(ExtendFindSortsInterface $P)
			=> $P->AddFindSorts($SQL, $Input)
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GetByAlias(string $Alias):
	?static {

		return static::GetByField('Alias', $Alias);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Now = new Common\Date;

		$Input = Common\Datastore::NewBlended($Input, [
			'TimeCreated' => $Now->GetUnixtime(),
			'Title'       => NULL,
			'Alias'       => NULL
		]);

		////////

		if(!$Input['Title'])
		throw new Common\Error\RequiredDataMissing('Title', 'string');

		if(!$Input['Alias'])
		$Input['Alias'] = Common\Filters\Text::PathableKey($Input['Title']);

		if(!$Input['Alias'])
		throw new Common\Error\RequiredDataMissing('Alias', 'string');

		////////

		return parent::Insert($Input);
	}

	#[Common\Meta\Date('2025-02-21')]
	#[Common\Meta\Info('Fetch a profile matching this alias, making a new one if it does not exist yet.')]
	static public function
	Touch(string $Alias, string $Title, int $Enabled=0):
	static {

		$Profile = static::GetByAlias($Alias);

		////////

		if(!$Profile)
		$Profile = static::Insert([
			'Alias'   => $Alias,
			'Title'   => $Title,
			'Enabled' => $Enabled
		]);

		////////

		return $Profile;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-07-28')]
	public function
	FetchRelationships(string $Type):
	Database\ResultSet {

		$Index = Atlantis\Struct\EntityRelationship::Find([
			'ParentType' => 'Profile.Entity',
			'ParentUUID' => $this->UUID,

			'ChildType'  => $Type,
			'Remappers'  => fn(Atlantis\Struct\EntityRelationship $P)=> $P->ChildUUID
		]);

		if(!$Index->Count())
		$Index->Push('null-null-null');

		$Class = Atlantis\Struct\EntityRelationship::TypeClass($Type);

		$Result = ($Class)::Find([
			'UUID'    => $Index->GetData(),
			'Sort'    => 'newest',
			'Limit'   => 0
		]);

		return $Result;
	}

	public function
	FetchNews():
	?Common\Datastore {

		if(!class_exists('Nether\Blog\Post'))
		return NULL;

		////////

		$Index = Atlantis\Struct\EntityRelationship::Find([
			'EntityUUID' => $this->UUID,
			'EntityType' => Blog\Post::EntType,
			'Remappers'  => (
				fn(Atlantis\Struct\EntityRelationship $ERI)
				=> Atlantis\Struct\EntityRelationship::KeepTheOtherOne($ERI, $this->UUID)
			)
		]);

		if(!$Index->Count())
		$Index->Push('null-null-null');

		$Result = Blog\Post::Find([
			'UUID'    => $Index->GetData(),
			'Sort'    => 'newest',
			'Limit'   => 0
		]);

		return $Result;
	}

	#[Common\Meta\Date('2023-07-28')]
	public function
	FetchPhotos():
	Common\Datastore {

		$Index = Atlantis\Struct\EntityRelationship::Find([
			'ParentType' => 'Profile.Entity',
			'ParentUUID' => $this->UUID,

			'ChildType'  => 'Media.Image',
			'Remappers'  => fn(Atlantis\Struct\EntityRelationship $P)=> $P->ChildUUID
		]);

		if($this->HasCoverImage())
		$Index->Filter(fn(string $U)=> $U !== $this->CoverImage->UUID);

		if(!$Index->Count())
		$Index->Push('null-null-null');

		$Result = Atlantis\Media\File::Find([
			'UUID'    => $Index->GetData(),
			'Sort'    => 'newest',
			'Limit'   => 0
		]);

		return $Result;
	}

	#[Common\Meta\Date('2023-07-28')]
	public function
	FetchVideos():
	Common\Datastore {

		$Index = Atlantis\Struct\EntityRelationship::Find([
			'EntityUUID' => $this->UUID,
			'EntityType' => Atlantis\Media\VideoThirdParty::EntType,
			'Remappers'  => [
				fn(Atlantis\Struct\EntityRelationship $ER)=>
				Atlantis\Struct\EntityRelationship::KeepTheOtherUUID(
					$ER, $this->UUID
				)
			]
		]);

		if(!$Index->Count())
		$Index->Push('null-null-null');

		$Result = Atlantis\Media\VideoThirdParty::Find([
			'UUID'    => $Index->GetData(),
			'Sort'    => 'newest',
			'Limit'   => 0
		]);

		return $Result;
	}

	#[Common\Meta\Date('2023-08-18')]
	public function
	FetchRelatedLinks():
	Common\Datastore {

		$Index = Atlantis\Struct\EntityRelationship::Find([
			'ParentType' => 'Profile.Entity',
			'ParentUUID' => $this->UUID,

			'ChildType'  => 'Media.Related.Link',
			'Remappers'  => fn(Atlantis\Struct\EntityRelationship $P)=> $P->ChildUUID,

			'Limit'      => 0
		]);

		if(!$Index->Count())
		$Index->Push('null-null-null');

		$Result = Atlantis\Media\RelatedLink::Find([
			'UUID'    => $Index->GetData(),
			'Sort'    => 'newest',
			'Limit'   => 0
		]);

		return $Result;
	}

	#[Common\Meta\Date('2023-12-15')]
	public function
	FetchRelatedEntityIndex():
	Common\Datastore {

		$AnchorUUID = [ $this->UUID ];

		if($this->ParentUUID)
		$AnchorUUID[] = $this->ParentUUID;

		$Results = Atlantis\Struct\EntityRelationship::Find([
			'EntityUUID'  => $AnchorUUID,
			'Remappers'   => fn($T)=> Atlantis\Struct\EntityRelationship::KeepTheOtherOne($T, $this->UUID)
		]);

		return Common\Datastore::FromArray($Results->GetData());
	}

	#[Common\Meta\Date('2023-12-15')]
	public function
	GetRelatedEntityIndex():
	Common\Datastore {

		if(!isset($this->RelCache))
		$this->RelCache = $this->FetchRelatedEntityIndex();

		return $this->RelCache;
	}

	#[Common\Meta\Date('2023-12-15')]
	public function
	FetchRelatedProfiles(?array $TagsAll=NULL):
	Database\ResultSet {

		$UUIDS = $this->GetRelatedEntityIndex();

		$Profiles = static::Find([
			'UseSiteTags' => FALSE,
			'UUID'        => $UUIDS->GetData(),
			'Limit'       => 0
		]);

		return $Profiles;
	}

}
