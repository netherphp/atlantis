<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Avenue;

class VideoDashboard
extends Atlantis\ProtectedWeb {

	#[Avenue\Meta\RouteHandler('/dashboard/media/videos-tp')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	Index():
	void {

		($this->Data)
		->Q(Common\Filters\Text::TrimmedNullable(...))
		->Page(Common\Filters\Numbers::Page(...))
		->Untagged(Common\Filters\Numbers::BoolNullable(...));

		$Title = 'Videos (Third Party)';
		$Area = 'atlantis/dashboard/media/video-tp/index';
		$Trail = Common\Datastore::FromArray([
			'Videos' => '/dashboard/media/videos-tp'
		]);

		$Filters = new Common\Datastore([
			'Search'   => $this->Data->Q,
			'Page'     => $this->Data->Page,
			'Untagged' => $this->Data->Untagged
		]);

		$Videos = Atlantis\Media\VideoThirdParty::Find($Filters);
		$Videos->Each(Atlantis\Prototype::TagCachePrime(...));

		$Searched = (FALSE
			|| $this->Data->Q
		);

		////////

		($this->Surface)
		->Set('Page.Title', $Title)
		->Wrap($Area, [
			'Trail'    => $Trail,
			'Searched' => $Searched,
			'Videos'   => $Videos,
			'Filters'  => $Filters
		]);

		return;
	}

}
