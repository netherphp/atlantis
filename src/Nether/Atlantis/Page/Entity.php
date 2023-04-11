<?php

namespace Nether\Atlantis\Page;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\Storage;

use Exception;

#[Database\Meta\TableClass('Pages', 'P')]
class Entity
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

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

	#[Database\Meta\TypeVarChar(Size: 12, Default: 'html')]
	public string
	$Editor;

	#[Database\Meta\TypeText]
	public string
	$Content;

	////////

	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	public Common\Date
	$DateCreated;

	#[Common\Meta\PropertyFactory('FromTime', 'TimeUpdated')]
	public Common\Date
	$DateUpdated;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Now = time();
		$Input = new Common\Datastore($Input);

		$Input->BlendRight([
			'UserID'      => NULL,
			'Title'       => NULL,
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

	static public function
	LoadStaticFile(Atlantis\Engine $App, $Alias):
	?static {
	/*//
	@date 2023-04-10
	generate a page entity from a static file on disk.
	//*/

		$Path = sprintf(
			'pages/static/%s.phtml',
			Common\Datafilters::SlottableKey($Alias)
		);

		$Location = $App->Storage->Location('Default');

		////////

		if(!$Location)
		throw new Exception('Page library unable to find Default storage.');

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
				'TimeUpdated' => strtotime($DateUpdated)
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
