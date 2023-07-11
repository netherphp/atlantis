<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Atlantis;

class AtlantisTagsElement
extends Atlantis\Dashboard\Element {

	public int
	$TagCount = 0;

	public function
	__Construct(Atlantis\Engine $App) {

		parent::__Construct(
			$App,
			'Tags',
			'tag/dashboard/element/main'
		);

		$this->TagCount = Atlantis\Tag\Entity::FindCount([]);

		return;
	}

	protected function
	OnReady():
	void {

		$this->Columns = 'half';
		//$this->Priority = -499;

		return;
	}

}
