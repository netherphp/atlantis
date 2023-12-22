<?php

namespace Nether\Atlantis\Plugin;

use Nether\Atlantis;
use Nether\Common;

class Manager {

	protected Atlantis\Engine
	$App;

	protected Common\Datastore
	$Interfaces;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(Atlantis\Engine $App) {

		$this->App = $App;
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
	Common\Datastore {

		return $this->Interfaces[$IFace] ?? new Common\Datastore;
	}

	public function
	GetInstanced(string $IFace):
	Common\Datastore {

		$Output = $this->Get($IFace);

		$Output->Remap(fn(string $Class)=> new $Class);

		return $Output;
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

	public function
	RegisterMany(iterable $Map):
	static {

		$Key = NULL;
		$Val = NULL;

		foreach($Map as $Key => $Val) {

		}

		return $this;
	}

}
