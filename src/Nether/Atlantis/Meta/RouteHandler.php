<?php

namespace Nether\Atlantis\Meta;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class RouteHandler
extends Avenue\Meta\RouteHandler {

	public function
	__Construct(?string $Path=NULL, ?string $Domain=NULL, ?string $Verb='GET', ?string $Sort=NULL) {
		parent::__Construct($Path, $Domain, $Verb, $Sort);
		return;
	}

}
