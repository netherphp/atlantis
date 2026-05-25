<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Profile;

use Nether\Atlantis;
use Nether\Common;

use Nether\Atlantis\Plugin\Interfaces\ProfileAPI\EntityEventPatchset;
use Nether\Atlantis\Plugin\Interfaces\ProfileAPI\EntityEventUpdated;

################################################################################
################################################################################

class EntityEventHelper {

	//. an api interface that needs to handle updating a profile entity
	//. should call this method to allow configured plugins to perform
	//. modification to the patchset data.

	//. 1) collect input from where ever
	//. 2) call EventEntityHelper::Patch()
	//. 3) call $Entity->Update() on the object you have with the patchset you have.
	//. 4) call EventEntityHelper::Updated()

	static public function
	Patchset(Atlantis\Engine $Atl, Atlantis\Profile\Entity $Profile, Common\Datastore $Patchset):
	void {

		$Plugins = $Atl->Plugins->GetInstanced(EntityEventPatchset::class);
		$Plug = NULL;

		////////

		foreach($Plugins as $Plug) {
			/** @var EntityEventPatchset $Plug */
			$Plug->Patchset($Atl, $Profile, $Patchset);
		}

		return;
	}

	static public function
	Updated(Atlantis\Engine $Atl, Atlantis\Profile\Entity $Profile, Common\Datastore $Patchset):
	void {

		$Plugins = $Atl->Plugins->GetInstanced(EntityEventUpdated::class);
		$Plug = NULL;

		////////

		foreach($Plugins as $Plug) {
			/** @var EntityEventUpdated $Plug */
			$Plug->Updated($Atl, $Profile, $Patchset);
		}

		return;
	}

};
