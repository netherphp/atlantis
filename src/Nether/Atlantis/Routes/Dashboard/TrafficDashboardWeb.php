<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class TrafficDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/traffic/view')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	ViewGet():
	void {

		($this->Data)
		->When(Common\Filters\Text::Trimmed(...))
		->Path([
			Common\Filters\Text::PathableKey(...),
			Common\Filters\Text::StringNullable(...)
		]);

		$Filters = [
			'Since'     => NULL,
			'Before'    => NULL,
			'PathStart' => $this->Data->Path,
			'Group'     => 'path',
			'Sort'      => 'group-count-za',
			'Limit'     => 0
		];

		$Title = 'Traffic Report';
		$When = NULL;

		$Now = new Common\Date;
		$Today = NULL;
		$Next = NULL;
		$Prev = NULL;
		$Timeline = NULL;

		switch($this->Data->When) {
			case '24hr':
				$Title = 'Past 24 Hours';
				$Filters['Since'] = Common\Date::Unixtime('-24 hour');
				break;
			break;
			case '48hr':
				$Title = 'Past 48 Hours';
				$Filters['Since'] = Common\Date::Unixtime('-48 hour');
				break;
			break;
			case '1wk':
				$Title = 'Past Week';
				$Filters['Since'] = Common\Date::Unixtime('-1 week');
				break;
			break;
			case '1mo':
				$Title = 'Past Month';
				$Filters['Since'] = Common\Date::Unixtime('-1 month');
				break;
			break;
			default:
				if(preg_match('#\d{4}-\d{2}-\d{2}#', $this->Data->When)) {
					$When = Common\Date::FromDateString($this->Data->When);
					$Title = sprintf('Day of %s', $When->Get(Common\Values::DateFormatFancyDate));

					$Today = new Common\Date($When, TRUE);
					$Prev = $Today->Modify('-1 day');
					$Next = $Today->Modify('+24 hour');
					$Timeline = Atlantis\Struct\TrafficTimeline::FindAround($Today);

					$Filters['Since'] = $When->GetUnixtime();
					$Filters['Before'] = $When->Modify('+1 day')->GetUnixtime();
				}

				elseif(preg_match('#\d{4}-\d{2}#', $this->Data->When)) {
					$When = Common\Date::FromDateString("{$this->Data->When}-01");
					$Title = sprintf('Month of %s', $When->Get('F Y'));

					$Filters['Since'] = $When->GetUnixtime();
					$Filters['Before'] = $When->Modify('+1 month')->GetUnixtime();
				}

				elseif(preg_match('#\d{4}#', $this->Data->When)) {
					$When = Common\Date::FromDateString("{$this->Data->When}-01-01");
					$Title = sprintf('Year of %s', $When->Get('Y'));

					$Filters['Since'] = $When->GetUnixtime();
					$Filters['Before'] = $When->Modify('+1 year')->GetUnixtime();
				}

				else
				$Filters['Since'] = Common\Date::Unixtime('today');

				break;
			break;
		}

		////////

		$Rows = Atlantis\Struct\TrafficRow::Find($Filters);
		$Sources = Atlantis\Struct\TrafficRow::Find(array_merge($Filters, [ 'Group'=> 'from-domain', 'FromDomain'=> TRUE, 'Sort'=> 'group-count-za' ]));
		$Pages = $Rows->Count();
		$Hits = Atlantis\Struct\TrafficRow::FindCount(array_merge($Filters, [ 'Group'=> NULL, 'Sort'=> NULL ]));
		$Visitors = Atlantis\Struct\TrafficRow::FindCount(array_merge($Filters, [ 'Group'=> 'visitor' ]));

		$Sources->RemapKeys(function(mixed $K, mixed $V) {
			return [ $V->FromDomain => $V->GetGroupCount() ];
		});

		($this->App->Surface)
		->Set('Page.Title', "Traffic Report: {$Title} - Dashboard")
		->Wrap('atlantis/dashboard/traffic/view', [
			'When'     => $When,
			'Title'    => $Title,
			'Rows'     => $Rows,
			'Sources'  => $Sources,
			'Hits'     => $Hits,
			'Visitors' => $Visitors,
			'Pages'    => $Pages,
			'Path'     => $Filters['PathStart'],

			'Now'      => $Now,    // always the literal now.
			'Today'    => $Today,  // the "now" of the current view.
			'Next'     => $Next,   // relative to current view
			'Prev'     => $Prev,   // relative to current view,
			'Timeline' => $Timeline
		]);

		return;
	}

}
