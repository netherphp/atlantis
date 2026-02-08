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

		($this)
		->SetPageTitle('Contact Us')
		->Area('contact/index', []);

		return;
	}

}
