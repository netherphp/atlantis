<?php

namespace Nether\Atlantis\Meta\Prototype;

use Nether\Atlantis;
use Nether\Common;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class FindClass {

	public string
	$ClassName;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $ClassName) {

		$this->ClassName = $ClassName;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	IsValidPrototype():
	bool {

		return (TRUE
			&& isset($this->ClassName)
			&& class_exists($this->ClassName)
			&& is_a($this->ClassName, Atlantis\Prototype::class, TRUE)
		);
	}

	public function
	Find(iterable $Input):
	Common\Datastore {

		return ($this->ClassName)::Find($Input);
	}

};
