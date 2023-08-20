<?php

namespace Nether\Atlantis\Plugin;

use Nether\Common;

class Manager {

	protected Common\Datastore
	$Interfaces;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct() {

		$this->Interfaces = new Common\Datastore;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Clear(string $IFace):
	static {

		if($this->Has($IFace))
		unset($this->Interfaces[$IFace]);

		$this->Interfaces[$IFace] = new Common\Datastore;

		return $this;
	}

	public function
	Get(string $IFace):
	?Common\Datastore {

		return $this->Interfaces[$IFace] ?? new Common\Datastore;
	}

	public function
	Has(string $IFace):
	bool {

		return (
			TRUE
			&& $this->Interfaces->HasKey($IFace)
			&& $this->Interfaces[$IFace]->Count()
		);
	}

	public function
	Register(string $IFace, string $Plug):
	static {

		if(!$this->Has($IFace))
		$this->Clear($IFace);

		$this->Interfaces[$IFace]->Push($Plug);

		return $this;
	}



}
