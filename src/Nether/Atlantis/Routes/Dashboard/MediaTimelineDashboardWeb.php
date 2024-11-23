<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Avenue;

################################################################################
################################################################################

class MediaTimelineDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/timelines')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.Timelines.Manage')]
	public function
	TimelineIndex():
	void {

		$Timelines = Atlantis\Media\Timeline::Find([
			'sort' => 'title-az'
		]);

		($this->Surface)
		->Set('Page.Title', 'Timeline Manager')
		->PushInto('Page.Body.Classes', 'atl-page-dashboard')
		->Area('atlantis/dashboard/timelines/index', [
			'Timelines' => $Timelines
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/dashboard/timelines/:UUID:')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.Timelines.Manage')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	#[Avenue\Meta\ExtraDataArgs]
	public function
	TimelineView(string $UUID, Atlantis\Media\Timeline $Timeline):
	void {

		($this->Surface)
		->Set('Page.Title', sprintf('Timeline Edit: %s', $Timeline->GetTitle()))
		->PushInto('Page.Body.Classes', 'atl-page-dashboard')
		->Area('atlantis/dashboard/timelines/view', [
			'Timeline' => $Timeline
		]);

		return;
	}

	protected function
	TimelineViewWillAnswerRequest(string $UUID, Avenue\Struct\ExtraData $Data):
	int {

		$Timeline = Atlantis\Media\Timeline::GetByUUID($UUID);

		if(!$Timeline)
		return Avenue\Response::CodeNotFound;

		////////

		$Data->Set('Timeline', $Timeline);

		return Avenue\Response::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/dashboard/timelines/item-edit/:UUID:')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.Timelines.Manage')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	#[Avenue\Meta\ExtraDataArgs]
	public function
	ItemView(string $UUID, Atlantis\Media\TimelineItem $Item):
	void {

		($this->Surface)
		->Set('Page.Title', sprintf('Timeline Item Edit: %s', $Item->GetTitle()))
		->PushInto('Page.Body.Classes', 'atl-page-dashboard')
		->Area('atlantis/dashboard/timelines/item-edit', [
			'Timeline' => $Item->Timeline,
			'Item'     => $Item
		]);

		return;
	}

	protected function
	ItemViewWillAnswerRequest(string $UUID, Avenue\Struct\ExtraData $Data):
	int {

		$Item = Atlantis\Media\TimelineItem::GetByUUID($UUID);

		if(!$Item)
		return Avenue\Response::CodeNotFound;

		////////

		$Data->Set('Item', $Item);

		return Avenue\Response::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/dashboard/timelines/item-delete/:UUID:')]
	#[Atlantis\Meta\RouteAccessType('Nether.Atlantis.Timelines.Manage')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	#[Avenue\Meta\ExtraDataArgs]
	public function
	ItemDelete(string $UUID, Atlantis\Media\TimelineItem $Item):
	void {

		($this->Surface)
		->Set('Page.Title', sprintf('Timeline Item Delete: %s', $Item->GetTitle()))
		->PushInto('Page.Body.Classes', 'atl-page-dashboard')
		->Area('atlantis/dashboard/timelines/item-delete', [
			'Timeline' => $Item->Timeline,
			'Item'     => $Item
		]);

		return;
	}

	protected function
	ItemDeleteWillAnswerRequest(string $UUID, Avenue\Struct\ExtraData $Data):
	int {

		$Item = Atlantis\Media\TimelineItem::GetByUUID($UUID);

		if(!$Item)
		return Avenue\Response::CodeNotFound;

		////////

		$Data->Set('Item', $Item);

		return Avenue\Response::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

};

################################################################################
################################################################################
