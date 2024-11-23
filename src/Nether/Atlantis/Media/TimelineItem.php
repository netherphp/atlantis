<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

#[Common\Meta\Date('2024-11-22')]
#[Database\Meta\TableClass('TimelineItems', 'TLI')]
class TimelineItem
extends Atlantis\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Timelines', 'ID')]
	public int
	$TimelineID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntSmall(Unsigned: TRUE)]
	public int
	$SortVal;

	#[Database\Meta\TypeVarChar(Size: 128)]
	public ?string
	$Title;

	#[Database\Meta\TypeVarChar(Size: 32)]
	public ?string
	$Date;

	#[Database\Meta\TypeVarChar(Size: 256)]
	public ?string
	$URL;

	#[Database\Meta\TypeText]
	public ?string
	$Details;

	////////

	#[Database\Meta\TableJoin('TimelineID')]
	public Timeline
	$Timeline;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		parent::OnReady($Args);

		if($Args->InputHas('TLE_ID'))
		$this->Timeline = Timeline::FromPrefixedDataset($Args->Input);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetTitle():
	string {

		if($this->Title)
		return $this->Title;

		return sprintf('Item #%d', $this->ID);
	}

	////////////////
	////////////////

	public function
	GetEditURL():
	string {

		return sprintf('/dashboard/timelines/item-edit/%s', $this->UUID);
	}

	public function
	GetDeleteURL():
	string {

		return sprintf('/dashboard/timelines/item-delete/%s', $this->UUID);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input->Define([
			'TimelineID' => NULL,
			'Sort'       => 'sort-asc',
			'Order'      => NULL
		]);

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['TimelineID'] !== NULL) {
			$SQL->Where('TimelineID=:TimelineID');
		}

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['Order'] !== NULL) {
			if(is_array($Input['Order'])) {
				$SQL->Sort('FIELD(Main.ID, :Order)', $SQL::SortAsc);
			}
		}

		switch($Input['Sort']) {
			case 'sort-asc':
				$SQL->Sort('Main.SortVal', $SQL::SortAsc);
			break;
			case 'sort-dsc':
				$SQL->Sort('Main.SortVal', $SQL::SortDesc);
			break;
		};

		return;
	}

};
