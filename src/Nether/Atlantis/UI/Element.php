<?php

namespace Nether\Atlantis\UI;

use Stringable;

class Element
implements Stringable {

	public ?string
	$Title;

	public ?string
	$Icon;

	public ?string
	$Class;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(?string $Title=NULL, ?string $Icon=NULL, ?string $Class=NULL) {

		$this->Title = $Title;
		$this->Icon = $Icon;
		$this->Class = $Class;

		return;
	}

	public function
	__ToString():
	string {

		return $this->Render();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetIconHTML():
	string {

		if(!$this->Icon)
		return '';

		////////

		if(str_starts_with($this->Icon, 'mdi-'))
		return sprintf('<i class="mdi %s"></i> ', $this->Icon);

		if(str_starts_with($this->Icon, 'si-'))
		return sprintf('<i class="si %s"></i> ', $this->Icon);

		////////

		return $this->Icon;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetIcon(?string $Icon):
	static {

		$this->Icon = $Icon;

		return $this;
	}

	public function
	SetTitle(?string $Title):
	static {

		$this->Title = $Title;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Render():
	string {

		return '';
	}

	public function
	Print():
	void {

		echo $this->Render();
		return;
	}

}
