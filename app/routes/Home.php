<?php

namespace Routes;

use Nether\Atlantis\PublicWeb;
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

	#[ErrorHandler(Response::CodeForbidden)]
	public function
	ErrorForbidden():
	void {

		($this->App->Surface)
		->Area('error/forbidden');

		return;
	}

	#[ErrorHandler(Response::CodeNotFound)]
	public function
	ErrorNotFound():
	void {

		($this->App->Surface)
		->Area('error/not-found');

		return;
	}

}