<?php

namespace Nether\Atlantis\Meta;
use Nether;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class UserActivationFlow {
/*//
routes with this attribute will not trigger a redirect if the account
accessing them is not yet activted.
//*/
}
