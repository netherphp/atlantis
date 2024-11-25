<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Common;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class MediaTimelineDashboardAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/media/timeline', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.Timelines.Manage')]
	public function
	EntityPost():
	void {

		($this->Data)
		->FilterPush('Title', Common\Filters\Text::TrimmedNullable(...));

		////////

		$Title = $this->Data->Get('Title');

		if(!$Title)
		$this->Quit(1, 'Title is required');

		////////

		$Timeline = Atlantis\Media\Timeline::Insert([
			'Title' => $Title
		]);

		$this->SetGoto($this->App->RewriteURL($Timeline->GetEditURL()));
		$this->SetPayload($Timeline->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/timeline', Verb: 'SORT')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.Timelines.Manage')]
	public function
	EntitySorting():
	void {

		// expect a list of integers.
		// fetch the faqs in that specific order.
		// re-iterate the sortval down that specific list.

		($this->Data)
		->FilterPush('ID', Common\Filters\Numbers::IntNullable(...))
		->FilterPush('Order', [
			[ Common\Filters\Text::Trimmed(...) ],
			[ (fn(Common\Struct\DatafilterItem $I)=> explode(',', $I->Value)) ],
			[ Common\Filters\Lists::ArrayOf(...), Common\Filters\Numbers::IntType(...) ]
		]);

		////////

		$TimelineID = $this->Data->Get('ID');
		$Order = $this->Data->Get('Order');

		if(!$Order || !is_array($Order))
		$this->Quit(1, 'Invalid Inputs.');

		////////

		$Iter = 1;

		$Ents = Atlantis\Media\TimelineItem::Find([
			'TimelineID' => $TimelineID,
			'Order'      => $Order,
			'Limit'      => 0
		]);

		var_dump($Order);

		$Ents->Each(function(Atlantis\Media\TimelineItem $E) use(&$Iter) {
			var_dump($Iter);
			$E->Update([ 'SortVal'=> $Iter ]);
			$Iter += 1;
			return;
		});

		////////

		$this->SetPayload([
			'ID'      => $TimelineID,
			'Order'   => $Order,
			'Query'   => $Ents->Map(fn($I)=> $I->ID)->Export()
		]);

		return;
	}


	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/media/timeline/item', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.Timelines.Manage')]
	public function
	ItemPost():
	void {

		($this->Data)
		->FilterPush('TimelineID', Common\Filters\Numbers::IntType(...))
		->FilterPush('Title', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Date', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('URL', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Details', Common\Filters\Text::TrimmedNullable(...));

		$Dataset = $this->Data->Pick(
			'TimelineID', 'Title', 'Date', 'URL', 'Details'
		);

		////////

		if(!$Dataset['TimelineID'])
		$this->Quit(1, 'TimelineID is required');

		////////

		$Timeline = Atlantis\Media\Timeline::GetByID($Dataset['TimelineID']);

		if(!$Timeline)
		$this->Quit(2, sprintf('Timeline %d not found', $Dataset['TimelineID']));

		////////

		$Dataset['SortVal'] = $Timeline->FetchSortValMax();

		$Item = Atlantis\Media\TimelineItem::Insert($Dataset);

		$this->SetPayload($Item->DescribeForPublicAPI());
		$this->SetGoto($Timeline->GetEditURL());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/timeline/item', Verb: 'PATCH')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.Timelines.Manage')]
	public function
	ItemPatch():
	void {

		($this->Data)
		->FilterPush('ID', Common\Filters\Numbers::IntType(...))
		->FilterPush('Title', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Date', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('URL', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Details', Common\Filters\Text::TrimmedNullable(...));

		$Dataset = $this->Data->Pick(
			'ID', 'Title', 'Date', 'URL', 'Details'
		);

		////////

		if(!$Dataset['ID'])
		$this->Quit(1, 'ID is required');

		$Item = Atlantis\Media\TimelineItem::GetByID($Dataset['ID']);

		if(!$Item)
		$this->Quit(2, sprintf('Item %d not found', $Dataset['ID']));

		////////

		$Item->Update([
			'Title'   => $Dataset['Title'],
			'Date'    => $Dataset['Date'],
			'URL'     => $Dataset['URL'],
			'Details' => $Dataset['Details']
		]);

		$this->SetPayload($Item->DescribeForPublicAPI());
		$this->SetGoto($Item->Timeline->GetEditURL());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/timeline/item', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.Timelines.Manage')]
	public function
	ItemDelete():
	void {

		($this->Data)
		->FilterPush('ID', Common\Filters\Numbers::IntType(...));

		$ID = $this->Data->Get('ID');

		////////

		if(!$ID)
		$this->Quit(1, 'ID is required');

		$Item = Atlantis\Media\TimelineItem::GetByID($ID);

		if(!$Item)
		$this->Quit(2, sprintf('Item %d not found', $ID));

		////////

		$Timeline = $Item->Timeline;
		$Item->Drop();

		$this->SetGoto($Timeline->GetEditURL());

		return;
	}

};

################################################################################
################################################################################
