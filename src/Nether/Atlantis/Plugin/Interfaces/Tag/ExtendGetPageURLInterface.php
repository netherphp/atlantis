<?php

namespace Nether\Atlantis\Plugin\Interfaces\Tag;

use Nether\Atlantis;

interface ExtendGetPageURLInterface {

	// when applied to an Atlantis\Plugin we can intercept and determine
	// a custom url for this tag. the original use case was so that a network
	// of sites would send all tags to the home domain.

	// urls defined in the extra data of the tag will take priority. to make
	// a tag hit these its defined url must be removed from extra data.

	public function
	GetPageURL(Atlantis\Tag\Entity $Tag):
	?string;

};
