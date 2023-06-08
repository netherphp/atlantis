<?php

namespace Routes;

use Nether\Atlantis;

class Test
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/test/public')]
	public function
	HandlePublic():
	void {

		return;
	}

	#[Atlantis\Meta\RouteHandler('/test/user')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	HandleRequireUser():
	void {

		echo 'user access granted';

		return;
	}

	#[Atlantis\Meta\RouteHandler('/test/admin')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleRequireAdmin():
	void {

		echo 'admin access granted';

		return;
	}

	#[Atlantis\Meta\RouteHandler('/test/at1')]
	#[Atlantis\Meta\RouteAccessType('AccessType1')]
	public function
	HandleRequireAT1():
	void {

		echo 'user access with type granted';

		return;
	}

}