<?php

namespace Nether\Atlantis\Tag;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

#[Database\Meta\TableClass('Tags', 'T')]
class Entity
extends Atlantis\Prototype {

	#[Database\Meta\TypeChar(Size: 8, Default: 'tag', Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$Type;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntTiny(Unsigned: TRUE, Default: 1, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Datafilters::class, 'TypeInt' ])]
	public int
	$Enabled;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: NULL)]
	#[Database\Meta\ForeignKey('Uploads', 'ID')]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Datafilters::class, 'TypeIntNullable' ])]
	public ?int
	$CoverImageID;

	#[Database\Meta\TypeVarChar(Size: 100, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Datafilters::class, 'SlottableKey' ])]
	public string
	$Alias;

	#[Database\Meta\TypeVarChar(Size: 100, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Datafilters::class, 'TrimmedText' ])]
	public string
	$Name;

	#[Database\Meta\TypeText]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Datafilters::class, 'TrimmedText' ])]
	public ?string
	$Details;

	////////

	public Atlantis\Media\File
	$CoverImage;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('UP_ID'))
		$this->CoverImage = Atlantis\Media\File::FromPrefixedDataset($Args->Input, 'UP_');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetCoverImageURL():
	?string {

		if(!isset($this->CoverImage))
		return NULL;

		return $this->CoverImage->GetPublicURL();
	}

	public function
	FetchPhotos():
	Database\Struct\PrototypeFindResult {

		return EntityPhoto::Find([
			'EntityID' => $this->ID,
			'Limit'    => 0
		]);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	JoinExtendTables(Database\Verse $SQL, string $JAlias='Main', ?string $TPre=NULL):
	void {

		$Table = static::GetTableInfo();
		$TPre = $Table->GetPrefixedAlias($TPre);
		$JAlias = $Table->GetPrefixedAlias($JAlias);

		Atlantis\Media\File::JoinMainTables($SQL, $JAlias, 'CoverImageID', $TPre);

		return;
	}

	static public function
	JoinExtendFields(Database\Verse $SQL, ?string $TPre=NULL):
	void {

		$Table = static::GetTableInfo();
		$TPre = $Table->GetPrefixedAlias($TPre);

		Atlantis\Media\File::JoinMainFields($SQL, $TPre);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['NameLike'] ??= NULL;
		$Input['Alias'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['NameLike'] !== NULL) {
			$SQL->Where('Main.Name LIKE :NameLikeLike');
			$Input['NameLikeLike'] = "%%{$Input['NameLike']}%%";
		}

		if($Input['Alias'] !== NULL) {
			if(is_array($Input['Alias']))
			$SQL->Where('Main.Alias IN(:Alias)');

			else
			$SQL->Where('Main.Alias=:Alias');
		}

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'title-az':
				$SQL->Sort('Main.Name', $SQL::SortAsc);
			break;
			case 'title-za':
				$SQL->Sort('Main.Name', $SQL::SortDesc);
			break;
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Input = new Common\Datastore($Input);
		$Input->BlendLeft([
			'TimeCreated' => time(),
			'UUID'        => Common\UUID::V7(),
			'Alias'       => NULL,
			'Name'        => NULL
		]);

		////////

		if(!$Input['Name'])
		throw new Exception('tag needs a name');

		////////

		if(!$Input['Alias'])
		$Input['Alias'] = Common\Datafilters::PathableKey($Input['Name']);

		if(!$Input['Alias'])
		$Input['Alias'] = Common\UUID::V7();

		////////

		return parent::Insert($Input);
	}

}
