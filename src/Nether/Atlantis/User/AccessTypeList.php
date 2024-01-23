<?php

namespace Nether\Atlantis\User;

use Nether\Atlantis;
use Nether\User;

use Nether\Common\Datastore;

class AccessTypeList
extends Datastore {

	static public function
	Fetch(Atlantis\Engine $App):
	Datastore {

		$Output = new static;
		$Plugins = $App->Plugins->Get(Atlantis\Plugin\Interfaces\User\AccessTypeDefineInterface::class);
		$Plugin = NULL;
		$Plug = NULL;

		foreach($Plugins as $Plugin) {
			$Plug = new $Plugin($App);
			$Output->MergeRight($Plug->Get());
		}

		$Output->Sort(function(AccessTypeDef $A, AccessTypeDef $B) {

			if($A->Key !== $B->Key)
			return $A->Key <=> $B->Key;

			return $A->Value <=> $B->Value;
		});

		return $Output;
	}

}