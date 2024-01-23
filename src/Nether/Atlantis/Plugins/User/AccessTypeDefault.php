<?php

namespace Nether\Atlantis\Plugins\User;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

use Nether\Atlantis;

use Nether\Atlantis\User\AccessTypeDef;
use Nether\Atlantis\Plugin\Interfaces\User\AccessTypeDefineInterface;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class AccessTypeDefault
implements AccessTypeDefineInterface {

	public function
	Get():
	iterable {

		$Output = [
			new AccessTypeDef(
				Atlantis\Key::AccessContactLogManage, 1,
				'Allow the user to view the Contact Us log.'
			),
			new AccessTypeDef(
				Atlantis\Key::AccessPageManage, 1,
				'Allow the user to manage Pages on the site.'
			)
		];

		return $Output;
	}

};
