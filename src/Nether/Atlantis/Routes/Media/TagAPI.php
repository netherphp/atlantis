<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;

class TagAPI
extends Atlantis\Routes\UploadAPI {

	#[Atlantis\Meta\RouteHandler('/api/tag/entity', Verb: 'GET')]
	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'GET')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	TagGet():
	void {

		$Tag = $this->FetchTagByField();

		$this->SetPayload($Tag->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/tag/entity', Verb: 'SEARCH')]
	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'SEARCH')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	TagSearch():
	void {

		($this->Data)
		->Query(Common\Filters\Text::Trimmed(...))
		->Type(Common\Filters\Misc::OneOfTheseNullable(...), [ 'site', 'tag' ])
		->ParentID(Common\Filters\Numbers::IntNullable(...));

		$TagTypes = [ 'site', 'tag' ];
		$Tags = new Common\Datastore;
		$Tag = NULL;
		$Result = NULL;

		////////

		if($this->User->HasAccessTypeOrAdmin('Tags.Admin', 1))
		$TagTypes[] = 'admin';

		if($this->Data->Type !== NULL)
		$TagTypes = $this->Data->Type;

		////////

		$Result = Atlantis\Tag\Entity::Find([
			'Type'     => $TagTypes,
			'ParentID' => $this->Data->ParentID,
			'NameLike' => $this->Data->Query,
			'Sort'     => 'tag-name-az',
			'Limit'    => 20
		]);

		foreach($Result as $Tag) {
			/** @var Atlantis\Tag\Entity $Tag */
			$Tags[] = $Tag->DescribeForPublicAPI();
		}

		$this->SetPayload([
			'Query' => $this->Data->Query,
			'Tags'  => $Tags,
			'Type'  => $TagTypes,
			'Total' => $Result->Total
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/tag/entity', Verb: 'POST')]
	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	TagPost():
	void {

		($this->Data)
		->Name(Common\Filters\Text::Trimmed(...));

		$Name = $this->Data->Name;
		$Alias = Common\Filters\Text::SlottableKey($Name);
		$Old = NULL;

		////////

		$Old = Atlantis\Tag\Entity::GetByField('Name', $Name);

		if($Old)
		$this->Quit(1, "A tag with this name ({$Name}) already exists.");

		$Old = Atlantis\Tag\Entity::GetByField('Alias', $Alias);

		if($Old)
		$this->Quit(2, "A tag with this alias ({$Alias}) already exists.");

		////////

		$Tag = Atlantis\Tag\Entity::Insert([
			'Name'  => $Name,
			'Alias' => $Alias
		]);

		$this
		->SetGoto("/tag/{$Tag->Alias}")
		->SetPayload($Tag->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/tag/entity', Verb: 'PATCH')]
	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'PATCH')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	TagPatch():
	void {

		$Tag = $this->FetchTagByField();
		$Tag->Update($Tag->Patch($this->Data));

		$this->SetPayload($Tag->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/tag/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	TagDelete():
	void {

		$Tag = $this->FetchTagbyField();
		$Tag->Drop();

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/tag/entity', Verb: 'ENABLE')]
	#[Atlantis\Meta\RouteHandler('/api/media/tag', Verb: 'ENABLE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	TagEnable():
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	FetchTagByField(string $Field='ID'):
	Atlantis\Tag\Entity {

		$ID = Common\Filters\Numbers::IntNullable($this->Data->Get($Field));

		if(!$ID)
		$this->Quit(1, 'no ID specified');

		////////

		$Tag = Atlantis\Tag\Entity::GetByID($ID);

		if(!$Tag)
		$this->Quit(2, 'tag not found');

		////////

		return $Tag;
	}

}
