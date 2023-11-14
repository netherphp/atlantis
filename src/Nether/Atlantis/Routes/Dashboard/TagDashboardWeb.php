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

		($this->Data)
		->Type(Atlantis\Tag\Entity::FilterValidType(...))
		->Q(Common\Filters\Text::TrimmedNullable(...));

		////////

		$Trail = Common\Datastore::FromArray([
			'Tags' => '/dashboard/tags'
		]);

		$Filters = Common\Datastore::FromArray([
			'NameLike' => $this->Data->Q,
			'Type'     => $this->Data->Type,
			'Sort'     => 'name-az',
			'Limit'    => 0
		]);

		$Tags = Atlantis\Tag\Entity::Find($Filters);

		$Searched = (FALSE
			|| $this->Data->Q
			|| $this->Data->Type !== 'tag'
		);

		////////

		($this->Surface)
		->Set('Page.Title', 'Manage Tags - Dashboard')
		->Wrap('atlantis/dashboard/tag/list', [
			'Trail'    => $Trail,
			'Tags'     => $Tags,
			'Filters'  => $Filters,
			'Searched' => $Searched
		]);

		return;
	}

}
