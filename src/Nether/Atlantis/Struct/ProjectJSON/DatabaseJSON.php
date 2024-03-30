<?php

namespace Nether\Atlantis\Struct\ProjectJSON;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

use Nether\Common;
use Nether\Database;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class DatabaseJSON
extends Common\Prototype
implements
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON,
	Common\Interfaces\ToString {

	use
	Common\Package\ToJSON,
	Common\Package\ToString;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	private Common\Datastore
	$Connections;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		parent::OnReady($Args);

		$this->Connections = Common\Datastore::FromArray(
			$Args->Input
		);

		$this->Connections->RemapKeyValue(
			fn(string $Name, array $Row)
			=> new Database\Connection(...[ 'Name'=> $Name, ...$Row ])
		);

		return;
	}

	public function
	__DebugInfo():
	array {

		return $this->Connections->GetData();
	}

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Interfaces\ToArray ////////////////////////

	public function
	ToArray():
	array {

		$Data = $this->Connections->Map(fn(Database\Connection $C)=>
			Common\Datastore::FromArray($C->ToArray())
			->Remove('Name')
			->GetData()
		);

		return $Data->GetData();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetConnections():
	Common\Datastore {

		return $this->Connections->Copy();
	}

	public function
	SetConnection(Database\Connection $Cnx):
	static {

		$this->Connections[$Cnx->Name] = $Cnx;

		return $this;
	}

	public function
	DeleteConnection(string $Name):
	static {

		if($this->Connections->HasKey($Name))
		$this->Connections->Remove($Name);

		return $this;
	}

	public function
	HasAnything():
	bool {

		$Anything = (FALSE
			|| $this->Connections->Count()
		);

		return $Anything;
	}

};
