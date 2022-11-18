<?php

namespace Nether\Atlantis\Meta;
use Nether;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class RouteAccessTypeAdmin
extends RouteAccessType {

	public function
	__Construct(int $Min=1) {
		parent::__Construct('Admin', Value: $Min);
		return;
	}

}
