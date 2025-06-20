<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Traffic;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class TrafficDashboard
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/traffic/overview')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleOverview():
	void {

		($this->Data)
		->FilterPush('Title', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Path', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Query', Common\Filters\Text::TrimmedNullable(...));

		$RangeToday = Common\Units\Timeframe::Today();

		$RangeWeek = Common\Units\Timeframe::Today();
		$RangeWeek->GetStart()->Modify('-7 days');

		$RangeMonth = Common\Units\Timeframe::Today();
		$RangeMonth->GetStart()->Modify('-30 days');

		$ResultToday = Atlantis\Struct\TrafficRow::Find([
			'Timeframe' => $RangeToday,
			'Limit'     => 1
		]);

		$ResultWeek = Atlantis\Struct\TrafficRow::Find([
			'Timeframe' => $RangeWeek,
			'Limit'     => 1
		]);

		$ResultMonth = Atlantis\Struct\TrafficRow::Find([
			'Timeframe' => $RangeMonth,
			'Limit'     => 1
		]);

		$ResultAll = Atlantis\Struct\TrafficRow::Find([
			'Timeframe' => NULL,
			'Limit'     => 1
		]);

		($this->Surface)
		->Set('Page.Title', 'Traffic Overview')
		->Area('atlantis/dashboard/traffic/overview', [
			'Title'        => $this->Data->Get('Title'),
			'Path'         => $this->Data->Get('Path'),
			'Query'        => $this->Data->Get('Query'),
			'TotalToday'   => $ResultToday->Total(),
			'TotalWeek'    => $ResultWeek->Total(),
			'TotalMonth'   => $ResultMonth->Total(),
			'TotalAllTime' => $ResultAll->Total()
		]);

		return;
	}

};
