<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\Storage;

use ArrayAccess;
use Exception;

#[Database\Meta\TableClass('RelatedLinks', 'RL')]
class RelatedLink
extends Atlantis\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
	public int
	$TimeCreated;

	//#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	//public int
	//$TimePosted;

	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	#[Database\Meta\TypeVarChar(Size: 255, Nullable: FALSE)]
	public string
	$Title;

	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	#[Database\Meta\TypeVarChar(Size: 255, Nullable: FALSE)]
	public string
	$URL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	public Common\Date
	$DateCreated;

	//#[Common\Meta\PropertyFactory('FromTime', 'TimePosted')]
	//public Common\Date
	//$DatePosted;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		return;
	}

	public function
	DescribeForPublicAPI():
	array {

		return [
			'ID'          => $this->ID,
			'UUID'        => $this->UUID,
			'Title'       => $this->Title,
			'DateCreated' => $this->DateCreated->Get(),
			'URL'         => $this->URL
		];
	}

	public function
	Patch(array|ArrayAccess $Input):
	array {

		$Output = parent::Patch($Input);

		if(isset($Input['DateCreated'])) {
			$Output['TimeCreated'] = Common\Date::FromDateString($Input['DateCreated'])->GetUnixtime();
			unset($Output['DateCreated']);
		}

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-09-05')]
	public function
	GetDomain():
	string {

		$URL = parse_url($this->URL, PHP_URL_HOST);
		$URL ??= '';

		$URL = preg_replace('/^www\./', '', $URL);

		return $URL ?: '';
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['Sort'] ??= 'newest';

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
			break;
			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
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
		$Now = Common\Date::Unixtime();

		$Input->BlendRight([
			'TimeCreated' => $Now
		]);

		return parent::Insert($Input);
	}

}
