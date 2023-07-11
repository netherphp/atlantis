<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Atlantis;

class AtlantisMediaElement
extends Atlantis\Dashboard\Element {

	public function
	__Construct(Atlantis\Engine $App) {

		parent::__Construct(
			$App,
			'Account',
			'media/dashboard/element/main'
		);

		return;
	}

	protected function
	OnReady():
	void {

		$this->Columns = 'half';
		$this->Priority = -999;

		return;
	}

}
