<?php

namespace Nether\Atlantis\Plugin\Interfaces\Profile;

use Nether\Atlantis;

interface ExtendGetPageURLInterface {

	// when applied to an Atlantis\Plugin we can intercept and determine
	// a custom url for this profile. the original use case was so that
	// profiles with certain tags went to a different domain name.

	public function
	GetPageURL(Atlantis\Profile\Entity $Profile):
	?string;

};
