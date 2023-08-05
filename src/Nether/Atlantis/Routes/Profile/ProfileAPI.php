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

		$Dataset = Atlantis\Profile\Entity::DatasetFromInput($this->Data);
		$Ent = NULL;

		try { $Ent = Atlantis\Profile\Entity::Insert($Dataset); }
		catch(Exception $Err) { $this->Quit(100, $Err->GetMessage()); }

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

		$this->SetPayload($Ent->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/profile/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityDelete():
	void {

		$Ent = $this->DemandEntityByID($this->Data->ID);
		$Ent->Drop();

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
