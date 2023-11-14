<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class RelatedLinkAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/media/link')]
	public function
	EntityGet():
	void {

		$Ent = $this->FetchEntityByField();

		$this->SetPayload($Ent->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/link', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin()]
	public function
	EntityPost():
	void {

		($this->Data)
		->Date(Common\Filters\Text::TrimmedNullable(...))
		->Title(Common\Filters\Text::TrimmedNullable(...))
		->URL(Common\Filters\Text::TrimmedNullable(...))
		->ParentType(Common\Filters\Text::TrimmedNullable(...))
		->ParentUUID(Common\Filters\Text::TrimmedNullable(...));

		////////

		$Now = new Common\Date('today');
		$Date = $this->Data->Date;
		$Title = $this->Data->Title;
		$URL = $this->Data->URL;

		$ParentType = $this->Data->ParentType;
		$ParentUUID = $this->Data->ParentUUID;
		$ParentClass = NULL;

		$ChildType = NULL;
		$ChildClass = Atlantis\Media\RelatedLink::class;

		////////

		if(!$Title)
		$this->Quit(1, 'No Title specified');

		if(!$URL)
		$this->Quit(2, 'No URL specified');

		if($Date)
		$Date = Common\Date::FromDateString($Date);
		else
		$Date = $Now;

		////////

		$Ent = Atlantis\Media\RelatedLink::Insert([
			'TimeCreated' => $Date->GetUnixtime(),
			'Title'       => $Title,
			'URL'         => $URL
		]);

		////////

		if($ParentType && $ParentUUID) {
			$ParentClass = Atlantis\Struct\EntityRelationship::TypeClass($ParentType);
			$ChildType = Atlantis\Struct\EntityRelationship::ClassType($ChildClass);

			if(!$ParentClass)
			$this->Quit(3, sprintf(
				'No registered ERI class for %s',
				$ParentType
			));

			if(!$ChildType)
			$this->Quit(4, sprintf(
				'No registered ERI type for %s',
				$ChildClass
			));

			Atlantis\Struct\EntityRelationship::Insert([
				'ParentType' => $ParentType,
				'ParentUUID' => $ParentUUID,
				'ChildType'  => $ChildType,
				'ChildUUID'  => $Ent->UUID
			]);
		}

		////////

		$this->SetPayload($Ent->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/link', Verb: 'PATCH')]
	#[Atlantis\Meta\RouteAccessTypeAdmin()]
	public function
	EntityPatch():
	void {

		$Ent = $this->FetchEntityByField();
		$Ent->Update($Ent->Patch($this->Data));

		$this->SetPayload($Ent->DescribeForPublicAPI());
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/link', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin()]
	public function
	EntityDelete():
	void {

		$Ent = $this->FetchEntityByField();

		$Ent->Drop();
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	FetchEntityByField(string $Field='ID'):
	Atlantis\Media\RelatedLink {

		$ID = Common\Filters\Numbers::IntNullable($this->Data->Get($Field));

		if(!$ID)
		$this->Quit(1, "no {$Field} specified");

		////////

		$Tag = Atlantis\Media\RelatedLink::GetByID($ID);

		if(!$Tag)
		$this->Quit(2, 'related link not found');

		////////

		return $Tag;
	}

}
