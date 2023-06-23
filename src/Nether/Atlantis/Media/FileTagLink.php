<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

class FileTagLink
extends TagLink {

	#[Atlantis\Meta\TagEntityProperty('file')]
	public File
	$Entity;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	_OnReady(Common\Prototype\ConstructArgs $Args):
	void {
		parent::OnReady($Args);

		if($Args->InputHas('ENT_ID'))
		$this->Entity = File::FromPrefixedDataset($Args->Input, 'ENT_');

		return;
	}

	static protected function
	FindExtendTables(Database\Verse $SQL, Common\Datastore $Input):
	void {
		parent::FindExtendTables($SQL, $Input);

		File::JoinMainTables($SQL, 'Main', 'EntityUUID', TAlias: 'ENT');
		File::JoinMainFields($SQL, TAlias: 'ENT');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {
		parent::FindExtendSorts($SQL, $Input);

		return;
	}

}
