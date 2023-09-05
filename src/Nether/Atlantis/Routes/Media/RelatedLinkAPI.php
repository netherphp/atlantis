<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class RelatedLinkAPI
extends Atlantis\ProtectedAPI {

	#[Avenue\Meta\RouteHandler('/api/media/link')]
	public function
	EntityGet():
	void {

		$Ent = $this->FetchEntityByField();

		return;
	}

	protected function
	FetchEntityByField(string $Field='ID'):
	Atlantis\Media\RelatedLink {

		$ID = Common\Filters\Numbers::IntNullable($this->Data->Get($Field));

		if(!$ID)
		$this->Quit(1, "no {$Field} specified");

		////////

		$Tag = Atlantis\Media\RelatedLink::GetByID($ID);

		if(!$Tag)
		$this->Quit(2, 'related link not found');

		////////

		return $Tag;
	}

}
