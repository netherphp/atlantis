<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class ProfileDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/profiles')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	ListGet():
	void {

		($this->Data)
		->Q(Common\Filters\Text::TrimmedNullable(...))
		->Untagged(Common\Filters\Numbers::BoolNullable(...))
		->Page(Common\Filters\Numbers::Page(...));

		$Trail = new Common\Datastore([
			'Profiles' => '/dashboard/profiles'
		]);

		$Filters = [
			'Search'   => $this->Data->Q,
			'Untagged' => $this->Data->Untagged,
			'Page'     => $this->Data->Page,
			'Sort'     => 'name-az',
			'Limit'    => 20
		];

		$Profiles = Atlantis\Profile\Entity::Find($Filters);

		$Searched = (FALSE
			|| $this->Data->Q
			|| $this->Data->Untagged
		);

		////////

		($this->Surface)
		->Set('Page.Title', 'Manage Profiles - Dashboard')
		->Wrap('atlantis/dashboard/profile/index', [
			'Trail'    => $Trail,
			'Filters'  => $Filters,
			'Searched' => $Searched,
			'Profiles' => $Profiles
		]);

		return;
	}

}
