<?php

namespace Routes;
use Nether;

use Nether\Avenue\Meta\RouteHandler;
use Nether\Atlantis\Routes\Web;

class Home
extends Web {

	#[RouteHandler('/index')]
	public function
	Index():
	void {

		($this->App->Surface)
		->Area('home/index', [ 'Herp'=> 'Derp' ]);

		return;
	}

}