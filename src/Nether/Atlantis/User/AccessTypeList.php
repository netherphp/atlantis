<?php

namespace Nether\Atlantis\User;

use Nether\Atlantis;
use Nether\User;

use Nether\Object\Datastore;

class AccessTypeList
extends Datastore {

	static public function
	Fetch(Atlantis\Engine $App):
	Datastore {

		$Output = new static;
		$Lib = NULL;

		foreach($App->Library as $Lib) {
			if($Lib instanceof Atlantis\Plugins\AccessTypeDefineInterface)
			$Lib->OnAccessTypeDefine($App, $Output);
		}

		$Output->Sort(function(AccessTypeDef $A, AccessTypeDef $B) {

			if($A->Key !== $B->Key)
			return $A->Key <=> $B->Key;

			return $A->Value <=> $B->Value;
		});

		return $Output;
	}

}