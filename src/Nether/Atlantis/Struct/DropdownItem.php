<?php

namespace Nether\Atlantis\Struct;

use Nether\Common;

class DropdownItem
extends Common\Prototype {

	const
	Normal   = 0,
	Warning  = 1,
	Danger   = 2,
	Success  = 3;

	public string
	$Title;

	public ?string
	$Icon;

	public ?string
	$URL;

	public int
	$Warn = 0;

	#[Common\Meta\PropertyFactory('FromArray', 'Attr')]
	public array|Common\Datastore
	$Attr = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetAttrForHTML():
	string {

		$Output = [];

		($this->Attr)
		->Each(
			function(mixed $V, string $K) use(&$Output) {
				array_push($Output, "{$K}=\"{$V}\"");
				return;
			}
		);

		return join(' ', $Output);
	}

	public function
	GetTitle():
	string {

		return $this->Title;
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

	public function
	GetURL():
	string {

		return $this->URL ?? '#';
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(?string $Title='Item', ?string $Icon='mdi-edit', ?string $URL=NULL, ?iterable $Attr=[], ?int $Warn=0):
	static {

		$Output = new static([
			'Warn' => $Warn
		]);

		////////

		$Output->Title = $Title;
		$Output->Icon = $Icon;
		$Output->URL = $URL;
		$Output->Warn = $Warn;

		if($Attr !== NULL)
		$Output->Attr->SetData($Attr);

		////////

		return $Output;
	}

};
