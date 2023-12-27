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

		if(!$this->Interfaces->HasKey($IFace))
		$this->Interfaces->Shove($IFace, new Common\Datastore);

		return $this->Interfaces[$IFace]->Copy();
	}

	public function
	GetInstanced(string $IFace):
	Common\Datastore {

		$Output = $this->Get($IFace);

		$Output->Remap(fn(string $Class)=> new $Class($this->App));

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
	RegisterPluginMap(iterable $Map):
	static {

		$Key = NULL;
		$Val = NULL;

		foreach($Map as $Key => $Val) {
			$this->Register($Key, $Val);
		}

		return $this;
	}

}
