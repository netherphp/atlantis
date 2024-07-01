<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class TagWeb
extends Atlantis\ProtectedWeb {

	#[Avenue\Meta\RouteHandler('/ops/tag/view/:TagID:')]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleView(string $TagID):
	void {

		if(Common\Values::IsNumericDec($TagID))
		$Tag = Atlantis\Tag\Entity::GetByID($TagID);
		else
		$Tag = Atlantis\Tag\Entity::GetByAlias($TagID);

		Common\Dump::Var($Tag, TRUE);

		return;
	}

}
