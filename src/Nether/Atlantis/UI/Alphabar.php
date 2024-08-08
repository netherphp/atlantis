<?php

namespace Nether\Atlantis\UI;

use Nether\Common;
use Nether\Surface;

class Alphabar
extends Surface\Element {

	public string
	$Area = 'elements/alphabar/main';

	public string
	$Title = 'Alphabar';

	public string
	$BaseURL = '';

	public string
	$Field = 'letter';

	public string
	$Selected = '';

	public bool
	$Uppercase = TRUE;

	#[Common\Meta\PropertyFactory('FromArray', 'Items')]
	public array|Common\Datastore
	$Items = [];

	public function
	OnWith(iterable $Props):
	static {

		$Bounds = [ 'a', 'z' ];

		////////

		if($this->Uppercase)
		$Bounds = array_map(
			(fn(string $L)=> strtolower($L)),
			$Bounds
		);

		////////

		$this->Items->SetData(range($Bounds[0], $Bounds[1]));

		return $this;
	}

	public function
	IsSelected(string $Val):
	bool {

		return $Val === $this->Selected;
	}

	public function
	GetQueryBroth(string $Val):
	array {

		$Bits = [
			$this->Field => $Val
		];

		return $Bits;
	}

}
