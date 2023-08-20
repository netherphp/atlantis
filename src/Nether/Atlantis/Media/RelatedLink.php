<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\Storage;

use Exception;

#[Database\Meta\TableClass('RelatedLinks', 'RL')]
class RelatedLink
extends Atlantis\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeVarChar(Size: 255, Nullable: FALSE)]
	public string
	$Title;

	#[Database\Meta\TypeVarChar(Size: 255, Nullable: FALSE)]
	public string
	$URL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	public Common\Date
	$DateCreated;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		return;
	}

	public function
	Drop():
	static {

		parent::Drop();

		Atlantis\Struct\EntityRelationship::DeleteByUUID($this->UUID);

		return $this;
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
