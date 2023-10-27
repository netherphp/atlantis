<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Common;

class ShortURLDashboardAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/shorturl/entity', Verb: 'GET')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityGet():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		////////

		$ID = $this->Data->ID;
		$Entity = Atlantis\ShortURL\Entity::GetByID($ID);

		if(!$Entity)
		$this->Quit(1, 'entity not found');

		////////

		$this->SetPayload($Entity->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/shorturl/entity', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityPost():
	void {

		($this->Data)
		->URL(Common\Filters\Text::TrimmedNullable(...));

		////////

		if(!$this->Data->URL)
		$this->Quit(1, 'URL is required.');

		////////

		$Entity = Atlantis\ShortURL\Entity::Insert([
			'URL' => $this->Data->URL
		]);

		$this->SetPayload($Entity->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/shorturl/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityDelete():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		////////

		$ID = $this->Data->ID;
		$Entity = Atlantis\ShortURL\Entity::GetByID($ID);

		if(!$Entity)
		$this->Quit(1, 'entity not found');

		////////

		$Entity->Drop();

		return;
	}

}
