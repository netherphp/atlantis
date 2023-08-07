<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

class NetworkItem
extends Common\Prototype {

	#[Common\Meta\PropertyFactory('FromArray', 'Social')]
	public array|Atlantis\Struct\SocialData
	$Social = [];

	#[Common\Meta\PropertyFactory('FromArray', 'Sitemap')]
	public array|Common\Datastore
	$Sitemap = [];

}



