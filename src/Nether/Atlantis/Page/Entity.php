<?php

namespace Nether\Atlantis\Page;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\Storage;

use Exception;

#[Database\Meta\TableClass('Pages', 'P')]
class Entity
extends Atlantis\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	public string
	$UUID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: NULL)]
	#[Database\Meta\ForeignKey('Users', 'ID')]
	public int
	$UserID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	public int
	$TimeUpdated;

	#[Database\Meta\TypeVarChar(Size: 64, Default: NULL)]
	#[Database\Meta\NullifyEmptyValue]
	public ?string
	$Alias;

	#[Database\Meta\TypeVarChar(Size: 64, Default: NULL)]
	public string
	$Title;

	#[Database\Meta\TypeVarChar(Size: 64, Default: NULL)]
	public string
	$Subtitle;

	/*
	#[Database\Meta\TypeVarChar(Size: 12, Default: 'html')]
	public string
	$Editor;

	#[Database\Meta\TypeText]
	public string
	$Content;
	*/

	////////

	public bool
	$Cached = FALSE;

	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	public Common\Date
	$DateCreated;

	#[Common\Meta\PropertyFactory('FromTime', 'TimeUpdated')]
	public Common\Date
	$DateUpdated;

	////////

	protected Common\Datastore
	$Sections;

	protected string
	$Content;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {


		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetPublicURL():
	string {

		return sprintf('/%s', $this->Alias);
	}

	public function
	GetEditURL():
	string {

		return sprintf('/dashboard/page/edit/%d', $this->ID);
	}

	public function
	GetSections():
	Database\Struct\PrototypeFindResult {

		return Section::Find([
			'PageID' => $this->ID
		]);
	}

	public function
	HasContent():
	string {

		return isset($this->Content);
	}

	public function
	GetContent():
	string {

		if(!isset($this->Content))
		throw new Exception('page has not been rendered');

		return $this->Content;
	}

	public function
	Render(Atlantis\Engine $App, bool $Force=FALSE):
	string {

		$Sect = NULL;

		////////

		if(!isset($this->Sections) || $Force)
		$this->Sections = $this->GetSections();

		////////

		$this->Content = '';

		foreach($this->Sections as $Sect) {
			/** @var Atlantis\Page\Section $Sect */
			$this->Content .= $Sect->Render($App, $this);
		}

		return $this->Content;
	}

	public function
	Write(Atlantis\Engine $App, ?string $Path=NULL):
	static {

		$Key ??= $App->Config[Atlantis\Library::ConfPageStaticStorageKey];
		$Path ??= $App->Config[Atlantis\Library::ConfPageStaticStoragePath];

		$Filename = sprintf(
			'%s/%s.phtml',
			$Path,
			Common\Datafilters::SlottableKey($this->Alias)
		);

		////////

		$Location = $App->Storage->Location($Key);

		if(!$Location)
		throw new Exception("Page library unable to find {$Key} storage.");

		////////

		$Location->Put($Filename, $this->GetContent());

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Now = time();
		$Input = new Common\Datastore($Input);

		$Input->BlendRight([
			'UUID'        => Common\UUID::V7(),
			'UserID'      => NULL,
			'Title'       => NULL,
			'Subtitle'    => NULL,
			'Alias'       => NULL,
			'TimeCreated' => $Now,
			'TimeUpdated' => $Now
		]);

		////////

		if(!$Input['Title'])
		throw new Exception('Page must have a title.');

		if(!$Input['Alias'])
		$Input['Alias'] = Common\Datafilters::PathableKey($Input['Title']);

		////////

		return parent::Insert($Input);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			default: {
				$SQL->Sort('Main.Title', $SQL::SortAsc);
				break;
			}
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	LoadStaticFile(Atlantis\Engine $App, $Alias):
	?static {
	/*//
	@date 2023-04-10
	generate a page entity from a static file on disk.
	//*/

		$StorageKey = (
			$App->Config[Atlantis\Library::ConfPageStaticStorageKey]
			?? 'Default'
		);

		$StoragePrefix = (
			$App->Config[Atlantis\Library::ConfPageStaticStoragePath]
			?? 'pages/static'
		);

		$Path = sprintf(
			'%s/%s.phtml',
			$StoragePrefix,
			Common\Datafilters::SlottableKey($Alias)
		);

		$Location = $App->Storage->Location($StorageKey);

		////////

		if(!$Location)
		throw new Exception("Page library unable to find {$StorageKey} storage.");

		if(!($Location instanceof Storage\Adaptors\Local))
		throw new Exception('Page library only works with local filesystem adaptor.');

		////////

		if(!$Location->Exists($Path))
		return NULL;

		$Filename = $Location->GetPath($Path);

		////////

		$Page = (function(string $__FILENAME, string $Alias) {
			$Title = 'Untitled Page';
			$DateCreated = date('Y-m-d');
			$DateUpdated = date('Y-m-d');

			ob_start();
			require($__FILENAME);
			$Content = ob_get_clean();

			return new static([
				'Title'       => $Title,
				'Alias'       => $Alias,
				'Content'     => $Content,
				'Editor'      => 'static',
				'TimeCreated' => strtotime($DateCreated),
				'TimeUpdated' => strtotime($DateUpdated),
				'Cached'      => TRUE
			]);
		})($Filename, $Alias);

		////////

		if(!($Page instanceof static))
		return NULL;

		return $Page;
	}

	static public function
	FromStaticFile(Atlantis\Engine $App, string $Alias):
	?static {

		$Page = static::LoadStaticFile($App, $Alias);

		if(!$Page)
		return NULL;

		////////

		return $Page;
	}

}
