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

		$Trail = new Common\Datastore([
			'Tags' => '/dashboard/tags'
		]);

		$Tags = Atlantis\Tag\Entity::Find([
			'Sort'  => 'name-az',
			'Limit' => 0
		]);

		////////

		($this->Surface)
		->Set('Page.Title', 'Manage Tags - Dashboard')
		->Wrap('atlantis/dashboard/tag/list', [
			'Trail' => $Trail,
			'Tags'  => $Tags
		]);

		return;
	}

}
