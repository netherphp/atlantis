<?php

namespace Nether\Atlantis\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TagEntityProperty {
/*//
this attribute marks a property to be the "Entity" side of a Tag-Entity
relationship in the database. this allows the tag linking class to
automatically fill it in.
//*/

	public string
	$LinkType;

	public function
	__Construct(string $LinkType) {

		$this->LinkType = $LinkType;

		return;
	}

}
