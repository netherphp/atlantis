<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Atlantis;
use Nether\Common;

class AtlantisTrafficElement
extends Atlantis\Dashboard\Element {

	public Common\Datastore
	$Rows;

	public int
	$Hits;

	public function
	__Construct(Atlantis\Engine $App) {

		parent::__Construct(
			$App,
			'Account',
			'atlantis/dashboard/element/traffic'
		);

		$Since = new Common\Date('-24 hour');

		$this->Rows = Atlantis\Struct\TrafficRow::Find([
			'Since' => $Since->GetUnixtime(),
			'Group' => 'path',
			'Sort'  => 'path-count',
			'Limit' => 10
		]);

		$this->Hits = Atlantis\Struct\TrafficRow::FindCount([
			'Since' => $Since->GetUnixtime()
		]);

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
