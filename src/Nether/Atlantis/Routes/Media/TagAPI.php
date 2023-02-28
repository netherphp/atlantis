<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Storage;

use Exception;

class TagAPI
extends Atlantis\Routes\UploadAPI {

	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'GET')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	TagGet():
	void {

		($this->Data)
		->ID(Common\Datafilters::TypeIntNullable(...));

		////////

		if(!$this->Data->ID)
		$this->Quit(1, 'no ID specified');

		$Tag = Atlantis\Media\Tag::GetByID($this->Data->ID);

		if(!$Tag)
		$this->Quit(2, 'tag not found');

		////////

		$this->SetPayload([
			'ID'    => $Tag->ID,
			'Type'  => $Tag->Type,
			'Alias' => $Tag->Alias,
			'Name'  => $Tag->Name
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'SEARCH')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	TagSearch():
	void {

		($this->Data)
		->Query(Common\Datafilters::TrimmedText(...));

		$Result = Atlantis\Media\Tag::Find([
			'NameLike' => $this->Data->Query,
			'Sort'     => 'tag-name-az',
			'Limit'    => 20
		]);

		$Tags = new Common\Datastore;
		$Tag = NULL;

		foreach($Result as $Tag) {
			/** @var Atlantis\Media\Tag $Tag */

			$Tags[] = [
				'ID'    => $Tag->ID,
				'Alias' => $Tag->Alias,
				'Name'  => $Tag->Name
			];
		}

		$this->SetPayload([
			'Query' => $this->Data->Query,
			'Tags'  => $Tags
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	TagPost():
	void {

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'PATCH')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	TagPatch():
	void {

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	TagDelete():
	void {

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'ENABLE')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	TagEnable():
	void {

		return;
	}

}
