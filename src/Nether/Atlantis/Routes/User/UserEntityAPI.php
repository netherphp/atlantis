<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis\ProtectedAPI;
use Nether\Common\Datafilters;

use Nether\Avenue\Meta\RouteHandler;
use Nether\Atlantis\Meta\RouteAccessTypeAdmin;

class UserEntityAPI
extends ProtectedAPI {


	#[RouteHandler('/api/user/entity', Verb: 'GET')]
	#[RouteAccessTypeAdmin]
	public function
	EntityGet():
	void {

		$this->SetMessage('GET');

		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'POST')]
	#[RouteAccessTypeAdmin]
	public function
	EntityPost():
	void {

		$this->SetMessage('POST');

		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'PATCH')]
	#[RouteAccessTypeAdmin]
	public function
	EntityPatch():
	void {

		$this->SetMessage('PATCH');

		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'DELETE')]
	#[RouteAccessTypeAdmin]
	public function
	EntityDelete():
	void {

		$this->SetMessage('DELETE');

		return;
	}

}