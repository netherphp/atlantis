<?php

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

#[Common\Meta\Date('2023-11-22')]
class Pillbox
extends Surface\Element {

	public string
	$Area = 'elements/pillbox/main';

	////////

	public ?string
	$Section = NULL;

	#[Common\Meta\PropertyFactory('FromArray')]
	public array|Common\Datastore
	$Items = [];

	#[Common\Meta\Info('used by moredown')]
	public int
	$Max = 3;

	#[Common\Meta\Info('used by moredown')]
	public bool
	$MaxAuto = FALSE;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetMax(?int $Max=NULL):
	static {

		$this->Max = (int)$Max;

		return $this;
	}

	public function
	GetSection():
	?string {

		return $this->Section;
	}

	public function
	SetSection(?string $Section):
	static {

		$this->Section = $Section;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromSurfaceWithItems(Surface\Engine $Surface, iterable $Items):
	static {

		$Output = new static($Surface);
		$Output->Items->SetData($Items);

		return $Output;
	}

}
