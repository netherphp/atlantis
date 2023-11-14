<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

class VideoThirdPartyTagLink
extends Atlantis\Tag\EntityLink {

	const
	SortNew        = 'newest',
	SortOld        = 'oldest',
	SortPostedNew  = 'newest-posted',
	SortPostedOld  = 'oldest-posted';

	#[Atlantis\Meta\TagEntityProperty('videotp')]
	#[Database\Meta\TableJoin('EntityUUID', Extend: TRUE)]
	public VideoThirdParty
	$Video;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendSorts($SQL, $Input);

		switch($Input['Sort']) {
			case static::SortPostedNew:

			break;

			case static::SortPostedOld:

			break;

			case static::SortNew:

			break;

			case static::SortOld:

			break;
		}

		return;
	}

}
