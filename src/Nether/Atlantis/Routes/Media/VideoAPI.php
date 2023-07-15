<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;

class VideoAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/media/video-tp', Verb: 'GET')]
	public function
	VideoThirdPartyGet():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		////////

		$Vid = Atlantis\Media\VideoThirdParty::GetByID($this->Data->ID);

		if(!$Vid)
		$this->Quit(1, "video {$this->Data->ID} not found");

		////////

		$this->SetPayload($Vid->DescribeForPublicAPI());
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/video-tp', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	VideoThirdPartyPost():
	void {

		($this->Data)
		->ParentUUID(Common\Filters\Text::UUID(...))
		->URL(Common\Filters\Text::Trimmed(...))
		->Title(Common\Filters\Text::TrimmedNullable(...))
		->DatePosted(Common\Filters\Text::TrimmedNullable(...));

		if(!$this->Data->URL)
		$this->Quit(1, 'no URL specified');

		////////

		$DatePosted = match(TRUE) {
			$this->Data->DatePosted !== NULL
			=> Common\Date::FromDateSTring($this->Data->DatePosted),

			default
			=> new Common\Date
		};

		$Vid = Atlantis\Media\VideoThirdParty::Insert([
			'ParentUUID' => $this->Data->ParentUUID,
			'URL'        => $this->Data->URL,
			'Title'      => $this->Data->Title,
			'TimePosted' => $DatePosted->GetUnixtime()
		]);

		$this->SetPayload($Vid->DescribeForPublicAPI());
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/video-tp', Verb: 'PATCH')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	VideoThirdPartyPatch():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		////////

		$Vid = Atlantis\Media\VideoThirdParty::GetByID($this->Data->ID);

		if(!$Vid)
		$this->Quit(1, "video {$this->Data->ID} not found");

		////////

		$Vid->Update($Vid->Patch($this->Data));

		$this->SetPayload($Vid->DescribeForPublicAPI());
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/video-tp', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	VideoThirdPartyDelete():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		////////

		$Video = Atlantis\Media\VideoThirdParty::GetByID($this->Data->ID);

		if($Video)
		$Video->Drop();

		return;
	}

}
