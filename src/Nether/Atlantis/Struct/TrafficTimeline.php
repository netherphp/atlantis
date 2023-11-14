<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

class TrafficTimeline
extends Common\Prototype {

	public Common\Struct\RangeInt
	$Hits;

	public Common\Struct\RangeInt
	$Visitors;

	public Common\Struct\RangeInt
	$Pages;

	#[Common\Meta\PropertyFactory('FromArray', 'Reports')]
	public array|Common\Datastore
	$Reports;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->Hits = $this->GetHitRange();
		$this->Visitors = $this->GetVisitorRange();
		$this->Pages = $this->GetPageRange();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetHitRange():
	Common\Struct\RangeInt {

		$R = new Common\Struct\RangeInt(
			$this->Reports->Accumulate(
				PHP_INT_MAX,
				fn(int $C, TrafficReport $Rep)
				=> $Rep->HitCount < $C ? $Rep->HitCount : $C
			),
			$this->Reports->Accumulate(
				PHP_INT_MIN,
				fn(int $C, TrafficReport $Rep)
				=> $Rep->HitCount > $C ? $Rep->HitCount : $C
			)
		);

		return $R;
	}

	public function
	GetVisitorRange():
	Common\Struct\RangeInt {

		$R = new Common\Struct\RangeInt(
			$this->Reports->Accumulate(
				PHP_INT_MAX,
				fn(int $C, TrafficReport $Rep)
				=> $Rep->VisitorCount < $C ? $Rep->VisitorCount : $C
			),
			$this->Reports->Accumulate(
				PHP_INT_MIN,
				fn(int $C, TrafficReport $Rep)
				=> $Rep->VisitorCount > $C ? $Rep->VisitorCount : $C
			)
		);

		return $R;
	}

	public function
	GetPageRange():
	Common\Struct\RangeInt {

		$R = new Common\Struct\RangeInt(
			$this->Reports->Accumulate(
				PHP_INT_MAX,
				fn(int $C, TrafficReport $Rep)
				=> $Rep->PageCount < $C ? $Rep->PageCount : $C
			),
			$this->Reports->Accumulate(
				PHP_INT_MIN,
				fn(int $C, TrafficReport $Rep)
				=> $Rep->PageCount > $C ? $Rep->PageCount : $C
			)
		);

		return $R;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FindAround(Common\Date $Date, int $Days=7):
	static {

		$Today = new Common\Date('today', TRUE);
		$Today->SetTimezone('UTC');

		$When = new Common\Date($Date, TRUE);
		$When->SetTimezone('UTC');

		$Since = $When->Modify(sprintf('-%d days', $Days));
		$Until = $When->Modify(sprintf('+%d days', $Days));

		if($Today->IsAfter($Until)) {
			$Diff = new Common\Units\Timeframe($Today, $Until);
			$Since = $Since->Modify(sprintf(
				'-%d day',
				(int)$Diff->Get([ '%d' ])
			));
		}

		$Result = TrafficReport::Find([
			'Since' => $Since,
			'Until' => $Until
		]);

		$Output = new static([
			'Reports' => $Result->GetData()
		]);

		return $Output;
	}

};
