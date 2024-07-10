<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Meta\Prototype;

use Attribute;

################################################################################
################################################################################

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ExtraDataFilter {

	// this attribute's main purpose is to define extra data key-value
	// filterings so that prototype objects can continue to use the ExtraData
	// willy nilly while preventing garbage input and allowing for
	// reinforcement of the data that came in into its proper hard type
	// when needed. this gives ExtraData simliar sanitisation features as
	// class properties with the PropertyFilter attribute.

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
