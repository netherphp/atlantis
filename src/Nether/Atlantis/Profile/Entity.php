<?php

namespace Nether\Atlantis\Profile;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use ArrayAccess;
use Exception;

#[Database\Meta\TableClass('Profiles', 'PRO')]
class Entity
extends Atlantis\Prototype
implements Atlantis\Packages\ExtraDataInterface {

	const
	EntType = 'Profile.Entity';

	use
	Atlantis\Packages\ExtraData;

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
	public int
	$CoverImageID;

	#[Database\Meta\TypeVarChar(Size: 100)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	public string
	$Alias;

	#[Database\Meta\TypeVarChar(Size: 100)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	public string
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
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
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

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Atlantis\Prototype ///////////////////////////////

	#[Common\Meta\Date('2023-07-04')]
	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('UP_ID'))
		$this->CoverImage = Atlantis\Media\File::FromPrefixedDataset($Args->Input, 'UP_');

		return;
	}

	public function
	DescribeForPublicAPI():
	array {

		$Output = array_merge(parent::DescribeForPublicAPI(), [
			'PageURL'    => $this->GetPageURL()
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
	GetPageURL():
	string {

		$URL = new Atlantis\WebURL(sprintf(
			'/profile/%s',
			$this->Alias
		));

		return $URL->Get();
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
	IsAddressMappable():
	bool {

		return (TRUE
			&& $this->AddressCity
			&& $this->AddressState
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
	GetMapURL():
	string {

		$Output = sprintf(
			'https://maps.google.com/?q=%s',
			urlencode($this->GetAddresssLines()->Join(' '))
		);

		return $Output;
	}

	public function
	HasDetails():
	bool {

		// this is to handle the cases where you blank out the description
		// editor but it still is <div><br></div> which is common for those
		// html editors.

		if(strlen($this->Details) < 32)
		return trim(strip_tags($this->Details)) !== '';

		return !!$this->Details;
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
			$URL = $this->CoverImage->GetPublicURL();

			foreach($this->CoverImage->ExtraFiles as $FName => $FInfo) {
				if(str_starts_with($FName, "")) {
					$URL = str_replace($this->CoverImage->Name, $FName, $URL);
					break;
				}
			}
		}

		return (string)(new Atlantis\WebURL($URL ?? '/share/atlantis/gfx/misc/no-image.png'));
	}

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['UseSiteTags'] ??= TRUE;
		$Input['TagsAll'] ??= NULL;
		$Input['TagsAny'] ??= NULL;

		$Input['Search'] ??= NULL;
		$Input['SearchTitle'] ??= TRUE;
		$Input['SearchLocation'] ??= FALSE;

		$Input['ProfileID'] ??= NULL;
		$Input['Enabled'] ??= 1;
		$Input['Alias'] ??= NULL;

		$Input['AddressState'] ??= NULL;

		$Input['TagID'] ??= NULL;
		$Input['SubtagID'] ??= NULL;

		return;
	}

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

		if($Input['TagID'] !== NULL)
		static::FindExtendFilters_ByEntityFields_ByTagID($SQL, $Input);

		if($Input['SubtagID'] !== NULL)
		static::FindExtendFilters_ByEntityFields_ByTagID2($SQL, $Input);

		if($Input['Alias'] !== NULL) {
			if(is_array($Input['Alias'])) {
				$SQL->Where('Main.Alias IN(:Alias)');
			}
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

		if($Input['Search'] !== NULL) {
			if(is_string($Input['Search'])) {

				$RelChecks = Common\Datastore::FromArray(explode(' ', $Input['Search']));
				$Input->Shove(':SearchRegEx', $RelChecks->Join('|'));
				$SQL->Where('Main.Title REGEXP :SearchRegEx');

				// cook each word as an input token.

				$RelChecks->RemapKeys(
					fn(int $K, string $V)
					=> [ sprintf(':SearchRelCheck%d', ($K+1))=> "%{$V}%" ]
				);

				$Input->MergeRight($RelChecks);

				// cook each word as math.

				$RelChecks->RemapKeyValue(
					fn(string $K)
					=> sprintf('CASE WHEN Main.Title LIKE %s THEN 1 ELSE 0 END', $K)
				);

				$SQL->Fields(sprintf('(%s) AS RelVal', $RelChecks->Join('+')));
				$SQL->Sort('RelVal', $SQL::SortDesc);
			}
		}

		////////

		if($Input['AddressState'] !== NULL)
		$SQL->Where('Main.AddressState LIKE :AddressState');

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
		}

		//file_put_contents('/opt/ss-dev/temp/ok.txt', (string)$SQL . PHP_EOL . json_encode($Input));

		//echo $SQL;
		//print_r($Input);

		return;
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
			'ParentType' => 'Profile.Entity',
			'ParentUUID' => $this->UUID,

			'ChildType'  => 'Media.Video.ThirdParty',
			'Remappers'  => fn(Atlantis\Struct\EntityRelationship $P)=> $P->ChildUUID
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

		$Results = Atlantis\Struct\EntityRelationship::Find([
			'EntityUUID' => $this->UUID,
			'Remappers'  => fn($T)=> Atlantis\Struct\EntityRelationship::KeepTheOtherOne($T, $this->UUID)
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
	FetchRelatedProfiles():
	Common\Datastore {

		$UUIDS = $this->GetRelatedEntityIndex();

		$Profiles = static::Find([
			'UseSiteTags' => FALSE,
			'UUID'        => $UUIDS->GetData()
		]);

		return $Profiles;
	}

}
