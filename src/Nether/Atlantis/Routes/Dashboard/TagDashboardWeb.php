<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class TagDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/tags')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	ListGet():
	void {

		$Tags = Atlantis\Tag\Entity::Find([
			'Sort'  => 'name-az',
			'Limit' => 0
		]);

		$this->Surface->Wrap('tag/dashboard/list', [ 'Tags'=> $Tags ]);

		return;
	}

}
