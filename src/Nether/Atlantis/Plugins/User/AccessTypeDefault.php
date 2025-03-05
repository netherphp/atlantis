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
				Atlantis\Key::AccessContentLog, 1,
				'Access the basic CMS info.'
			),
			new AccessTypeDef(
				Atlantis\Key::AccessTrafficLog, 1,
				'Access the traffic log info.'
			),
			new AccessTypeDef(
				Atlantis\Key::AccessContactLogManage, 1,
				'Access the Contact Us log.'
			),
			new AccessTypeDef(
				Atlantis\Key::AccessPageManage, 1,
				'Manage Pages on the site.'
			),
			new AccessTypeDef(
				Atlantis\Key::AccessTagAdmin, 1,
				'Use site administration tags.'
			),
			new AccessTypeDef(
				Atlantis\Blob\Entity::AccessTypeManage, 1,
				'Manage Content Blobs'
			)
		];

		return $Output;
	}

};
