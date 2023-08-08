<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Storage;

use Exception;
use Imagick;
use GdImage;

class EntityAPI
extends Atlantis\Routes\UploadAPI {

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'GET')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityGet():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		if(!$this->Data->ID)
		$this->Quit(1, 'no ID specified');

		////////

		$Entity = Atlantis\Media\File::GetByID($this->Data->ID);

		if(!$Entity)
		$this->Quit(2, 'not entity found');

		////////

		$this->SetPayload($Entity->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityDelete():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		if(!$this->Data->ID)
		$this->Quit(1, 'no ID specified');

		$Entity = Atlantis\Media\File::GetByID($this->Data->ID);

		if(!$Entity)
		$this->Quit(2, 'not entity found');

		$Entity->Drop();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityPost():
	void {

		$this->ChunkPost();

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'POSTFINAL')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityPostFinal():
	void {

		$Entity = NULL;
		$ParentUUID = NULL;
		$ChildType = NULL;

		////////

		try { $this->ChunkFinalise(); }
		catch(Exception $Error) {
			$this->Quit(1, $Error->GetMessage());
			return;
		}

		($this->Data)
		->TagID(
			Common\Filters\Lists::ArrayOfNullable(...),
			Common\Filters\Numbers::IntType(...)
		)
		->ParentUUID(
			Common\Filters\Lists::ArrayOfNullable(...),
			Common\Filters\Text::UUID(...)
		)
		->ParentType(Common\Filters\Text::TrimmedNullable(...));

		////////

		$Entity = Atlantis\Media\File::GetByUUID($this->Data->UUID);

		if(!$Entity)
		$this->Quit(2, 'invalid or unhandled upload');

		////////

		$ChildType = match(TRUE) {
			$Entity->Type === $Entity::TypeImg
			=> 'Media.Image',

			default
			=> 'Media.File'
		};

		if(is_iterable($this->Data->ParentUUID) && $this->Data->ParentType)
		foreach($this->Data->ParentUUID as $ParentUUID) {
			Atlantis\Struct\EntityRelationship::Insert([
				'ParentType'  => $this->Data->ParentType,
				'ParentUUID'  => $ParentUUID,
				'ChildType'   => $ChildType,
				'ChildUUID'   => $Entity->UUID
			]);
		}

		$this->SetPayload($Entity->DescribeForPublicAPI());

		return;
	}

	protected function
	OnFinaliseInspectFile(Storage\File $File):
	void {

		if($File->GetType() === $File::TypeImg) {
			if(!Atlantis\Util::IsFormatSupported($File->GetExtension()))
			throw new Atlantis\Error\Media\UnsupportedFormat($File->GetExtension());
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'TAGSGET')]
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

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'TAGSPATCH')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityTagsSet():
	void {

		($this->Data)
		->EntityType(Common\Filters\Text::Trimmed(...))
		->EntityUUID(Common\Filters\Text::Trimmed(...))
		->TagID(
			Common\Filters\Lists::ArrayOfNullable(...),
			Common\Filters\Numbers::IntType(...)
		)
		->TagName(
			Common\Filters\Lists::ArrayOfNullable(...),
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

		//var_dump($EInfo->LinkClass);

		$Add->Each(
			fn(int $TagID)
			=> ($EInfo->LinkClass)::Insert([ 'TagID'=> $TagID, 'EntityUUID'=> $Entity->UUID ])
		);

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'REGEN')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityRegen():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		if(!$this->Data->ID)
		$this->Quit(1, 'no ID specified');

		$Entity = Atlantis\Media\File::GetByID($this->Data->ID);

		if(!$Entity)
		$this->Quit(2, 'not entity found');

		try {
			$Entity->GenerateExtraFiles();
		}

		catch(Exception $Error) {
			$this->Quit(3, $Error->GetMessage());
		}

		$this->SetPayload([
			'ID'          => $Entity->ID,
			'Type'        => $Entity->Type,
			'DateCreated' => $Entity->DateCreated,
			'URL'         => $Entity->GetPublicURL(),
			'ExtraFiles'  => $Entity->ExtraFiles->Keys()
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'SEARCH')]
	public function
	EntitySearch():
	void {

		($this->Data)
		->Page(Common\Filters\Numbers::Page(...))
		->Limit(Common\Filters\Numbers::IntRange(...), 10, 30, 0);

		$Filters = [
			'Type'  => 'img',
			'Sort'  => 'newest',
			'Page'  => $this->Data->Page,
			'Limit' => $this->Data->Limit ?: 20
		];

		$Results = Atlantis\Media\File::Find($Filters);

		$this->SetPayload([
			'Filters' => $Filters,
			'Results' => $Results->Map(
				fn(Atlantis\Media\File $File)
				=> $File->GetEntityInfo()
			)
		]);

		return;
	}

}
