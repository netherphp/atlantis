<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Traffic;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class TrafficAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/traffic/v1/query')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleTrafficQuery():
	void {

		// error codes:
		// 1 - no date specified

		($this->Data)
		->FilterPush('Path', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Query', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Timezone', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Date', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('DateStart', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('DateStop', Common\Filters\Text::TrimmedNullable(...));

		$DateSingle = $this->Data->Get('Date');
		$DateStart = $this->Data->Get('DateStart');
		$DateStop = $this->Data->Get('DateStop');
		$DateZone = $this->Data->Get('Timezone') ?? 'GMT';

		$InputPath = $this->Data->Get('Path');
		$InputQuery = $this->Data->Get('Query');

		////////

		if(!$DateSingle && !$DateStart && !$DateStop)
		$this->Quit(1, 'no Date specified');

		if($DateSingle) {
			$DateStart = $DateSingle;
			$DateStop = $DateSingle;
		}

		$Timeframe = match(TRUE) {
			($DateSingle === 'all')
			=> NULL,

			($DateStart && $DateStop)
			=> new Common\Units\Timeframe(
				Start: sprintf('%s 00:00:00 %s', $DateStart, $DateZone),
				Stop: sprintf('%s 23:59:59 %s', $DateStart, $DateZone)
			),

			default
			=> Common\Units\Timeframe::Today($DateZone)
		};

		$Results = Atlantis\Struct\TrafficRow::Find([
			'Path'      => $InputPath,
			'Query'     => $InputQuery,
			'Timeframe' => $Timeframe,
			'Limit'     => 1
		]);

		////////

		$this->SetPayload([
			'Path'      => $InputPath,
			'Query'     => $InputQuery,
			'DateStart' => $DateStart,
			'DateStop'  => $DateStop,
			'Timezone'  => $DateZone,
			'Timeframe' => [
				'Start' => $Timeframe ? $Timeframe->GetStartFormat(Common\Values::DateFormatYMDT24VO) : NULL,
				'Stop'  => $Timeframe ? $Timeframe->GetStopFormat(Common\Values::DateFormatYMDT24VO) : NULL
			],
			'Total' => $Results->Total()
		]);

		return;
	}

};
