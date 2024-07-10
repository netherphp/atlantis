<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Meta\Prototype;

use Attribute;

################################################################################
################################################################################

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ExtraDataFilter {

	public string
	$Key;

	public mixed
	$Callable;

	public function
	__Construct(string $Key, mixed $Callable) {

		$this->Key = $Key;
		$this->Callable = $Callable;

		return;
	}

};
