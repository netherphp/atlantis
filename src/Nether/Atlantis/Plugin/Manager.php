<?php

namespace Nether\Atlantis\Plugin;

use Nether\Atlantis;
use Nether\Common;

class Manager {

	protected Atlantis\Engine
	$App;

	protected Common\Datastore
	$Interfaces;

	protected Common\Datastore
	$Namespaces;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(Atlantis\Engine $App) {

		$this->App = $App;
		$this->Interfaces = new Common\Datastore;
		$this->Namespaces = new Common\Datastore;

		$this->RegisterInterfaceNamespace('Nether\Atlantis\Plugin\Interfaces');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Info('Register a plugin namespace for magic registration.')]
	public function
	RegisterInterfaceNamespace(string $Namespace):
	static {

		$this->Namespaces->Push($Namespace);

		return $this;
	}

	#[Common\Meta\Info('Register a specific plugin with a specific interface.')]
	public function
	RegisterInterfacePlugin(string $IFace, string $Plugin):
	static {

		if(!$this->Has($IFace))
		$this->Clear($IFace);

		$this->Interfaces[$IFace]->Push($Plugin);

		return $this;
	}

	#[Common\Meta\Info('Register class with all its plugin interfaces.')]
	public function
	Register(string $Class):
	static {

		// make a list of any interfaces this class implements that are
		// within any of the registered plugin interface namespaces.

		$Faces = array_filter(
			class_implements($Class),
			(fn(string $I)=> $this->Namespaces->Accumulate(
				FALSE,
				(fn(bool $C, string $N)=> str_starts_with($I, $N) ? TRUE : $C)
			))
		);

		// then register this plugin with each of those interfaces so that
		// they can be queried later.

		array_walk(
			$Faces,
			(fn(string $IFace)=> $this->RegisterInterfacePlugin($IFace, $Class))
		);

		return $this;
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

}
