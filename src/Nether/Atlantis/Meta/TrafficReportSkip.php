<?php

namespace Nether\Atlantis\Meta;
use Nether;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class TrafficReportSkip {
/*//
routes with this attribute will not log in the traffic report.
//*/
}
