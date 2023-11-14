<?php

namespace Nether\Atlantis\Routes\Misc;

use Nether\Atlantis;
use Nether\Common;

class ShortURLHandler
extends Atlantis\PublicWeb {

	#[Atlantis\Meta\RouteHandler('/link/:Key:')]
	public function
	Handle(string $Key):
	void {

		($this->Data)
		->View(Common\Filters\Numbers::BoolType(...));

		////////

		$Link = Atlantis\ShortURL\Entity::GetByField('Alias', $Key);

		if(!$Link)
		$this->Quit(404, 'ShortURL not found.');

		////////

		$Link->BumpHitCount();
		$this->Goto($Link->URL);

		return;
	}

}
