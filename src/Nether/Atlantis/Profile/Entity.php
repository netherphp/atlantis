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

		if(isset($this->CoverImage)) {
			$URL = $this->CoverImage->GetPublicURL();

			foreach($this->CoverImage->ExtraFiles as $FName => $FInfo) {
				if(str_starts_with($FName, "")) {
					$URL = str_replace($this->CoverImage->Name, $FName, $URL);
					break;
				}
			}
		}

		return (string)(new Atlantis\WebURL($URL));
	}

	public function
	_Update(iterable $Dataset):
	static {

		if(isset($Dataset['SocialData']))
		$Dataset['SocialJSON'] = json_encode($Dataset['SocialJSON']);

		return parent::Update($Dataset);
	}

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['Search'] ??= NULL;
		$Input['UseSiteTags'] ??= TRUE;

		$Input['TagID'] ??= NULL;
		$Input['SubtagID'] ??= NULL;
		$Input['Enabled'] ??= 1;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		////////

		if($Input['Enabled'] !== NULL) {
			if(is_int($Input['Enabled']))
			$SQL->Where('Main.Enabled=:Enabled');
		}

		if($Input['TagID'] !== NULL)
		static::FindExtendFilters_ByEntityFields_ByTagID($SQL, $Input);

		if($Input['SubtagID'] !== NULL)
		static::FindExtendFilters_ByEntityFields_ByTagID2($SQL, $Input);

		////////

		if($Input['UseSiteTags'] === TRUE)
		static::FindExtendFilters_ByEntityFields_UseSiteTags($SQL, $Input);

		if($Input['Search'] !== NULL) {
			if(is_string($Input['Search'])) {
				$Input['SearchRegEx'] = join('|', explode(' ', $Input['Search']));
				$SQL->Where('Main.Title REGEXP :SearchRegEx');
			}
		}

		////////

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

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		return;
	}

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

}
