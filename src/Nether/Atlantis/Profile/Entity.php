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
			$URL = str_replace('original.', "{$Size}.", $URL);
			return $URL;
		}

		if(isset($this->Blog->ImageHeader)) {
			$URL = $this->Blog->ImageHeader->GetPublicURL();
			$URL = str_replace('original.', "{$Size}.", $URL);
			return $URL;
		}

		return NULL;
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

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		if($Input['Search'] !== NULL) {
			if(is_string($Input['Search'])) {
				$Input['SearchRegEx'] = join('|', explode(' ', $Input['Search']));
				$SQL->Where('Main.Title REGEXP :SearchRegEx');
			}
		}

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

}
