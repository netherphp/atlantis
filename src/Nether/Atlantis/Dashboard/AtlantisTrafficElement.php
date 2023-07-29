<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

class AtlantisTrafficElement
extends Atlantis\Dashboard\Element {

	public Database\Struct\PrototypeFindResult
	$Rows;

	public int
	$Hits;

	public int
	$Visitors;

	public Database\Struct\PrototypeFindResult
	$Sources;

	public function
	__Construct(Atlantis\Engine $App) {

		parent::__Construct(
			$App,
			'Account',
			'atlantis/dashboard/traffic/element-dash'
		);

		$Since = new Common\Date('-24 hour');

		$this->Rows = Atlantis\Struct\TrafficRow::Find([
			'Since' => $Since->GetUnixtime(),
			'Group' => 'path',
			'Sort'  => 'group-count-za',
			'Limit' => 10
		]);

		$this->Hits = Atlantis\Struct\TrafficRow::FindCount([
			'Since' => $Since->GetUnixtime()
		]);

		$this->Visitors = Atlantis\Struct\TrafficRow::FindCount([
			'Since' => $Since->GetUnixtime(),
			'Group' => 'visitor'
		]);

		$this->Sources = Atlantis\Struct\TrafficRow::Find([
			'Since'      => $Since->GetUnixtime(),
			'FromDomain' => TRUE,
			'Group'      => 'from-domain',
			'Sort'       => 'group-count-za',
			'Limit'      => 6
		]);

		$this->Sources->RemapKeys(function(mixed $K, mixed $V) {
			return [ $V->FromDomain => $V->GetGroupCount() ];
		});

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
