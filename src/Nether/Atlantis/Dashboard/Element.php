<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Atlantis;
use Nether\Surface;

class Element {

	public string
	$Title;

	public string
	$Area;

	public int
	$Priority = 1;

	public int|string
	$Columns = 'auto';

	////////

	protected Atlantis\Engine
	$App;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(Atlantis\Engine $App, string $Title, string $Area) {

		$this->App = $App;
		$this->Title = $Title;
		$this->Area = $Area;

		$this->OnReady();

		return;
	}

	protected function
	OnReady():
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetBootstrapColumnClasses():
	string {

		return match($this->Columns) {
			3, '3', 'full', 'three'
			=> 'col-12 col-md-12',

			2, '2', 'two', 'two-third'
			=> 'col-12 col-md-8',

			1, '1', 'one', 'one-third'
			=> 'col-12 col-md-4',

			'auto'
			=> 'col-auto',

			'share'
			=> 'col',

			default
			=> 'col'
		};
	}

	public function
	Render(Surface\Engine $Surface, array $Scope=[]):
	string {

		$Scope['Element'] = $this;

		$Output = $Surface->GetArea(
			$this->Area,
			$Scope
		);

		return $Output;
	}

	public function
	Print(Surface\Engine $Surface, array $Scope=[]):
	static {

		echo $this->Render($Surface, $Scope);

		return $this;
	}

}
