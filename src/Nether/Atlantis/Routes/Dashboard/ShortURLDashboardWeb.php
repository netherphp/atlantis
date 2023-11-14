<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Common;

class ShortURLDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/shorturl/list')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	ListGet():
	void {

		($this->Data)
		->New(Common\Filters\Numbers::IntType(...));

		$Trail = new Common\Datastore([
			'ShortURLs' => '/dashboard/shorturl/list'
		]);

		$Results = Atlantis\ShortURL\Entity::Find([
			'Limit' => 0,
			'Sort'  => 'newest'
		]);

		($this->Surface)
		->Wrap('atlantis/dashboard/shorturl/list', [
			'Trail'   => $Trail,
			'Results' => $Results
		]);

		return;
	}

}
