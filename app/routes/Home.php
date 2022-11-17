<?php

namespace Routes;

use Nether\Atlantis\Routes\PublicWeb;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Avenue\Meta\ErrorHandler;
use Nether\Avenue\Response;

class Home
extends PublicWeb {

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

		($this->App->Surface)
		->Area('error/not-found');

		return;
	}

}