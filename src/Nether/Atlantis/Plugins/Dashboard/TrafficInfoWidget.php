<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Plugins\Dashboard;

use Nether\Atlantis;
use Nether\Common;

use Nether\Atlantis\Struct\TrafficRow;

################################################################################
################################################################################

class TrafficInfoWidget
extends Atlantis\Plugin
implements Atlantis\Plugin\Interfaces\Dashboard\InfoWidgetInterface {

	public int
	$Hits = 0;

	public int
	$Visitors = 0;

	public int
	$Pages = 0;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetSorting():
	int {

		return 20;
	}

	public function
	GetColumnSizes():
	string {

		return 'col-12';
	}

	public function
	Allow():
	bool {

		$CanTrafficLog = $this->App->User->HasAccessTypeOrAdmin(
			Atlantis\Key::AccessTrafficLog
		);

		return $CanTrafficLog;
	}

	public function
	Render():
	string {

		$this->FetchData();

		$Output = $this->App->Surface->GetArea(
			'atlantis/dashboard/infowidget/traffic',
			[ 'Element'=> $this ]
		);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	FetchData():
	void {

		$Now = new Common\Date('now', Immutable: TRUE);
		$Then = $Now->Modify('-24 hours');
		$Filters = [ 'Since'=> $Then->GetUnixtime() ];

		////////

		$this->Hits = Atlantis\Struct\TrafficRow::FindCount($Filters);

		$this->Visitors = Atlantis\Struct\TrafficRow::FindCount(array_merge(
			$Filters, [ 'Group'=> 'visitor' ]
		));

		$this->Pages = Atlantis\Struct\TrafficRow::FindCount(array_merge(
			$Filters, [ 'Group'=> 'page' ]
		));

		return;
	}

};
