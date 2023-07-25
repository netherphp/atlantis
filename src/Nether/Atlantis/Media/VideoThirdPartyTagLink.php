<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

class VideoThirdPartyTagLink
extends Atlantis\Tag\EntityLink {

	#[Atlantis\Meta\TagEntityProperty('videotp')]
	public VideoThirdParty
	$Video;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendTables(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendTables($SQL, $Input);

		VideoThirdParty::JoinMainTables($SQL, 'Main', 'EntityUUID', TAlias: 'ENT');
		VideoThirdParty::JoinMainFields($SQL, TAlias: 'ENT');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendSorts($SQL, $Input);

		switch($Input['Sort']) {
			// ...
		}

		return;
	}

}
