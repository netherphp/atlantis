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

	public bool
	$Numbers = FALSE;

	#[Common\Meta\PropertyFactory('FromArray', 'Items')]
	public array|Common\Datastore
	$Items = [];

	#[Common\Meta\PropertyFactory('FromArray', 'Disabled')]
	public array|Common\Datastore
	$Disabled = [];

	public function
	OnWith(iterable $Props):
	static {

		$Bounds = [ 'a', 'z' ];

		////////

		if($this->Uppercase)
		$Bounds = array_map(
			(fn(string $L)=> strtoupper($L)),
			$Bounds
		);

		////////

		$this->Items->SetData(range($Bounds[0], $Bounds[1]));

		if($this->Numbers)
		$this->Items->MergeRight(range(0, 9));

		return $this;
	}

	public function
	IsSelected(string $Val):
	bool {

		return $Val === $this->Selected;
	}

	public function
	Recase(string $Val):
	string {

		$V = match(TRUE) {
			$this->Uppercase => strtoupper($Val),
			default          => strtolower($Val)
		};

		return $V;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	ItemIsEnabled(string $Key):
	bool {

		$K = $this->Recase($Key);

		// not found means enabled.

		if(!$this->Disabled->HasKey($K))
		return TRUE;

		// found but false means enabled.

		if($this->Disabled->IsFalseEnough($K))
		return TRUE;

		////////

		return FALSE;
	}

	public function
	ItemIsDisabled(string $Key):
	bool {

		return !$this->ItemIsEnabled($Key);
	}

	public function
	ItemEnable(string $Key, bool $Enabled=TRUE):
	static {

		$K = $this->Recase($Key);
		$this->Disabled->Set($K, !$Enabled);

		return $this;
	}

	public function
	ItemEnableAll():
	static {

		foreach($this->Items as $I)
		$this->ItemEnable($I, TRUE);

		return $this;
	}

	public function
	ItemDisable(string $Key):
	static {

		return $this->ItemEnable($Key, FALSE);
	}

	public function
	ItemDisableAll():
	static {

		foreach($this->Items as $I)
		$this->ItemEnable($I, FALSE);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetQueryBroth(string $Val):
	array {

		$Bits = [
			$this->Field => $Val
		];

		return $Bits;
	}

}
