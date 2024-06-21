<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

#[Database\Meta\TableClass('TrafficReports', 'TRPT')]
#[Database\Meta\InsertUpdate]
class TrafficReport
extends Atlantis\Prototype
implements
	Atlantis\Interfaces\ExtraDataInterface {

	#[Database\Meta\TypeChar(Size: 10)]
	#[Database\Meta\FieldIndex(Unique: TRUE)]
	public string
	$ReportDate;

	#[Database\Meta\TypeInt(Unsigned: TRUE)]
	public int
	$HitCount;

	#[Database\Meta\TypeInt(Unsigned: TRUE)]
	public int
	$VisitorCount;

	#[Database\Meta\TypeInt(Unsigned: TRUE)]
	public int
	$PageCount;

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS ExtraDataInterface ///////////////////////////////

	use
	Atlantis\Packages\ExtraData;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['Since'] ??= NULL;
		$Input['Until'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['Since'] !== NULL) {
			if(is_int($Input['Since'])) {
				$Input['Since'] = (
					Common\Date::FromTime($Input['Since'])
					->SetTimezone('UTC')
					->Get(Common\Values::DateFormatYMD)
				);
			}

			$SQL->Where('Main.ReportDate >= :Since');
		}

		if($Input['Until'] !== NULL) {
			if(is_int($Input['Until'])) {
				$Input['Until'] = (
					Common\Date::FromTime($Input['Until'])
					->SetTimezone('UTC')
					->Get(Common\Values::DateFormatYMD)
				);
			}

			$SQL->Where('Main.ReportDate <= :Until');
		}

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		$SQL->Sort('Main.ReportDate', $SQL::SortAsc);

		return;
	}

}

