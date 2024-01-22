<?php

namespace Nether\Atlantis\Plugin\Interfaces\ProfileView;

use Nether\Atlantis;
use Nether\Common;

interface AdminMenuAuditInterface {

	public function
	AuditItems(Atlantis\Profile\Entity $Profile, Common\Datastore $Sections, Common\Datastore $ExtraData):
	void;

};

