<?php

namespace Nether\Atlantis\Routes;

use Nether\Atlantis;
use Nether\Common;

use Exception;
use Nether\Atlantis\Struct\EntityRelationship;

class EntityRelationshipAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/eri/entity', Verb: 'GET')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityGet():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...))
		->UUID(Common\Filters\Text::UUID(...));

		$Key = ($this->Data->ID ? 'ID' : 'UUID');
		$Val = ($this->Data->ID ? $this->Data->ID : $this->Data->UUID);
		$Ent = Atlantis\Struct\EntityRelationship::GetByField($Key, $Val);

		if(!$Ent)
		$this->Quit(1, "no relationship found ({$Key}: {$Val})");

		////////

		$this->SetPayload($Ent->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/eri/entity', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityPost():
	void {

		($this->Data)
		->AppendOnly(Common\Filters\Numbers::BoolType(...))
		->ParentType(Common\Filters\Text::TrimmedNullable(...))
		->ParentUUID(Common\Filters\Text::UUID(...))
		->ChildType(Common\Filters\Text::TrimmedNullable(...))
		->ChildUUID(
			Common\Filters\Lists::ArrayOfNullable(...),
			Common\Filters\Text::UUID(...)
		);

		$Added = new Common\Datastore;
		$ChildUUID = NULL;
		$Old = NULL;

		////////

		if(!$this->Data->ParentType)
		$this->Quit(1, 'no ParentType defined');

		if(!$this->Data->ParentUUID)
		$this->Quit(2, 'no ParentUUID defined');

		if(!$this->Data->ChildType)
		$this->Quit(3, 'no ChildType defined');

		//if(!is_array($this->Data) || !count($this->Data->ChildUUID))
		//$this->Quit(4, 'no ChildUUID have been defined');

		// make an index of the relationships we already have to use as
		// a way to determine what needs to be removed at the end of the
		// process.

		$Old = EntityRelationship::Find([
			'ParentUUID' => $this->Data->ParentUUID,
			'ChildType'  => $this->Data->ChildType
		]);

		if(is_array($this->Data->ChildUUID))
		foreach($this->Data->ChildUUID as $ChildUUID) {
			// demand that the UUIDs given exist in the tables it is
			// claimed they came from.

			try {
				$this->VerifyTypeUUID($this->Data->ParentType, $this->Data->ParentUUID);
				$this->VerifyTypeUUID($this->Data->ChildType, $ChildUUID);
			}

			catch(Exception $Err) {
				$this->Quit(1, $Err->GetMessage());
			}

			// if we do not already have a relationship that looks like
			// this then create one.

			$Exists = $Old->Accumulate(FALSE, (
				fn(bool $C, EntityRelationship $P)
				=> $C || $P->ChildUUID === $ChildUUID
			));

			if(!$Exists) {
				$Rel = Atlantis\Struct\EntityRelationship::Insert([
					'ParentType' => $this->Data->ParentType,
					'ParentUUID' => $this->Data->ParentUUID,
					'ChildType'  => $this->Data->ChildType,
					'ChildUUID'  => $ChildUUID
				]);

				$Added->Push($Rel);
			}

			// remove this tag from the old index to confirm that we
			// acknoleged this relationship.

			$Old->Filter(
				fn(EntityRelationship $P)
				=> $P->ChildUUID !== $ChildUUID
			);
		}

		// if we only wanted to append then zero our local old index out.

		if($this->Data->AppendOnly)
		$Old->Clear();

		// remove remaining relationships from the old index completely.

		$Old->Each(fn(EntityRelationship $P)=> $P->Drop());

		// describe for the api output.

		$Old->Remap(fn(Atlantis\Prototype $P)=> $P->DescribeForPublicAPI());
		$Added->Remap(fn(Atlantis\Prototype $P)=> $P->DescribeForPublicAPI());

		$this->SetPayload([
			'Added'   => $Added->GetData(),
			'Removed' => $Old->GetData()
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/eri/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityDelete():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...))
		->UUID(Common\Filters\Text::UUID(...));

		$Key = ($this->Data->ID ? 'ID' : 'UUID');
		$Val = ($this->Data->ID ? $this->Data->ID : $this->Data->UUID);
		$Ent = Atlantis\Struct\EntityRelationship::GetByField($Key, $Val);

		if(!$Ent)
		$this->Quit(1, "no relationship found ({$Key}: {$Val})");

		////////

		$Ent->Drop();

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/eri/entity', Verb: 'LIST')]
	#[Atlantis\Meta\RouteHandler('/api/eri/entity/list', Verb: 'GET')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityList():
	void {

		($this->Data)
		->ParentUUID(Common\Filters\Text::UUID(...))
		->ChildType(Common\Filters\Text::TrimmedNullable(...));

		$Filters = [
			'ParentUUID' => $this->Data->ParentUUID,
			'ChildType'  => $this->Data->ChildType
		];

		////////

		$Payload = new Common\Datastore;
		$Result = Atlantis\Struct\EntityRelationship::Find($Filters);

		$Result->Each(
			fn(Atlantis\Struct\EntityRelationship $E)
			=> $Payload->Push($E->DescribeForPublicAPI())
		);

		$this->SetPayload($Payload->GetData());
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	VerifyTypeUUID(string $Type, string $UUID):
	Atlantis\Prototype {

		// 1. if we can find a registered class
		// 2. and the class could find a row

		$Class = Atlantis\Struct\EntityRelationship::TypeClass($Type);
		$Inst = $Class::GetByField('UUID', $UUID);

		if(!$Inst)
		throw new Exception("entity not found {$Type} {$UUID}");

		return $Inst;
	}

}
