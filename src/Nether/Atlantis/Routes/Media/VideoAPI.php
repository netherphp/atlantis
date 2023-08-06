<?php

namespace Nether\Atlantis\Routes\Media;

use GuzzleHttp;
use Nether\Atlantis;
use Nether\Common;

use Exception;

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
		->URL(Common\Filters\Text::Trimmed(...))
		->Title(Common\Filters\Text::TrimmedNullable(...))
		->DatePosted(Common\Filters\Text::TrimmedNullable(...))
		->TagID(
			Common\Filters\Lists::ArrayOfNullable(...),
			Common\Filters\Numbers::IntType(...)
		)
		->ParentUUID(
			Common\Filters\Lists::ArrayOfNullable(...),
			Common\Filters\Text::UUID(...)
		)
		->ParentType(Common\Filters\Text::TrimmedNullable(...));

		$Title = NULL;
		$DatePosted = NULL;
		$Vid = NULL;
		$TagID = NULL;
		$ParentUUID = NULL;

		////////

		if(!$this->Data->URL)
		$this->Quit(1, 'no URL specified');

		$RemoteInfo = $this->TryToGetInfo($this->Data->URL);

		////////

		$Title = $this->Data->Title;

		if(!$Title && isset($RemoteInfo['Title']))
		$Title = $RemoteInfo['Title'];

		////////

		$DatePosted = $this->Data->DatePosted;

		if(!$DatePosted && isset($RemoteInfo['Date']))
		$DatePosted = $RemoteInfo['Date'];

		$Vid = Atlantis\Media\VideoThirdParty::Insert([
			'URL'        => $this->Data->URL,
			'Title'      => $Title,
			'TimePosted' => (Common\Date::FromDateString($DatePosted))->GetUnixtime()
		]);

		////////

		if(is_iterable($this->Data->TagID))
		foreach($this->Data->TagID as $TagID) {
			Atlantis\Media\VideoThirdPartyTagLink::InsertByPair(
				$TagID,
				$Vid->UUID
			);
		}

		if(is_iterable($this->Data->ParentUUID) && $this->Data->ParentType)
		foreach($this->Data->ParentUUID as $ParentUUID) {
			Atlantis\Struct\EntityRelationship::Insert([
				'ParentType'  => $this->Data->ParentType,
				'ParentUUID'  => $ParentUUID,
				'ChildType'   => 'Media.Video.ThirdParty',
				'ChildUUID'   => $Vid->UUID
			]);
		}

		////////

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	TryToGetInfo(string $URL):
	?array {

		$Output = NULL;
		$Client = NULL;
		$Result = NULL;

		////////

		$Client = new GuzzleHttp\Client([
			'verify'  => FALSE,
			'headers' => [ 'user-agent' => Atlantis\Library::Get(Atlantis\Key::ConfUserAgent) ],
			'timeout' => 4
		]);

		// squelch failures to fetch.

		try { $Result = $Client->Request('GET', $URL); }
		catch(Exception $Err) { return NULL; }

		// squelch different errors to fetch.

		if(!$Result || $Result->GetStatusCode() !== 200)
		return NULL;

		// squelch failures to parse.

		$HTML = $Result->GetBody();
		$HTML = $HTML->GetContents();

		if(!$HTML)
		return NULL;

		////////

		$Doc = html5qp($HTML);

		$El = $Doc->Find('meta[property="og:title"]');
		$Title = html_entity_decode($El->Attr('content') ?: '');

		$El = $Doc->Find('meta[itemprop="datePublished"]');
		$Date = html_entity_decode($El->Attr('content') ?: '');

		////////

		return [
			'Title' => $Title,
			'Date'  => $Date
		];
	}

}
