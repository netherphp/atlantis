<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Traffic;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

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

		////////

		$InputTitle = $this->Data->Get('Title') ?? 'Traffic Overview';
		$InputPath = $this->Data->Get('Path');
		$InputQuery = $this->Data->Get('Query');

		$RangeToday = $this->GetTimeframeToday();
		$RangeWeek = $this->GetTimeframeDays(7);
		$RangeMonth = $this->GetTimeframeDays(30);
		$RangeAll = NULL;

		////////

		$ResultToday = $this->GetResultsTimeframe($RangeToday);
		$ResultWeek = $this->GetResultsTimeframe($RangeWeek);
		$ResultMonth = $this->GetResultsTimeframe($RangeMonth);
		$ResultAll = $this->GetResultsTimeframe($RangeAll);

		////////

		($this->Surface)
		->Set('Page.Title', 'Traffic Overview')
		->Area('atlantis/dashboard/traffic/overview', [
			'Title'        => $InputTitle,
			'Path'         => $InputPath,
			'Query'        => $InputQuery,
			'TotalToday'   => $ResultToday->Total(),
			'TotalWeek'    => $ResultWeek->Total(),
			'TotalMonth'   => $ResultMonth->Total(),
			'TotalAllTime' => $ResultAll->Total()
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GetTimeframeToday():
	Common\Units\Timeframe {

		return Common\Units\Timeframe::Today();
	}

	protected function
	GetTimeframeDays(int $Days):
	Common\Units\Timeframe {

		$Range = Common\Units\Timeframe::Today();
		$Range->GetStart()->Modify(sprintf('-%d days', $Days));

		return $Range;
	}

	protected function
	GetResultsTimeframe(?Common\Units\Timeframe $Range):
	Database\ResultSet {

		$Results = Atlantis\Struct\TrafficRow::Find([
			'Timeframe' => $Range,
			'Limit'     => 1
		]);

		return $Results;
	}

};
