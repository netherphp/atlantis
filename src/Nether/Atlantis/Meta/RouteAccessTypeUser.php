<?php

namespace Nether\Atlantis\Meta;
use Nether;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class RouteAccessTypeUser
extends RouteAccessType {

	public function
	__Construct() {
		parent::__Construct('Session');
		return;
	}

}
