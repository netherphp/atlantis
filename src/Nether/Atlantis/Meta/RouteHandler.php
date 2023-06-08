<?php

namespace Nether\Atlantis\Meta;
use Nether;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class RouteHandler
extends Nether\Avenue\Meta\RouteHandler {

}
