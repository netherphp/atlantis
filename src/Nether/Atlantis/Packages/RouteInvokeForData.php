<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Packages;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

################################################################################
################################################################################

trait RouteInvokeForData {

	public function
	__invoke(string $Key):
	mixed {

		//if(!$this instanceof Atlantis\PublicWeb)
		//throw new Common\Error\FormatInvalid(Atlantis\PublicWeb::class);

		return $this->Data->Get($Key);
	}

};
