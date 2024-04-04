<?php

namespace Nether\Atlantis\Routes\Contact;

use Nether\Atlantis\PublicWeb;
use Nether\Avenue\Meta\RouteHandler;

class OutboundWeb
extends PublicWeb {

	#[RouteHandler('/contact')]
	public function
	PageContact():
	void {

		($this->App->Surface)
		->Area('contact/index');

		return;
	}

}
