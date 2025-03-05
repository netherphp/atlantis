<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Blob;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

#[Common\Meta\Date('2025-02-11')]
class API
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/atl/blob/entity', Verb: 'GET')]
	#[Atlantis\Meta\RouteAccessType(Atlantis\Blob\Entity::AccessTypeManage)]
	public function
	EntityGet():
	void {

		$Ent = $this->FetchBlobEntityBasedOnInput();

		if(!$Ent)
		$this->Quit(1, 'no blob entity found');

		////////

		$this->SetPayload($Ent->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/atl/blob/entity', Verb: 'PATCH')]
	#[Atlantis\Meta\RouteAccessType(Atlantis\Blob\Entity::AccessTypeManage)]
	public function
	EntityPatch():
	void {

		$Ent = $this->FetchBlobEntityBasedOnInput();

		if(!$Ent)
		$this->Quit(1, 'no blob entity found');

		////////

		$Patch = $Ent->Patch($this->Data);

		$Ent->Update($Patch);

		$this->SetPayload($Ent->DescribeForPublicAPI());

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Info('allow ID or UUID to fetch a blob object.')]
	protected function
	FetchBlobEntityBasedOnInput():
	?Atlantis\Blob\Entity {

		$Ent = NULL;

		////////

		if(!$Ent && $this->Data->Exists('ID'))
		$Ent = Atlantis\Blob\Entity::GetByID(Common\Filters\Numbers::IntType(
			$this->Data->Get('ID')
		));

		if(!$Ent && $this->Data->Exists('UUID'))
		$Ent = Atlantis\Blob\Entity::GetByUUID(Common\Filters\Text::Trimmed(
			$this->Data->Get('UUID')
		));

		if(!$Ent)
		return NULL;

		////////

		return $Ent;
	}

};
