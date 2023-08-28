<?php

namespace Nether\Atlantis\Profile;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use ArrayAccess;
use Exception;

#[Database\Meta\TableClass('Profiles', 'PRO')]
class Entity
extends Atlantis\Prototype {

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

		return $URL;
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
		$Input['TagID'] ??= NULL;
		$Input['UseSiteTags'] ??= TRUE;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		////////

		if($Input['UseSiteTags'] === TRUE)
		static::FindExtendFilters_ByEntityFields_UseSiteTags($SQL, $Input);

		if($Input['TagID'] !== NULL)
		static::FindExtendFilters_ByEntityFields_ByTagID($SQL, $Input);

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

		if(!$SiteTags->Count())
		return;

		////////

		$Input['SiteTags'] = $SiteTags->GetData();

		$SQL
		->Join(sprintf(
			'%s TQ1 ON Main.UUID=TQ1.EntityUUID',
			$TableLink->Name
		));

		$SQL
		->Where(sprintf(
			'TQ1.TagID IN (:TagID)'
		));

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

		$SQL->Where('RET.TagID IN(:TagID)');

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
			'Remappers'  => fn(Atlantis\Struct\EntityRelationship $P)=> $P->ChildUUID
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
