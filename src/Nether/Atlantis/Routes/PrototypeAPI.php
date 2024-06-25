<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

use Exception;

################################################################################
################################################################################

class PrototypeAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/prototype/entity', Verb: 'TAGSGET')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityTagsGet():
	void {

		($this->Data)
		->EntityUUID(Common\Filters\Text::Trimmed(...))
		->Type(Common\Filters\Text::TrimmedNullable(...));

		$Result = Atlantis\Tag\EntityLink::Find([
			'EntityUUID' => $this->Data->EntityUUID,
			'Type'       => $this->Data->Type,
			'Sort'       => 'tag-name-az',
			'Limit'      => 20
		]);

		$Tags = [];
		$Link = NULL;

		foreach($Result as $Link) {
			/** @var Atlantis\Tag\EntityLink $Link */

			$Tags[] = [
				'ID'    => $Link->Tag->ID,
				'Alias' => $Link->Tag->Alias,
				'Name'  => $Link->Tag->Name
			];
		}

		$this->SetPayload([
			'Tags' => $Tags
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/prototype/entity', Verb: 'TAGSPATCH')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityTagsSet():
	void {

		($this->Data)
		->EntityType(Common\Filters\Text::Trimmed(...))
		->EntityUUID(Common\Filters\Text::Trimmed(...))
		->OnlyAdd(Common\Filters\Numbers::BoolType(...))
		->TagID(
			Common\Filters\Lists::CommaOfNullable(...),
			Common\Filters\Numbers::IntType(...)
		)
		->TagName(
			Common\Filters\Lists::CommaOfNullable(...),
			Common\Filters\Text::Trimmed(...)
		);

		////////

		$EType = $this->Data->EntityType;
		$EUUID = $this->Data->EntityUUID;
		$EInfo = NULL;

		$Entity = NULL;
		$TagsToHave = new Common\Datastore($this->Data->TagID);
		$TagsToMake = new Common\Datastore($this->Data->TagName);

		////////

		try {
			$EInfo = Atlantis\Tag\EntityLink::GetTypeEntityInfo($EType);
		}

		catch(Exception $Error) {
			$this->Quit(1, sprintf(
				'link type invalid: %s (%s)',
				$EType,
				$Error->GetMessage()
			));
		}

		////////

		$Entity = ($EInfo->EntityClass)::GetByUUID($EUUID);

		if(!$Entity)
		$this->Quit(2, 'entity not found');

		////////

		$Links = $Entity->GetTagLinks();

		$Existing = $Links->Map(
			fn(Atlantis\Tag\EntityLink $Link)
			=> $Link->TagID
		);

		$Existing = (
			$Links
			->Remap(
				fn(Atlantis\Tag\EntityLink $Link)
				=> $Link->TagID
			)
		);

		$Add = $TagsToHave->Distill(
			fn(int $TagID)
			=> $TagID && !$Existing->HasValue($TagID)
		);

		$Add->MergeRight(
			$TagsToMake
			->Map(function(string $Name) {
				$Tag = Atlantis\Tag\Entity::GetByField('Name', $Name);

				if(!$Tag)
				$Tag = Atlantis\Tag\Entity::Insert([ 'Name' => $Name ]);

				return $Tag->ID;
			})
			->Each(fn(int $TagID)=> $TagsToHave->Push($TagID))
			->Distill(fn(int $TagID)=> !$Existing->HasValue($TagID))
		);

		$Remove = $Existing->Distill(
			fn(int $TagID)
			=> !$TagsToHave->HasValue($TagID) && !$Add->HasValue($TagID)
		);

		////////

		$Add->Each(
			fn(int $TagID)
			=> ($EInfo->LinkClass)::Insert([ 'TagID'=> $TagID, 'EntityUUID'=> $Entity->UUID ])
		);

		if(!$this->Data->OnlyAdd)
		$Remove->Each(
			fn(int $TagID)
			=> ($EInfo->LinkClass)::DeleteByPair($TagID, $Entity->UUID)
		);

		////////

		$this->SetPayload([
			'Desire'   => $TagsToHave->GetData(),
			'Existing' => $Existing,
			'Add'      => $Add,
			'Remove'   => $Remove
		]);

		return;
	}

};
