<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use ArrayAccess;
use Exception;

#[Database\Meta\TableClass('VideoThirdParty')]
class VideoThirdParty
extends Atlantis\Prototype {

	#[Database\Meta\TypeChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	public string
	$ParentUUID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Uploads', 'ID')]
	public int
	$CoverImageID;

	#[Database\Meta\TypeIntTiny(Unsigned: TRUE, Default: 1)]
	public int
	$Enabled;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
	public int
	$TimePosted;

	#[Database\Meta\TypeVarChar(Size: 200)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	public string
	$URL;

	#[Database\Meta\TypeVarChar(Size: 200)]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	public ?string
	$Title;

	#[Database\Meta\TypeText]
	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	public ?string
	$Details;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	#[Common\Meta\PropertyListable('Get', Common\Values::DateFormatYMD)]
	public Common\Date
	$DateCreated;

	#[Common\Meta\PropertyFactory('FromTime', 'TimePosted')]
	#[Common\Meta\PropertyListable('Get', Common\Values::DateFormatYMD)]
	public Common\Date
	$DatePosted;

	public Atlantis\Media\File
	$CoverImage;

	////////////////////////////////////////////////////////////////
	// OVERRIDE Atlantis\Prototype /////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('UP_ID'))
		$this->CoverImage = Atlantis\Media\File::FromPrefixedDataset(
			$Args->Input, 'UP_'
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	// OVERRIDE Database\Prototype /////////////////////////////////

	public function
	Patch(array|ArrayAccess $Input):
	array {

		$Output = parent::Patch($Input);

		if(isset($Input['DatePosted']))
		$Output['TimePosted'] = Common\Date::FromDateString($Input['DatePosted'])->GetUnixtime();

		return $Output;
	}

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

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['ParentUUID'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['ParentUUID'] !== NULL)
		$SQL->Where('Main.ParentUUID=:ParentUUID');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'newest':
				$SQL->Where('Main.DatePosted', $SQL::SortDesc);
			break;
			case 'oldest':
				$SQL->Where('Main.DatePosted', $SQL::SortAsc);
			break;
		}

		return;
	}

	static public function
	Insert(iterable $Input):
	?static {

		$Now = Common\Date::CurrentUnixtime();

		$Input = new Common\Datastore($Input);
		$Input->BlendRight([
			'TimeCreated' => $Now,
			'TimePosted'  => $Now,
			'URL'         => NULL
		]);

		if(!$Input['URL'])
		throw new Exception('URL is required');

		return parent::Insert($Input);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	IsYouTube():
	bool {

		$URL = strtolower($this->URL);

		return match(TRUE) {
			str_contains($URL, 'youtube'),
			str_contains($URL, 'youtu.be')
			=> TRUE,

			default
			=> FALSE
		};
	}

	public function
	GetYouTubeID():
	?string {

		$URL = parse_url($this->URL);
		$Vars = NULL;

		if(!$URL)
		return NULL;

		// youtube.com/?v=VideoID
		if(isset($URL['query']) && is_string($URL['query'])) {
			parse_str($URL['query'], $Vars);

			if(array_key_exists('v', $Vars))
			return $Vars['v'];
		}

		// youtu.be/VideoID
		if(str_contains($URL['host'], 'youtu.be')) {
			return trim($URL['path'], '/');
		}

		////////

		return NULL;
	}

	public function
	GetCoverImageURL(string $Size='md'):
	?string {

		if(isset($this->CoverImage)) {
			$URL = $this->CoverImage->GetPublicURL();
			$URL = str_replace('original.', "{$Size}.", $URL);

			return (string)Atlantis\WebURL::FromString($URL);
		}

		if($this->IsYouTube())
		return $this->FetchCoverFromYouTube();

		return NULL;
	}

	public function
	FetchCoverFromYouTube():
	?string {

		$YouTubeID = $this->GetYouTubeID();

		return sprintf(
			'https://i.ytimg.com/vi/%s/maxresdefault.jpg',
			$YouTubeID
		);
	}

	public function
	GetPageURL():
	string {

		return sprintf('/video/%d', $this->ID);
	}

}
