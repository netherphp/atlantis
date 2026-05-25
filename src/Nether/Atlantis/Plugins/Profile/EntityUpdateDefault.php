<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Plugins\Profile;

use Nether\Atlantis;
use Nether\Browser;
use Nether\Common;

################################################################################
################################################################################

class EntityUpdateDefault
implements
	Atlantis\Plugin\Interfaces\ProfileAPI\EntityEventPatchset,
	Atlantis\Plugin\Interfaces\ProfileAPI\EntityEventUpdated {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Patchset(Atlantis\Engine $Atl, Atlantis\Profile\Entity $Profile, Common\Datastore $Patchset):
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Updated(Atlantis\Engine $Atl, Atlantis\Profile\Entity $Profile, Common\Datastore $Patchset):
	void {

		$this->TryToUpdateAttachedFilename($Profile);
		$this->TryToUpdateAddressGeoCoordsLazy($Atl, $Profile);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	TryToUpdateAttachedFilename(Atlantis\Profile\Entity $Profile):
	void {

		// bail if nothing attached.

		if(!$Profile->ParentUUID)
		return;

		// bail if not file or not found.

		$File = Atlantis\Media\File::GetByUUID($Profile->ParentUUID);

		if(!$File)
		return;

		// update the file name to match the profile title.

		$File->Update([
			'Name' => $Profile->GetTitle()
		]);

		return;
	}

	protected function
	TryToUpdateAddressGeoCoordsLazy(Atlantis\Engine $App, Atlantis\Profile\Entity $Profile):
	void {

		// todo: write a version of this that will perform it during the
		// patchset if it sees an address edit then perform a lookup so
		// to avoid this second update.

		if($Profile->IsAddressMappable() && !$Profile->HasGeoCoords()) {
			$MapKitTokFile = $App->FromConfEnv('keys/apple-mapkit.txt');
			$MapKitToken = NULL;
			$MapKitAPI = NULL;
			$MapKitCoord = NULL;

			if(file_exists($MapKitTokFile)) {
				$MapKitToken = trim(file_get_contents($App->FromConfEnv('keys/apple-mapkit.txt')));
				$MapKitAPI = Browser\Clients\AppleMap::FromMapKitToken($MapKitToken);
				$MapKitCoord = $MapKitAPI->LookupAddressCoords($Profile->GetAddressConcat());

				if($MapKitCoord)
				$Profile->Update($Profile->Patch([
					'ExtraData' => [ 'GeoCoord'=> $MapKitCoord ]
				]));
			}
		}

		return;
	}

};
