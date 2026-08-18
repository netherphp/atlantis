<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Plugin\Interfaces\Prototype;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

interface TagPatchEvent {

	public function
	OnPatch(
		Atlantis\Prototype $Ent,
		Common\Datastore $Added,
		Common\Datastore $Removed
	);

};
