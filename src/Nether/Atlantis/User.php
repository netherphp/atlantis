<?php

namespace Nether\Atlantis;
use Nether;

use Exception;

class User
extends Nether\User\Entity {

	public function
	UpdatePassword(string $Password):
	static {

		if(strlen($Password) < 10)
		throw new Exception('password min length');

		if(!preg_match('#[a-z]#', $Password))
		throw new Exception('complexity requirement');

		if(!preg_match('#[A-Z]#', $Password))
		throw new Exception('complexity requirement');

		if(!preg_match('#[0-9]#', $Password))
		throw new Exception('complexity requirement');

		if(!preg_match('#[^a-zA-Z0-9]#', $Password))
		throw new Exception('complexity requirement');

		////////

		parent::UpdatePassword($Password);
		return $this;
	}

}
