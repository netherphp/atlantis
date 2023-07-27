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
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendSorts($SQL, $Input);

		switch($Input['Sort']) {
			case static::SortNew:

			break;

			case static::SortOld:

			break;
		}

		return;
	}

}
