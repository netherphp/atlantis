<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Atlantis;

class AtlantisAccountElement
extends Atlantis\Dashboard\Element {

	public function
	__Construct(Atlantis\Engine $App) {

		parent::__Construct(
			$App,
			'Account',
			'user/dashboard/element/main'
		);

		return;
	}

	protected function
	OnReady():
	void {

		$this->Columns = 'half';
		$this->Priority = -499;

		return;
	}

}
