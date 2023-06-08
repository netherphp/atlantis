<?php

namespace Nether\Atlantis\Util;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class LibraryRouteScanner {

	protected Atlantis\Engine
	$App;

	protected Common\Datastore
	$Paths;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(Atlantis\Engine $App) {

		$this->App = $App;
		$this->Paths = new Common\Datastore;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	AddPath(string $Path):
	static {

		if(!$this->Paths->HasValue($Path))
		$this->Paths->Push($Path);

		return $this;
	}

	public function
	Commit():
	static {

		$Path = NULL;

		////////

		if($this->App->Router->GetSource() === 'dirscan')
		foreach($this->Paths as $Path) {
			$Scanner = new Avenue\RouteScanner($Path);
			$Map = $Scanner->Generate();

			////////

			$Map['Verbs']->Each(
				fn(Common\Datastore $Handlers)
				=> $this->App->Router->AddHandlers($Handlers)
			);

			$Map['Errors']->Each(
				fn(Avenue\Meta\RouteHandler $Handler, int $Code)
				=> $this->App->Router->AddErrorHandler($Code, $Handler)
			);
		}

		return $this;
	}

}

