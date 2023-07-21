<?php

namespace Nether\Atlantis\Struct;

use Nether\Common;

class DropdownItem
extends Common\Prototype {

	public string
	$Title;

	public ?string
	$Icon;

	public ?string
	$URL;

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$Attr;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetAttrForHTML():
	string {

		$Output = (
			($this->Attr)
			->MapKeys(fn(mixed $V, string $K)=> "{$K}={$V}")
			->Join(' ')
		);

		return $Output;
	}

	public function
	GetIcon():
	string {

		$Output = match(TRUE) {
			(str_starts_with($this->Icon, 'mdi-'))
			=> "mdi {$this->Icon}",

			(str_starts_with($this->Icon, 'fa-'))
			=> "fa fw {$this->Icon}",

			(str_starts_with($this->Icon, 'fab-'))
			=> "fab fw {$this->Icon}",

			(str_starts_with($this->Icon, 'far-'))
			=> "far fw {$this->Icon}",

			default
			=> $this->Icon
		};

		if(!$this->Title)
		$Output .= ' mr-0';

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(?string $Title='Item', ?string $Icon='mdi-edit', ?string $URL=NULL, ?iterable $Attr=NULL):
	static {

		$Output = new static;

		////////

		$Output->Title = $Title;
		$Output->Icon = $Icon;
		$Output->URL = $URL;

		if($Attr !== NULL)
		$Output->Attr->SetData($Attr);

		////////

		return $Output;
	}

};
