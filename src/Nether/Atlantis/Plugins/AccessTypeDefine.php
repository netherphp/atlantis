<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;

class AccessTypeDefine
implements AccessTypeDefineInterface {

	public function
	Get():
	iterable {

		$Output = [
			new Atlantis\User\AccessTypeDef(
				Atlantis\Library::AccessContactLogManage, 1,
				'Allow the user to view the Contact Us log.'
			),
			new Atlantis\User\AccessTypeDef(
				Atlantis\Library::AccessPageManage, 1,
				'Allow the user to manage Pages on the site.'
			)
		];

		return $Output;
	}

}