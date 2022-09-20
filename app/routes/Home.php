<?php

namespace Routes;

use Nether\Atlantis\Routes\Web;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Avenue\Meta\ErrorHandler;
use Nether\Avenue\Response;

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

	#[ErrorHandler(Response::CodeNotFound)]
	public function
	NotFound():
	void {

		echo '<div class="text-center pt-8 pb-8">404 m8</div>';

		return;
	}

}