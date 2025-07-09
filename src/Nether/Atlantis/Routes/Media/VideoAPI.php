<?php

namespace Nether\Atlantis\Routes\Media;

use GuzzleHttp;
use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

class VideoAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/video/entity')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	VideoGet():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...))
		->UUID(Common\Filters\Text::UUID(...));

		////////

		$Video = match(TRUE) {
			(!!$this->Data->UUID)
			=> Atlantis\Media\VideoThirdParty::GetByUUID($this->Data->UUID),

			default
			=> Atlantis\Media\VideoThirdParty::GetByID($this->Data->ID)
		};

		if(!$Video)
		$this->Quit(1, 'video not found');

		////////

		$this->SetPayload($Video->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/video/entity', Verb: 'SEARCH')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	VideoSearch():
	void {

		($this->Data)
		->Q(Common\Filters\Text::TrimmedNullable(...))
		->SearchTitle(Common\Filters\Numbers::BoolType(...))
		->SearchURL(Common\Filters\Numbers::BoolType(...))
		->Page(Common\Filters\Numbers::Page(...));

		////////

		$Filters = [
			'Search'      => $this->Data->Q,
			'SearchTitle' => $this->Data->SearchTitle,
			'SearchURL'   => $this->Data->SearchURL,
			'Limit'       => 10,
			'Page'        => $this->Data->Page
		];

		if($this->Data->Q)
		$Results = Atlantis\Media\VideoThirdParty::Find($Filters);
		else
		$Results = new Database\ResultSet([ ]);

		////////

		$this->SetPayload([
			'Filters' => $Filters,
			'Page'    => $Results->Page,
			'Total'   => $Results->Total,
			'Results' => $Results->Map(
				fn(Atlantis\Media\VideoThirdParty $V)
				=> $V->DescribeForPublicAPI()
			)->GetData()
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/video/entity', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	VideoPost():
	void {

		($this->Data)
		->URL(Common\Filters\Text::TrimmedNullable(...))
		->Title(Common\Filters\Text::TrimmedNullable(...))
		->DatePosted(Common\Filters\Text::TrimmedNullable(...))
		->OtherType(Common\Filters\Text::TrimmedNullable(...))
		->OtherUUID(Common\Filters\Text::TrimmedNullable(...));

		////////

		$URL = $this->Data->URL;
		$Title = $this->Data->Title;
		$TimePosted = (
			Common\Date::FromDateString($this->Data->DatePosted ?: 'now')
			->GetUnixtime()
		);

		$ShouldRelateOther = (TRUE
			&& $this->Data->OtherType
			&& $this->Data->OtherUUID
		);

		if(!$URL)
		$this->Quit(1, 'MissingURL');

		if(!$Title)
		$this->Quit(2, 'MissingTitle');

		////////

		// prevent dups.

		$Video = Atlantis\Media\VideoThirdParty::GetByField('URL', $URL);

		if($Video) {
			if($ShouldRelateOther)
			Atlantis\Struct\EntityRelationship::InsertByPair(
				$this->Data->OtherType,
				$this->Data->OtherUUID,
				$Video::EntType,
				$Video->UUID
			);

			$this->SetPayload($Video->DescribeForPublicAPI());

			return;
		}

		////////

		$Video = Atlantis\Media\VideoThirdParty::Insert([
			'URL'        => $URL,
			'Title'      => $Title ?: '',
			'TimePosted' => $TimePosted
		]);

		if($ShouldRelateOther)
		Atlantis\Struct\EntityRelationship::InsertByPair(
			$this->Data->OtherType,
			$this->Data->OtherUUID,
			$Video::EntType,
			$Video->UUID
		);

		$this->SetPayload($Video->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/video/entity', Verb: 'RELATE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	VideoRelate():
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/media/video-tp', Verb: 'GET')]
	#[Atlantis\Meta\RouteHandler('/api/video-tp/entity', Verb: 'GET')]
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
	#[Atlantis\Meta\RouteHandler('/api/video-tp/entity', Verb: 'POST')]
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

		////////

		$Old = Atlantis\Media\VideoThirdParty::Find([
			'URL'   => $this->Data->URL,
			'Limit' => 1
		]);

		if($Old->Count() > 0) {
			$Vid = $Old[0];
			goto Tagging;
		}

		////////

		$RemoteInfo = $this->TryToGetInfo($this->Data->URL);

		////////

		$Title = $this->Data->Title;

		if(!$Title && isset($RemoteInfo['Title']))
		$Title = $RemoteInfo['Title'];

		////////

		$DatePosted = $this->Data->DatePosted;

		if(!$DatePosted && isset($RemoteInfo['Date']))
		$DatePosted = $RemoteInfo['Date'];

		////////

		Insertion:
		$Vid = Atlantis\Media\VideoThirdParty::Insert([
			'URL'        => $this->Data->URL,
			'Title'      => $Title,
			'TimePosted' => (Common\Date::FromDateString($DatePosted))->GetUnixtime()
		]);

		Tagging:
		if(is_iterable($this->Data->TagID))
		foreach($this->Data->TagID as $TagID) {
			Atlantis\Media\VideoThirdPartyTagLink::InsertByPair(
				$TagID,
				$Vid->UUID
			);
		}

		Relation:
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
	#[Atlantis\Meta\RouteHandler('/api/video-tp/entity', Verb: 'PATCH')]
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

		if($this->Data->Exists('Title')) {
			if($Vid->Profile)
			$Vid->Profile->Update($Vid->Profile->Patch([
				'Title'=> $this->Data->Title
			]));
		}

		$this->SetPayload($Vid->DescribeForPublicAPI());
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/video-tp', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteHandler('/api/video-tp/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	VideoThirdPartyDelete():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...))
		->ParentUUID(Common\Filters\Text::TrimmedNullable(...));

		////////

		$Video = Atlantis\Media\VideoThirdParty::GetByID($this->Data->ID);

		if(!$Video)
		return;

		////////

		if($this->Data->ParentUUID) {
			Atlantis\Struct\EntityRelationship::DeleteByPair(
				$this->Data->ParentUUID,
				$Video->UUID
			);
		}

		else {
			$Video->Drop();
		}

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/video-tp', Verb: 'UNLINK')]
	#[Atlantis\Meta\RouteHandler('/api/video-tp/entity', Verb: 'UNLINK')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	VideoThirdPartyUnlink():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...))
		->ParentUUID(Common\Filters\Text::TrimmedNullable(...));

		////////

		$Video = Atlantis\Media\VideoThirdParty::GetByID($this->Data->ID);

		if(!$Video)
		return;

		////////

		if($this->Data->ParentUUID) {
			Atlantis\Struct\EntityRelationship::DeleteByPair(
				$this->Data->ParentUUID,
				$Video->UUID
			);
		}

		////////

		// if there are no more links it is likely they want to delete the
		// video completely.

		$Links = Atlantis\Struct\EntityRelationship::Find([
			'EntityUUID' => $Video->UUID
		]);

		if($Links->Count() !== 0)
		$this->Quit(4321, 'This video is still attached to some profiles though.');

		////////

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
