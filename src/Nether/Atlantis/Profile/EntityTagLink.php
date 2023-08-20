<?php

namespace Nether\Atlantis\Profile;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

class EntityTagLink
extends Atlantis\Tag\EntityLink {

	const
	SortNew        = 'newest',
	SortOld        = 'oldest';

	#[Atlantis\Meta\TagEntityProperty('profile')]
	#[Database\Meta\TableJoin('EntityUUID', Extend: TRUE)]
	public Entity
	$Entity;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		parent::FindExtendOptions($Input);

		$Input['Enabled'] ??= 1;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		if($Input['Enabled'] !== NULL)
		$SQL->Where('PRO.Enabled=:Enabled');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendSorts($SQL, $Input);

		switch($Input['Sort']) {
			case static::SortNew:
				$SQL->Sort('PRO.TimeCreated', $SQL::SortDesc);
			break;

			case static::SortOld:
				$SQL->Sort('PRO.TimeCreated', $SQL::SortAsc);
			break;
		}

		return;
	}

}
