<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Atlantis;

class AtlantisTrafficElement
extends Atlantis\Dashboard\Element {

	public function
	__Construct(Atlantis\Engine $App) {

		parent::__Construct(
			$App,
			'Account',
			'atlantis/dashboard/element/traffic'
		);

		return;
	}

	protected function
	OnReady():
	void {

		$this->Columns = 'full';
		$this->Priority = -499;

		return;
	}

}
