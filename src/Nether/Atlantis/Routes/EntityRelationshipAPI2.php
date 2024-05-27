<?php

################################################################################
namespace Nether\Atlantis\Routes; ##############################################

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

use Nether\Atlantis;
use Nether\Common;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class EntityRelationshipAPI2
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/eri/get')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleGet():
	void {

		($this->Data)
		->UUID(Common\Filters\Text::TrimmedNullable(...));

		////////

		$ER = $this->FetchRelationshipByUUID($this->Data->UUID);

		if(!$ER)
		$this->Quit(1, sprintf(
			'entity relationship not found: %s',
			$this->Data->UUID
		));

		////////

		$this->SetPayload(
			$ER->DescribeForPublicAPI()
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	FetchRelationshipByUUID(string $UUID):
	?Atlantis\Struct\EntityRelationship {

		$ER = Atlantis\Struct\EntityRelationship::GetByUUID($UUID);

		return $ER;
	}

}
