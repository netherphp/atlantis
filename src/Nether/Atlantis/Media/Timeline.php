<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

#[Common\Meta\Date('2024-11-22')]
#[Database\Meta\TableClass('Timelines', 'TLE')]
class Timeline
extends Atlantis\Prototype {

	const
	EntType = 'Timeline.Entity';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeVarChar(Size: 128)]
	public string
	$Title;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntSmall(Unsigned: TRUE)]
	public int
	$CountItems;

	////////

	protected Common\Datastore
	$Items;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	FetchItems():
	Database\ResultSet {

		$Rows = TimelineItem::Find(TimelineItemFinder::New(
			TimelineID: $this->ID,
			Sort: 'sort-asc'
		));

		return $Rows;
	}

	public function
	FetchSortValMax():
	int {

		return static::QuerySortValueMax($this->ID);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetItems():
	Common\Datastore {

		if(!isset($this->Items))
		$this->Items = $this->FetchItems();

		return $this->Items->Copy();
	}

	public function
	GetTitle():
	string {

		if($this->Title)
		return $this->Title;

		return sprintf('Timeline #%d', $this->ID);
	}

	////////////////
	////////////////

	public function
	GetEditURL():
	string {

		return sprintf(
			'/dashboard/timelines/%s',
			$this->UUID
		);
	}

	public function
	GetAddItemURL():
	string {

		return '';
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendSorts($SQL, $Input);

		switch($Input['Sort']) {
			case 'title-az':
				$SQL->Sort('Main.Title', $SQL::SortAsc);
			break;
			case 'title-za':
				$SQL->Sort('Main.Title', $SQL::SortDesc);
			break;
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	QuerySortValueMax(int $TimelineID):
	int {

		$DBM = new Database\Manager;
		$SQL = $DBM->NewVerse(static::$DBA);
		$Table = TimelineItem::GetTableInfo();

		$SQL->Select("{$Table->Name} Main");
		$SQL->Fields('MAX(Main.SortVal) AS SortValMax');
		$SQL->Where('Main.ID=:TimelineID');
		//$SQL->Group('Main.ID');

		$Result = $SQL->Query([ ':TimelineID'=> $TimelineID ]);
		$Row = $Result->Next();

		return $Row->SortValMax ?? 0;
	}

};
