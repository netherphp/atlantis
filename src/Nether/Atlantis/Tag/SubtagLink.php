<?php

namespace Nether\Atlantis\Tag;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

class SubtagLink
extends EntityLink {

	#[Atlantis\Meta\TagEntityProperty('subtag')]
	#[Database\Meta\TableJoin('EntityUUID', 'T2', FALSE)]
	public Entity
	$Subtag;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReadyEntity(Common\Prototype\ConstructArgs $Args):
	void {

		//Common\Dump::Var($Args->Input, TRUE);

		if($Args->InputHas('T2_ID'))
		$this->Subtag = Entity::FromPrefixedDataset(
			$Args->Input, 'T2_'
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendSorts($SQL, $Input);

		switch($Input['Sort']) {

		}

		//Common\Dump::Var($SQL, TRUE);

		return;
	}

}
