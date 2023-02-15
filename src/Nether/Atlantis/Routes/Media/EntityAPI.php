<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Storage;

use Exception;

class EntityAPI
extends Atlantis\Routes\UploadAPI {

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'GET')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityGet():
	void {

		($this->Data)
		->ID(Common\Datafilters::TypeInt(...));

		if(!$this->Data->ID)
		$this->Quit(1, 'no ID specified');

		$Entity = Atlantis\Media\File::GetByID($this->Data->ID);

		if(!$Entity)
		$this->Quit(2, 'not entity found');

		$this->SetPayload([
			'ID'          => $Entity->ID,
			'Type'        => $Entity->Type,
			'DateCreated' => $Entity->DateCreated,
			'URL'         => $Entity->GetPublicURL()
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityPost():
	void {

		$this->Queue(
			static::KiOnUploadComplete,
			$this->OnUpload(...),
			FALSE
		);

		$this->ChunkPost();
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityDelete():
	void {

		($this->Data)
		->ID(Common\Datafilters::TypeInt(...));

		if(!$this->Data->ID)
		$this->Quit(1, 'no ID specified');

		$Entity = Atlantis\Media\File::GetByID($this->Data->ID);

		if(!$Entity)
		$this->Quit(2, 'not entity found');

		$Entity->Drop();

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/media/entity', Verb: 'TAGSGET')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	EntityTagsGet():
	void {

		($this->Data)
		->EntityUUID(Common\Datafilters::TrimmedText(...));

		$Result = Atlantis\Media\TagLink::Find([
			'EntityUUID' => $this->Data->EntityUUID,
			'Sort'       => 'tag-name-az',
			'Limit'      => 20
		]);

		$Tags = [];
		$Link = NULL;

		foreach($Result as $Link) {
			/** @var Atlantis\Media\TagLink $Link */

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
		->EntityType(Common\Datafilters::TrimmedText(...))
		->EntityUUID(Common\Datafilters::TrimmedText(...))
		->TagID(fn(Common\Struct\DatafilterItem $Item)=> array_map(
			Common\Datafilters::TypeInt(...),
			Common\Datafilters::ArrayOf($Item->Value)
		))
		->TagName(fn(Common\Struct\DatafilterItem $Item)=> array_map(
			Common\Datafilters::TrimmedText(...),
			Common\Datafilters::ArrayOf($Item->Value)
		));

		////////

		$EType = $this->Data->EntityType;
		$EUUID = $this->Data->EntityUUID;
		$EInfo = NULL;

		$Entity = NULL;
		$TagsToHave = new Common\Datastore($this->Data->TagID);
		$TagsToMake = new Common\Datastore($this->Data->TagName);

		////////

		try {
			$EInfo = Atlantis\Media\TagLink::GetTypeEntityInfo($EType);
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
			fn(Atlantis\Media\TagLink $Link)
			=> $Link->TagID
		);

		$Add = $TagsToHave->Distill(
			fn(int $TagID)
			=> !$Existing->HasValue($TagID)
		);

		$Add->MergeRight(
			$TagsToMake
			->Map(function(string $Name) {
				$Tag = Atlantis\Media\Tag::GetByField('Name', $Name);

				if(!$Tag)
				$Tag = Atlantis\Media\Tag::Insert([ 'Name' => $Name ]);

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

	public function
	OnUpload(string $Name, Storage\File $File):
	void {

		$Storage = $this->PrepareStorageLocation('Default');
		$UUID = Common\UUID::V7();

		$Path = sprintf(
			'upl/%s/original.%s',
			join('/', explode('-',$UUID, 2)),
			$File->GetExtension()
		);

		////////

		$Storage->Put($Path, $File->Read());
		$File->DeleteParentDirectory();

		////////

		$File = $Storage->GetFileObject($Path);

		$Entity = Atlantis\Media\File::Insert([
			'UUID'   => $UUID,
			'UserID' => $this->User->ID,
			'Name'   => $Name,
			'Type'   => $File->GetType(),
			'Size'   => $File->GetSize(),
			'URL'    => $File->GetStorageURL()
		]);

		return;
	}

}
