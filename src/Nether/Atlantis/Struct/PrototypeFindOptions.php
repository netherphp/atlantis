<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

use IteratorAggregate;
use Traversable;
use Exception;

#[Common\Meta\Date('2024-11-16')]
class PrototypeFindOptions
extends Common\Prototype
implements
	IteratorAggregate,
	Common\Interfaces\ToArray,
	Common\Interfaces\ToDatastore {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2024-11-16')]
	public function
	GetIterator():
	Traversable {

		return $this->ToDatastore();
	}

	#[Common\Meta\Date('2024-11-16')]
	public function
	ToDatastore():
	Common\Datastore {

		$Props = Common\Meta\PropertyListable::FromClass($this::class);
		$Dataset = new Common\Datastore;

		foreach($Props as $P) {
			/** @var Common\Prototype\PropertyInfo $P */
			$Dataset->Set($P->Name, $this->{$P->Name});
		}

		return $Dataset;
	}

	#[Common\Meta\Date('2024-11-16')]
	public function
	ToArray():
	array {

		return $this->ToDatastore()->ToArray();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2024-11-16')]
	#[Common\Meta\Info('Return TRUE if all keys NULL (or never set).')]
	public function
	IsNull(...$Keys):
	bool {

		foreach($Keys as $K) {
			if(!property_exists($this, $K))
			return FALSE;

			if(isset($this->{$K}) && ($this->{$K} !== NULL))
			return FALSE;
		}

		////////

		return TRUE;
	}

	public function
	Import(iterable $Input):
	static {

		$K = NULL;
		$V = NULL;

		////////

		foreach($Input as $K=> $V) {
			if(!property_exists($this, $K))
			continue;

			$this->{$K} = $V;
		}

		return $this;
	}

	#[Common\Meta\Date('2024-11-16')]
	public function
	Keys():
	array {

		return $this->ToDatastore()->Keys();

	}

	#[Common\Meta\Date('2024-11-16')]
	public function
	Find():
	Common\Datastore {

		$CInfo = static::FetchClassInfo();
		$Attr = $CInfo->GetAttribute(Atlantis\Meta\Prototype\FindOptionsPrototype::class);

		////////

		/**
		 * @var Atlantis\Meta\Prototype\FindOptionsPrototype $Attr
		 */

		if($Attr) {
			if($Attr->IsValidPrototype())
			return $Attr->Find($this);
		}

		////////

		throw new \Exception(sprintf(
			'define FindPrototype constant or override %s::%s',
			static::class,
			__METHOD__
		));

		return new Common\Datastore;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromArray(iterable $Input):
	static {

		$Output = new static;
		$Output->Import($Input);

		return $Output;
	}

};

