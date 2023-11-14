<?php

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

#[Common\Meta\Date('2023-09-15')]
class Dropdown
extends Surface\Element {

	////////////////////////////////////////////////////////////////
	// OVERRIDES: Surface\Element /////////////////////////////////

	public string
	$Area = 'elements/dropdown/main';

	////////

	public function
	Render():
	string {

		return parent::Render();
	}

	static public function
	ExpandAreaPath(string $Area):
	string {

		if(str_starts_with($Area, '~/'))
		$Area = preg_replace('#^~/#', 'elements/dropdown/', $Area);

		return $Area;
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Menu API /////////////////////////////////////////////

	public Atlantis\Struct\DropdownMenu
	$Menu;

	public function
	SetMenuStruct(Atlantis\Struct\DropdownMenu $Menu):
	static {

		$this->Menu = $Menu;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Factory API //////////////////////////////////////////

	#[Common\Meta\Deprecated('2023-10-17')]
	static public function
	FromMenuStruct(Surface\Engine $Surface, Atlantis\Struct\DropdownMenu $Menu):
	static {

		$Output = new static($Surface);
		$Output->SetMenuStruct($Menu);

		return $Output;
	}

	static public function
	FromSurfaceWithMenu(Surface\Engine $Surface, Atlantis\Struct\DropdownMenu $Menu):
	static {

		$Output = new static($Surface);
		$Output->SetMenuStruct($Menu);

		return $Output;
	}

}
