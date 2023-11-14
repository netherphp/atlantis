<?php

namespace Nether\Atlantis\Routes;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class EntityRelationshipAdmin
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops/eri/list')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	Index():
	void {

		$Items = Atlantis\Struct\EntityRelationship::Find([
			'Page'  => 1,
			'Limit' => 50
		]);

		Common\Dump::Var($Items, TRUE);

		return;
	}

}
