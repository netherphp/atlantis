<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Browser;
use Nether\Common;
use Nether\Database;
use Nether\Surface;
use Nether\Storage;

use ArrayAccess;
use Exception;

#[Database\Meta\TableClass('VideoThirdParty', 'VTP')]
class VideoThirdParty
extends Atlantis\Prototype {

	const
	EntType = 'Media.Video.ThirdParty';

	// @todo 2024-03-26
	// this needs to be renamed to ProfileUUID.
	#[Database\Meta\TypeChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	public string
	$ParentUUID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Uploads', 'ID')]
	public int
	$CoverImageID;

	#[Database\Meta\TypeIntTiny(Unsigned: TRUE, Default: 1)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
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

	#[Database\Meta\TableJoin('CoverImageID')]
	public Atlantis\Media\File
	$CoverImage;

	public ?Atlantis\Profile\Entity
	$Profile;

	////////////////////////////////////////////////////////////////
	// OVERRIDE Atlantis\Prototype /////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('UP_ID'))
		$this->CoverImage = Atlantis\Media\File::FromPrefixedDataset(
			$Args->Input, 'UP_'
		);

		////////

		// @todo 2024-03-13 migrate this to a table join.

		if(!$this->ParentUUID) {
			$this->Profile = $this->BootstrapParentProfile();
		}

		else {
			$this->Profile = Atlantis\Profile\Entity::GetByUUID($this->ParentUUID);

			if(!$this->Profile)
			$this->Profile = $this->BootstrapParentProfile();
		}

		////////

		return;
	}

	public function
	DescribeForPublicAPI():
	array {

		$Output = parent::DescribeForPublicAPI();
		$Output['PageURL'] = $this->GetPageURL(FALSE);
		$Output['ImageURL'] = $this->GetCoverImageURL();

		return $Output;
	}

	public function
	Drop():
	static {

		// this property is not parent it is profile it needs to be
		// renamed on this object.

		if($this->ParentUUID) {
			$Profile = Atlantis\Profile\Entity::GetByField(
				'UUID', $this->ParentUUID
			);

			if($Profile)
			$Profile->Drop();
		}

		parent::Drop();

		return $this;
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

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['ParentUUID'] ??= NULL;
		$Input['Enabled'] ??= 1;

		$Input['Search'] ??= NULL;
		$Input['SearchTitle'] ??= FALSE;
		$Input['SearchDetails'] ??= FALSE;
		$Input['SearchURL'] ??= FALSE;

		$Input['Untagged'] ??= NULL;

		$Input['URL'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		////////

		if($Input['ParentUUID'] !== NULL)
		$SQL->Where('Main.ParentUUID=:ParentUUID');

		if($Input['URL'] !== NULL)
		$SQL->Where('Main.URL LIKE :URL');

		if($Input['SearchURL'] !== NULL) {
			if(is_string($Input['SearchURL'])) {
				$Input['LikeSearchURL'] = "%{$Input['SearchURL']}%";
				$SQL->Where('Main.URL LIKE :LikeSearchURL');
			}
		}

		if($Input['Enabled'] !== NULL) {
			if(is_bool($Input['Enabled']))
			$Input['Enabled'] = (int)$Input['Enabled'];

			$SQL->Where('Main.Enabled=:Enabled');
		}

		static::FindExtendFilters_SearchBasicRel($SQL, $Input);
		static::FindExtendFilters_ByEntityFields_TagsAll($SQL, $Input);

		/* this was moved to prototype
		if($Input['Untagged'] === TRUE) {
			$TableTL = Atlantis\Tag\EntityLink::GetTableInfo();

			$SQL->Join(sprintf(
				'%s UTCHK ON UTCHK.EntityUUID=Main.UUID',
				$TableTL->Name
			));

			$SQL->Where('UTCHK.ID IS NULL');
		}
		*/

		return;
	}

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

		if($Input['SearchURL'])
		$InputFields->Push('Main.URL');

		if(!$InputFields->Count())
		return;

		////////

		// turn the input into a by-word regex. this creates an or search
		// with the words.

		$InputText = preg_replace('/[\h\s]{2,}/', ' ', $Input['Search']);
		$InputWords = Common\Datastore::FromArray(explode(' ', $InputText));
		$InputWords->Remap(fn(string $Word)=> preg_quote($Word));
		$InputRegex = $InputWords->Join('|');

		// bake each word into individual numbered bound parameters.

		$InputBinds = $InputWords->MapKeys(fn(int $K, string $V)=> [
			sprintf(':SearchRelCheck%d', ($K+1))
			=> sprintf("[[:<:]]%s[[:>:]]", preg_quote($V))
		]);

		// bake each word into a stack of inline math that my friend said
		// would fit right in at home in every sap report ever.

		$InputFieldCalc = $InputBinds->MapKeyValue(fn(string $K)=> sprintf(
			'CASE WHEN Main.Title RLIKE %1$s THEN %2$d ELSE 0 END+'.
			'CASE WHEN Main.Details RLIKE %1$s THEN %3$d ELSE 0 END+'.
			'CASE WHEN Main.URL RLIKE %1$s THEN %4$d ELSE 0 END',
			$K,
			($InputFields->HasValue('Main.Title') ? 1 : 0),
			($InputFields->HasValue('Main.Details') ? 1 : 0),
			($InputFields->HasValue('Main.URL') ? 1 : 0)
		));

		////////

		($Input)
		->Shove(':SearchRegEx', "[[:<:]]($InputRegex)[[:>:]]")
		->MergeRight($InputBinds);

		($SQL)
		->Where(
			$InputFields
			->Map(fn(string $F)=> sprintf("{$F} %s :SearchRegEx", ($F==='Main.URL' ? 'REGEXP BINARY' : 'REGEXP') ))
			->Join(' OR ')
		)
		->Fields(sprintf('(%s) AS RelVal', $InputFieldCalc->Join('+')))
		->Sort('RelVal', $SQL::SortDesc);

		//echo $SQL;

		return;
	}

	static protected function
	FindExtendFilters_ByEntityFields_TagsAll(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if(!is_iterable($Input['TagsAll']))
		return;

		$TLink = VideoThirdPartyTagLink::GetTableInfo();

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
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'newest-added':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
			break;
			case 'oldest-added':
				$SQL->Sort('Main.TimeCreated', $SQL::SortAsc);
			break;

			case 'newest':
				$SQL->Sort('Main.TimePosted', $SQL::SortDesc);
			break;
			case 'oldest':
				$SQL->Sort('Main.TimePosted', $SQL::SortAsc);
			break;
		}

		//Common\Dump::Var($SQL, TRUE);

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
	BootstrapParentProfile():
	Atlantis\Profile\Entity {

		$Profile = Atlantis\Profile\Entity::Insert([
			'ParentUUID' => $this->UUID,
			'Title'      => $this->Title ?: "Video Profile {$this->ID}",
			'Alias'      => sprintf('video-profile-%d', $this->ID),
			'Enabled'    => $this->Enabled,
			'Details'    => $this->Details,
			'Enabled'    => 1
		]);

		$this->Update([
			'ParentUUID' => $Profile->UUID
		]);

		return $Profile;
	}

	public function
	IsVimeo():
	bool {

		$URL = strtolower($this->URL);

		return match(TRUE) {
			str_contains($URL, 'vimeo.com'),
			str_contains($URL, 'player.vimeo.com')
			=> TRUE,

			default
			=> FALSE
		};
	}

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
	GetVimeoID():
	?string {

		$URL = parse_url($this->URL);
		$Vars = NULL;

		if(!$URL)
		return NULL;



		//https://vimeo.com/video/<VideoID>
		//https://player.vimeo.com/video/<VideoID>
		if(isset($URL['path'])) {
			if(preg_match('#/video/(\d+)#', $URL['path'], $Vars))
			return $Vars[1];

			if(preg_match('#/(\d+)#', $URL['path'], $Vars))
			return $Vars[1];
		}

		return NULL;
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

		return match(TRUE) {
			$this->IsVimeo()
			=> $this->FetchCoverFromVimeo(),

			$this->IsYouTube()
			=> $this->FetchCoverFromYouTube(),

			default
			=> NULL
		};
	}

	public function
	FetchCoverFromVimeo():
	?string {

		$VimKey = (
			Atlantis\Library::Get('Vimeo.PublicAPI.Key')
			?? throw new Common\Error\RequiredDataMissing(
				'Vimeo.PublicAPI.Key', 'config'
			)
		);

		$VimID = $this->GetVimeoID();
		$VimURL = sprintf('https://api.vimeo.com/videos/%s?access_token=%s', $VimID, $VimKey);
		$VimFile = sprintf('vid/vimeo-%s.jpg', $VimID);

		$Storage = new Storage\Manager(Atlantis\Library::Config());
		$Store = $Storage->Location('Default');
		$Browser = NULL;
		$File = NULL;
		$Data = NULL;
		$Image = NULL;

		// check if we already have the thumbnail.

		$File = $Store->GetFileObject($VimFile);

		if($File->Exists())
		return $File->GetPublicURL() . '?v=cache';

		// check if we can find out what the thumbnail is.

		$Browser = Browser\Client::FromURL($VimURL);
		$Data = $Browser->FetchAsJSON();

		if(!$Data || !isset($Data['pictures']) || !isset($Data['pictures']['base_link']))
		return NULL;

		// cache the thumbnail locally.

		$Browser->SetURL($Data['pictures']['base_link']);
		$Image = $Browser->Fetch();

		if(!$Image)
		return NULL;

		$Store->Put($VimFile, $Image);
		$File = $Store->GetFileObject($VimFile);

		return $File->GetPublicURL() . '?v=fresh';
	}

	public function
	FetchCoverFromYouTube():
	?string {

		$YouTubeID = $this->GetYouTubeID();

		return sprintf(
			'https://i.ytimg.com/vi/%s/hqdefault.jpg',
			$YouTubeID
		);

		return sprintf(
			'https://i.ytimg.com/vi/%s/maxresdefault.jpg',
			$YouTubeID
		);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetPageURL(bool $Truth=FALSE):
	string {

		return (new Atlantis\WebURL("/video/{$this->ID}"));
	}

	public function
	GetPlayerArea():
	string {

		return match(TRUE) {
			$this->IsVimeo()
			=> 'media/video/players/vimeo',

			$this->IsYouTube()
			=> 'media/video/players/youtube',

			default
			=> 'media/video/unknown-video-host'
		};
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetPlayerHTML(Surface\Engine $Surface):
	string {

		return $Surface->GetArea(
			$this->GetPlayerArea(),
			[ 'Video'=> $this ]
		);
	}

}
