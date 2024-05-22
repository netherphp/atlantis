<?php

namespace Nether\Atlantis\Routes\Profile;

use Nether\Atlantis;
use Nether\Common;

use Exception;

class ProfileAPI
extends Atlantis\ProtectedAPI {

	public function
	OnReady(?Common\Datastore $Input):
	void {

		parent::OnReady($Input);

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/profile/entity', Verb: 'GET')]
	public function
	EntityGet():
	void {


		$Ent = $this->DemandEntityByID($this->Data->ID);
		$this->SetPayload($Ent->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/profile/entity', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityPost():
	void {

		($this->Data)
		->AliasPrefix([
			Common\Filters\Text::SlottableKey(...),
			Common\Filters\Text::TrimmedNullable(...)
		])
		->AdminNotes(Common\Filters\Text::TrimmedNullable(...))
		->ProfilePhoto(
			fn(Common\Struct\DatafilterItem $In)=>
			isset($_FILES['ProfilePhoto']) ? $_FILES['ProfilePhoto'] : NULL
		);

		////////

		$Dataset = Atlantis\Profile\Entity::DatasetFromInput($this->Data);
		$Ent = NULL;
		$Tag = NULL;

		////////

		if($this->Data->AliasPrefix)
		$Dataset['Alias'] = sprintf(
			'%s-%s',
			$this->Data->AliasPrefix,
			match(TRUE) {
				isset($Dataset['Alias'])
				=> Common\Filters\Text::SlottableKey($Dataset['Alias']),

				default
				=> Common\Filters\Text::SlottableKey($Dataset['Title'])
			}
		);

		////////

		$TagList = (
			Common\Datastore::FromArray(explode(
				',',
				Common\Filters\Text::TrimmedNullable($this->Data->Tags)
			))
			->Remap(fn(string $S)=> trim($S))
		);

		$Tags = Atlantis\Tag\Entity::Find([
			'Alias' => $TagList->GetData()
		]);

		try {
			$Ent = Atlantis\Profile\Entity::Insert($Dataset);

			foreach(Atlantis\Util::FetchSiteTags() as $Tag) {
				Atlantis\Profile\EntityTagLink::InsertByPair(
					$Tag->ID,
					$Ent->UUID
				);
			}

			foreach($Tags as $Tag) {
				Atlantis\Profile\EntityTagLink::InsertByPair(
					$Tag->ID,
					$Ent->UUID
				);
			}

			if($this->Data->ProfilePhoto) {
				$Importer = Atlantis\Util\FileUploadImporter::FromUploadItem(
					$this->App,
					$this->Data->ProfilePhoto
				);

				$Image = $Importer->GetFileObject();

				$Ent->Update([ 'CoverImageID'=> $Image->ID ]);
			}

			if($this->Data->AdminNotes)
			$Ent->Update($Ent->Patch([
				'ExtraData' => [ 'AdminNotes'=> $this->Data->AdminNotes ]
			]));
		}

		catch(Exception $Err) {
			$this->Quit(100, $Err->GetMessage());
		}

		$this
		->SetGoto($Ent->GetPageURL())
		->SetPayload($Ent->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/profile/entity', Verb: 'PATCH')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityPatch():
	void {

		$Ent = $this->DemandEntityByID($this->Data->ID);
		$Patchset = $Ent->Patch($this->Data);

		if(count($Patchset))
		$Ent->Update($Patchset);

		////////

		// PLUGINS ON EDIT BRUH

		////////

		$this->SetPayload($Ent->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/profile/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityDelete():
	void {

		$Ent = $this->DemandEntityByID($this->Data->ID);

		if($Ent) {

			////////

			// PLUGINS ON DELETE BRUH

			////////

			$Ent->Drop();
		}

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/profile/entity', Verb: 'SEARCH')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntitySearch():
	void {

		($this->Data)
		->Q(Common\Filters\Text::TrimmedNullable(...))
		->TagsAll(Common\Filters\Text::TrimmedNullable(...))
		->Limit(
			Common\Filters\Numbers::IntRange(...),
			1, 20
		)
		->Sort(
			Common\Filters\Misc::OneOfTheseFirst(...),
			[ 'name-az', 'state-az' ]
		);

		////////

		if($this->Data->TagsAll) {
			$TagKeys = explode(',', $this->Data->TagsAll);
			$Tags = Atlantis\Tag\Entity::Find([ 'Alias' => $TagKeys ]);
			var_dump($Tags);
		}

		$Enabled = $this->IsUserAdmin() ? NULL : 1;

		////////

		$Results = Atlantis\Profile\Entity::Find([
			'UseSiteTags'    => FALSE,
			'TagsAll'        => $Tags->Map(fn($T)=> $T->ID)->GetData(),

			'Search'         => $this->Data->Q,
			'SearchTitle'    => TRUE,
			'SearchLocation' => TRUE,

			'Enabled'        => $Enabled,

			'Page'           => 1,
			'Limit'          => 10,
			'Sort'           => $this->Data->Sort,
			'Remappers'      => (
				fn(Atlantis\Profile\Entity $P)
				=> $P->DescribeForPublicAPI()
			)
		]);

		$this->SetPayload($Results->GetData());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/profile/entity', Verb: 'FILTERS')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityFilters():
	void {

		$this->SetPayload([
			'test' => 'Test'
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	DemandEntityByID(int $ID):
	Atlantis\Profile\Entity {

		$Ent = Atlantis\profile\Entity::GetByID($ID);

		if(!$Ent)
		$this->Quit(1, "Entity ID {$ID} not found");

		return $Ent;
	}

}
